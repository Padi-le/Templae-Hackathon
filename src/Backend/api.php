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

	// Main function to handle the API request
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

    
}
?>