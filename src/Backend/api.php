<?php
include_once "config.php";


class Api {

    private static $instance = null;
    private $conn;
    private $data;

    public function __construct($dbConn) {
        $this->conn = $dbConn;
        $this->parseRequestData();
    }

    private function parseRequestData() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $this->data = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->respond("error", "Invalid JSON format", 400);
            }
        } else {
            $this->respond("error", "application/json expected", 415);
        }
    }

    private function respond($status, $message, $code) {
        http_response_code($code);
        header('Content-Type: application/json');

        echo json_encode([
            'status' => $status,
            'data' => $message
        ]);
        exit;
    }

    private function validateEmail($email) {
        return preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email) === 1;
    }

    private function validatePassword($pass) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{9,}$/', $pass) === 1;
    }

    private function validateName($name) {
        if (empty($name)) return false;
        return preg_match('/^[A-Za-z][A-Za-z\'\- ]{1,54}$/', $name) === 1;
    }

    private function userExists($email) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    private function generateApiKey() {
        $apiKey = bin2hex(random_bytes(32));

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE Api_key = :apikey");
        $stmt->bindValue(':apikey', $apiKey, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            return $this->generateApiKey();
        }

        return $apiKey;
    }

    private function addUser($name, $surname, $email, $password,$gender) {
        $apikey = $this->generateApiKey();
        $stmt = $this->conn->prepare("
            INSERT INTO users (email, password, name, Surname, Api_key)
            VALUES (:email, :password, :name, :surname, :gender, :apikey)
        ");

        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':surname', $surname, PDO::PARAM_STR);
        $stmt->bindValue(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindValue(':apikey', $apikey, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $this->respond("success", ['apikey' => $apikey], 201);
        } else {
            $errorInfo = $stmt->errorInfo();
            $this->respond("error", $errorInfo[2], 500);
        }
    }

    private function hashPassword($pass) {
        $options = ['memory_cost' => 1 << 15, 'time_cost' => 2, 'threads' => 1];
        return password_hash($pass, PASSWORD_ARGON2ID, $options);
    }

   private function handleRegister() {
        $data = $this->data;

        if (!isset($data['name']) || !$this->validateName($data['name'])) {
            $this->respond("error", "Invalid or missing name", 400);
        }

        if (!isset($data['surname']) || !$this->validateName($data['surname'])) {
            $this->respond("error", "Invalid or missing surname", 400);
        }

        if (!isset($data['email']) || !$this->validateEmail($data['email'])) {
            $this->respond("error", "Invalid or missing email", 400);
        }

        if ($this->userExists($data['email'])) {
            $this->respond("error", "User already exists", 409);
        }

        if (!isset($data['password']) || !$this->validatePassword($data['password'])) {
            $this->respond("error", "Password format insufficient, unsafe.", 400);
        }

        if (!isset($data['gender']) || !in_array(strtolower($data['gender']), ['male', 'female', 'other'])) {
            $this->respond("error", "Invalid or missing gender", 400);
        }

        $this->addUser(
            $data['name'],
            $data['surname'],
            $data['email'],
            $this->hashPassword($data['password']),
            $data['gender']
        );
    }

    private function handleLogin() {
        $data = $this->data;

        if (!isset($data['email']) || !$this->validateEmail($data['email'])) {
            $this->respond("error", "Invalid or missing email", 400);
        }

        if (!isset($data['password'])) {
            $this->respond("error", "Password missing", 400);
        }

        if (!$this->userExists($data['email'])) {
            $this->respond("error", "User does not exist", 401);
        }

        $stmt = $this->conn->prepare("
            SELECT  id, name, surname, api_key, password, last_logged_in, streaks,points
            FROM users WHERE email = :email
        ");
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $this->respond("error", "User not found", 404);
        }
        if (!password_verify($data['password'], $user['password'])) {
        $this->respond("error", "Password incorrect", 400);
        }
        $lastLogin = $user['last_logged_in'] ? new DateTime($user['last_logged_in']) : null;
        $today = new DateTime('today');
        $streak = $user['streaks'] ?? 0;
        $points = $user['points'] ?? 10;

        if ($lastLogin) {
            $diff = $today->diff($lastLogin)->days;
            if ($diff == 1) {
                $streak++;
                $points = $points + 10 ;
            } elseif ($diff > 1) {
                $streak = 1;
                $points = $points -10 ;
            }

        } else {
            $streak = 1; 
        }

        $stmt = $this->conn->prepare("
            UPDATE users SET streaks = :streaks, last_logged_in = NOW() ,points = :points WHERE id = :id
        ");

        $stmt->bindValue(':streaks', $streak, PDO::PARAM_INT);
        $stmt->bindValue(':points',$points, PDO::PARAM_INT);
        $stmt->bindValue(':id', $user['id'], PDO::PARAM_INT);
        $stmt->execute();

        $this->respond("success", [
            'apikey' => $user['api_key'],
            'name' => $user['name'],
            'surname' => $user['surname'],
            'streak' => $streak,
            'Points'=>$points
        ], 200);
        // if (password_verify($data['password'], $user['Password'])) {
        //     $this->respond("success", [
        //         'apikey' => $user['Api_key'],
        //         'name' => $user['FirstName'],
        //         'surname' => $user['LastName'],
                
        //     ], 200);
        // } else {
        //     $this->respond("error", "Password incorrect", 400);
        // }


    }
    
    public function handleGetInfo(){
        $data = $this->data;
        if(!isset($this->data["api_key"]))
            $this->respond("error", "API key required", 401);
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE api_key = :api_key");
        $stmt->bindValue(":api_key", $this->data["api_key"], PDO::PARAM_STR);
        $stmt->execute();
        if($stmt->fetchColumn() === 0)
            $this->respond("error", "Invalid API key", 401);
        $stmt = $this->conn->prepare("SELECT id, last_logged_in, name, email, surname, api_key, streaks, points, gender, online FROM users WHERE api_key = :api_key");
        $stmt->bindValue(':api_key', $data['api_key'], PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$user)
            $this->respond("error", "User not found", 404);
        $this->respond("success", $user, 200);
    }

    public function handleUpdateInfo(){
        $data = $this->data;
        if(!isset($data["api_key"]))
            $this->respond("error", "API key required", 401);
        //dynamically building the query
        $allowedFields = ["name", "surname", "email", "streaks", "points", "gender", "online"];
        $updatedFields = [];
        $params = [":api_key" => $data["api_key"]];
        
        foreach($allowedFields as $field){
            if(isset($data[$field])){
                if ($field === 'email' && !$this->validateEmail($data[$field]))
                    $this->respond("error", "Invalid email format", 400);
                if ($field === 'name' && !$this->validateName($data[$field]))
                    $this->respond("error", "Invalid name format", 400);
                if ($field === 'surname' && !$this->validateName($data[$field]))
                    $this->respond("error", "Invalid surname format", 400);
                if ($field === 'online' && (!$data[$field] === "TRUE" || !$data[$field] === "FALSE"))
                    $this->respond("error", "Online must be boolean", 400);
                
                $updateFields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if(empty($updateFields))
            $this->respond("error", "No valid fields provided for update", 400);

        $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE api_key = :api_key";
        $stmt = $this->conn->prepare($query);

        foreach($params as $key => $value){
            $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $paramType);
        }
        try{
            if ($stmt->execute()){
                $stmt = $this->conn->prepare("SELECT id, last_logged_in, name, email, surname, api_key, streaks, points, gender, online FROM users WHERE api_key = :api_key");
                $stmt->bindValue(":api_key", $data["api_key"], PDO::PARAM_STR);
                $stmt->execute();
                $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
                $this->respond("success", $updatedUser, 200);
            } else {
                $errorInfo = $stmt->errorInfo();
                $this->respond("error", $errorInfo[2], 500);
            }
        }
        catch(error){
            echo "error in update";
        }
    }

    private function handleOnline() {
        $data = $this->data;

        if(!isset($data['api_key']))
        {
            $this->respond("error", "API key missing", 400);
        }

        $stmt = $this->conn->prepare("
            SELECT COUNT(*) FROM users WHERE online = TRUE;
        ");

        $stmt->execute();

        $this->respond("success", [
            'online_users' => $stmt->fetchColumn()
        ], 200);
    }


    public function handleRequest() {
        $data = $this->data;

        if (!isset($data['type'])) {
            $this->respond('error', 'Type attribute missing', 400);
        }

        switch ($data['type']) {
            case "Register":
                $this->handleRegister();
                break;
            case "Login":
                $this->handleLogin();
                break;
            case "GetInfo":
                $this->handleGetInfo();
                break;
            case "UpdateInfo":
                $this->handleUpdateInfo();
                break;
            case "CheckOnline":
                $this->handleOnline();
                break;
            default:
                $this->respond('error', 'Invalid Request Type', 400);
        }
    }
}

// Instantiate and handle request
$dbConn = Database::instance()->getConnection();
$api = new Api($dbConn);
$api->handleRequest();
