<?php

// parkar-lib.php - contains overall importable functions

// Constants

require_once("parkar-config.php");

$NOT_FOUND = "Row not found";
$TAB = "&nbsp&nbsp&nbsp&nbsp";

$ALL_QUERIES = Array(
	"request-record" => "INSERT INTO `http_log` (`ip_addr`, `request`, `type`) VALUES (:client_ip, :request_str, :request_type)",
	"user-from-id" => "SELECT * FROM `users` WHERE `id` = :user_id LIMIT 1",
	"login" => Array(
		"register" => "INSERT INTO `users` (`username`, `email`, `password`, `salt`, `ip_addr`, `first_name`, `last_name`) VALUES (:username, :email, :password, :salt, :ip_addr, :first_name, :last_name)",
		"check-username" => "SELECT `username` FROM `users` WHERE `username` = :username",
		"select" => "SELECT `id`, `username`, `password`, `password`, `salt` FROM `users` WHERE `email` = :user OR `username` = :user LIMIT 1",
		"attempts" => "SELECT `user_id`, `ip_addr` FROM `login_attempts` WHERE `user_id` = :user OR `ip_addr`=:ip_addr",
		"record" => "INSERT INTO `login_attempts` (`user_id`, `ip_addr`) VALUES (:user, :ip_addr)",
		"cookie" => Array(
			"get" => "SELECT `user_id`, `cookie` FROM `user_sessions` WHERE `ip_addr` = :ip_addr LIMIT 1",
			"set" => "INSERT INTO `user_sessions` (`user_id`, `ip_addr`, `cookie`) VALUES (:user_id, :ip_addr, :cookie) ON DUPLICATE KEY UPDATE `cookie` = VALUES (:cookie);",
			"delete" => "DELETE FROM `user_sessions` WHERE `ip_addr` = :ip_addr AND `cookie` = :cookie"
			)
		)
	);

$REQUEST_ARGS = empty($_GET) ? $_POST: $_GET;
$CLIENT_IP = $_SERVER["REMOTE_ADDR"];

// Server functions

function recordHTTPRequest($db, $clientIP) {
	// Records the HTTP request and time
	global $ALL_QUERIES;
	$insertQuery = $ALL_QUERIES["request-record"];
	$requestString = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$insertStmt = $db->prepare($insertQuery);
	$insertStmt->bindValue(":client_ip", $clientIP, PDO::PARAM_STR);
	$insertStmt->bindValue(":request_str", $requestString, PDO::PARAM_STR);
	$insertStmt->bindValue(":request_type", $_SERVER["REQUEST_METHOD"]);
	$insertStmt->execute();
	}

function getUserData($db, $user_id) {
	// Gets user data based off of the IP
	global $ALL_QUERIES;
	$selectQuery = $ALL_QUERIES["user-from-id"];
	$selectStmt = $db->prepare($selectQuery);
	$selectStmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
	$selectStmt->execute();
	return getFromDatabase($selectStmt);
	}

function hashPassword($password) {
	// Returns the hashed password and a salt
	$length = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
	$salt = base64_encode(mcrypt_create_iv(ceil(0.75*$length), MCRYPT_DEV_URANDOM));
	return Array(
		"hash" => hash('sha512', $password . $salt),
		"salt" => $salt
		);
	}

// Database-related functions

function connectToDB($database, $user, $password, $host = "localhost", $options = Array()) {
	// Connects to a database and returns the connection object
	$db = new PDO("mysql:host=$host;dbname=$database", $user, $password, $options);
	return $db;
	}

function getFromDatabase($data) {
	// Gets data from the database
	global $NOT_FOUND;
	while($info = $data->fetch()) {
		return $info;
		}
	return $NOT_FOUND;
	}

// Miscellaneous Functions

function getData($url, $returnJson = true) {
	// Gets the data from an HTTP GET request to the URL
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$rawData = curl_exec($curl);
	curl_close($curl);
	return $returnJson ? json_decode($rawData, true): $rawData;
	}
	
function postData($url, $args, $returnJson = true) {
	// Sends an HTTP POST Request to the url
	foreach ($args as $key=>$value) {
		$fields_string.= $key.'='.urlencode($value).'&';
		}
	$fields_string = rtrim($fields_string, '&');
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, count($args));
	curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$rawData = curl_exec($curl);
	curl_close($curl);
	return $returnJson ? json_decode($rawData, true): $rawData;
	}

function getJSONData($filepath) {
	// Gets JSON data from the file path
	$undecoded_data = file_get_contents($filepath);
	$data = json_decode($undecoded_data, true);
	return $data;
	}

function getFromArray($array_name, $key_name, $default_value = "") {
	// Attempts to get from an array, but resorts to a default value if key is not found
	return (array_key_exists($key_name, $array_name)? $array_name[$key_name]: $default_value);
	}

function full_url() {
	// Returns the full current URL
	$s = &$_SERVER;
	$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true: false;
	$sp = strtolower($s['SERVER_PROTOCOL']);
	$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
	$port = $s['SERVER_PORT'];
	$port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
	$host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
	$host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
	$uri = $protocol . '://' . $host . $s['REQUEST_URI'];
	$segments = explode('?', $uri, 2);
	$url = $segments[0];
	return $url;
	}

function get_url_path($extra = "") {
	// Returns the current URL path (excludes the current file)
	return dirname(full_url()) . $extra;
	}

// Global Constants

$DB = connectToDB(DATABASE, DB_USER, DB_PASSWORD, HOST, Array(PDO::ATTR_PERSISTENT => true));
recordHTTPRequest($DB, $CLIENT_IP);

?>
