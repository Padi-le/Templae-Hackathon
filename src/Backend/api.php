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

    private function addUser($name, $surname, $email, $password) {
        $apikey = $this->generateApiKey();
        $stmt = $this->conn->prepare("
            INSERT INTO users (email, password, name, Surname, Api_key)
            VALUES (:email, :password, :name, :surname, :apikey)
        ");

        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':surname', $surname, PDO::PARAM_STR);
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

        $this->addUser(
            $data['name'],
            $data['surname'],
            $data['email'],
            $this->hashPassword($data['password'])
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
            SELECT name, surname, apikey, password
            FROM users WHERE email = :email
        ");
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $this->respond("error", "User not found", 404);
        }

        if (password_verify($data['password'], $user['Password'])) {
            $this->respond("success", [
                'apikey' => $user['Api_key'],
                'name' => $user['FirstName'],
                'surname' => $user['LastName'],
                'user_type' => $user['Type'] ?? null
            ], 200);
        } else {
            $this->respond("error", "Password incorrect", 400);
        }
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
            default:
                $this->respond('error', 'Invalid Request Type', 400);
        }
    }
}

// Instantiate and handle request
$dbConn = Database::instance()->getConnection();
$api = new Api($dbConn);
$api->handleRequest();
