<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once "config.php";

class Api{

    private static $instance = null;
	private $conn;

	private $data;

	// Constructor to initialize the database connection and parse request data
	public function __construct($dbConn){

		$this->conn=$dbConn;
		$this->parseRequestData();
	}

	private function parseRequestData(){

		$contentType=$_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

		if(strpos($contentType,'application/json')!==false){

			$json=file_get_contents('php://input');
		
			$this->data=json_decode($json,true);
		
			if(json_last_error()!==JSON_ERROR_NONE){
		
				$this->respond("error","Invalid JSON format",400);
			}
		
		}else{
		
			$this->respond("error","application/json expected",415);
		}

	}

	//
	private function respond($status, $message, $code){
		http_response_code($code);
		header('Content-Type: application/json');

		$jsonF=json_encode([
			'status'=>$status,
			'data'=>$message
		]);

		echo $jsonF;
		exit;
    }
    private function validateEmail($email){

		$regex="/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";

		return preg_match($regex, $email)===1;
	}

	private function validatePassword($pass){

		$regex='/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{9,}$/';
		return preg_match($regex,$pass)===1;
	}

	private function validateName($name){

		if(empty($name)){

			return false;
		}

		return preg_match('/^[A-Za-z][A-Za-z\'\- ]{1,54}$/', $name) === 1;
	}

	private function userExists($email){

		$conn=$this->conn;

		$checkUser=$conn->prepare("SELECT * FROM users WHERE email=?");

		$checkUser->bind_param("s",$email);

		$checkUser->execute();
		$checkUser->store_result();

		$rows=$checkUser->num_rows;

		$checkUser->close();

		return $rows>0;
		
	}

	private function generateApiKey(){
		$bytes = random_bytes(32);
		
		$apiKey = bin2hex($bytes);
		
		$conn = $this->conn;
		$stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE Api_key = ?");
		$stmt->bind_param("s", $apiKey);
		$stmt->execute();
		$stmt->bind_result($count);
		$stmt->fetch();
		$stmt->close();
		
		if($count > 0){
			return $this->generateApiKey();
		}
		
		return $apiKey;
	}

	private function addUser($name,$surname,$email,$password,$user_type){

		$conn=$this->conn;

		$add=$conn->prepare("INSERT INTO users (Email,Password,FirstName,LastName,Type,Api_key) VALUES(?,?,?,?,?,?)");

		$apikey=$this->generateApiKey();

		$add->bind_param("ssssss",$email,$password,$name,$surname,$user_type,$apikey);

		if($add->execute()){

			$this->respond("success",['apikey'=>$apikey],201);
		}else{

			$this->respond("error",$add->error,500);
		}

		$add->close();
	}

	private function hashPassword($pass){

		$options=['memory_cost'=>1 << 15, 'time_cost'=>2, 'threads'=>1];

		$hash=password_hash($pass,PASSWORD_ARGON2ID,$options);

		return $hash;
	}

	private function checkApiKey($apikey){

		$conn=$this->conn;

		if(isset($apikey)){

			$stmt=$conn->prepare("SELECT email FROM users WHERE Api_key=?");

			$stmt->bind_param("s",$apikey);

			if($stmt->execute()){

				$result=$stmt->get_result();

				if($result->num_rows>0){
					return true;
				}else{

					return false;
				}

			}else{

				return false;
			}
		}

		return false;
	}

	private function handleRegister(){

		$data=$this->data;

		$email=null;
		$name=null;
		$surname=null;
		$password=null;
		$user_type=null;

		if(!isset($data['user_type'])||($data['user_type']!='Customer'&&$data['user_type']!='Manager')){

			$this->respond("error","Invalid or missing user type",400);
		}

		$user_type=$data['user_type'];

		if(!isset($data['name'])||!$this->validateName($data['name'])){

			$this->respond("error","Invalid or missing name",400);
		}

		$name=$data['name'];

		if(!isset($data['surname'])||!$this->validateName($data['surname'])){

			$this->respond("error","Invalid or missing surname",400);
		}

		$surname=$data['surname'];

		if(!isset($data['email'])||!$this->validateEmail($data['email'])){

			$this->respond("error","Invalid or missing email",400);
		}

		if($this->userExists($data['email'])){

			$this->respond("error","User already exists",409);
		}

		$email=$data['email'];

		if(!isset($data['password'])||!$this->validatePassword($data['password'])){

			$this->respond("error","Password format insufficient, unsafe.",400);
		}

		$password=$this->hashPassword($data['password']);

		if(isset($name)&&isset($email)&&isset($password)&&isset($user_type)){

			$this->addUser($name,$surname,$email,$password,$user_type);
		}

	}

