<?php 
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

class Database {

    private $conn;

    public static function instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new Database();
        }
        return $instance;
    }

    private function __construct() {
        $host     = $_ENV['DB_HOST'];
        $port     = $_ENV['DB_PORT'];
        $dbname   = $_ENV['DB_NAME'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

        try {
            $this->conn = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            error_log("Connection success");
        } catch (PDOException $e) {
            die("Connection failure: " . $e->getMessage());
        }
    }

    public function __destruct() {
        $this->conn = null; // closes PDO connection
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