	private function handleLogin(){
		$data = $this->data;
		$conn = $this->conn;

		$accepted = ['type','email','password'];
		foreach($data as $attr=>$value){
			if(!in_array($attr,$accepted)){
				$this->respond("error","Invalid Parameter",400);
			}
		}

		if(!isset($data['email'])||!$this->validateEmail($data['email'])){
			$this->respond("error","Invalid or missing email",400);
		}
		if(!isset($data['password'])){
			$this->respond("error","Password missing",400);
		}

		$email = $data['email'];
		$password = $data['password'];

		if(!$this->userExists($email)){
			$this->respond("error","User does not exist",401);
		}

		// Fetch user info including failed attempts and lockout
		$stmt = $conn->prepare("SELECT FirstName,LastName,Api_key,Password,Type,failed_attempts,locked_until,lockout_duration FROM users WHERE email=?");
		$stmt->bind_param("s",$email);

		if($stmt->execute()){
			$stmt->bind_result($Firstname,$Lastname,$Api_key,$hashedPassword,$user_type,$failed_attempts,$locked_until,$lockout_duration);
			if($stmt->fetch()){
				// Check if user is locked out
				if ($locked_until && strtotime($locked_until) > time()) {
					$stmt->close();
					$remaining = strtotime($locked_until) - time();
					$minutes = ceil($remaining / 60);
					$this->respond("error","Account locked. Try again in $minutes minute(s).", 403);
				}

				if(password_verify($password,$hashedPassword)) {
					$stmt->close();
					// Reset failed attempts and lockout duration on success
					$stmt2 = $conn->prepare("UPDATE users SET failed_attempts=0, locked_until=NULL, lockout_duration=600 WHERE email=?");
					$stmt2->bind_param("s", $email);
					$stmt2->execute();
					$stmt2->close();

					$this->respond("success",[['apikey'=>$Api_key,'name'=>$Firstname, 'surname'=>$Lastname, 'user_type'=>$user_type]],200);
				} else {
					$stmt->close();
					// Increment failed attempts
					$failed_attempts++;
					$new_lockout_duration = $lockout_duration ?: 600; // default 10 min

					if ($failed_attempts >= 3) {
						// Lock for current duration, then triple it for next time
						$lockout = date('Y-m-d H:i:s', time() + $new_lockout_duration);
						$next_duration = $new_lockout_duration * 3;
						$stmt2 = $conn->prepare("UPDATE users SET failed_attempts=0, locked_until=?, lockout_duration=? WHERE email=?");
						$stmt2->bind_param("sis", $lockout, $next_duration, $email);
						$stmt2->execute();
						$stmt2->close();
						$minutes = ceil($new_lockout_duration / 60);
						$this->respond("error","Too many failed attempts. Account locked for $minutes minute(s).",403);
					} else {
						$stmt2 = $conn->prepare("UPDATE users SET failed_attempts=? WHERE email=?");
						$stmt2->bind_param("is", $failed_attempts, $email);
						$stmt2->execute();
						$stmt2->close();
						$this->respond("error","Password incorrect. ".(3-$failed_attempts)." attempt(s) left.",400);
					}
				}
			}
			$stmt->close();
		} else {
			$this->respond('error', $stmt->error, 500);
		}
	}
	
	public function handleRequest(){

		$data=$this->data;

		if(isset($data['type'])){

			$reqType=$data['type'];

			switch($reqType){

				case "Register":
					$this->handleRegister();
					break;

				case "Login":
					$this->handleLogin();
					break;

				default:
					$this->respond('error','Invalid Request Type',400);		
			}
			
		}else{

			$this->respond('error','Type attribute missing',400);
		}

	}

}

$dbConn=Database::instance()->getConnection();
$api=new Api($dbConn);
$api->handleRequest();



?>