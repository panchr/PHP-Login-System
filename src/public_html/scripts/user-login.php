<?php
/* login.php
Rushy Panchal */

require_once(__DIR__."/../../user-lib.php");

function setUser($db, $ip_addr, $validated, $remember = false, $time = NULL) {
	// Sets the current user
	global $ALL_QUERIES, $REQUEST_ARGS;
	if (is_null($time)) {
		$time = time() + 2592000;
		}
	$user_data = getUserData($db, $validated["id"]);
	$_SESSION["user"] = $user_data["username"];
	$_SESSION["id"] = $validated["id"];
	unset($_SESSION["login-error"]);
	if ($remember == "true" || $remember == "on" || $remember == true || $remember) {
		$uniqueID = hashPassword($user_data["username"].$ip_addr.$validated["id"].uniqid($user_data["username"]));
		$uniqueID = $uniqueID["hash"];
		setcookie("user-login", $uniqueID, $time, "/");
		$insertCookie = $db->prepare($ALL_QUERIES["login"]["cookie"]["set"]);
		$insertCookie->bindValue(":user_id", $validated["id"]);
		$insertCookie->bindValue(":ip_addr", $ip_addr);
		$insertCookie->bindValue(":cookie", $uniqueID);
		$insertCookie->execute();
		}
	if (isset($REQUEST_ARGS["redirect"])) { ?>
		<script type = "text/javascript"> window.location = "<?php echo $REQUEST_ARGS['redirect']; ?>";</script>
		<?php }
	}

function checkCookie($db, $ip_addr) {
	// Checks if a cookie exists
	global $ALL_QUERIES;
	$cookieSelect = $db->prepare($ALL_QUERIES["login"]["cookie"]["get"]);
	$cookieSelect->bindValue(":ip_addr", $ip_addr, PDO::PARAM_STR);
	$cookieSelect->execute();
	$cookieDB = $cookieSelect->fetchAll(PDO::FETCH_ASSOC);
	if (sizeof($cookieDB) == 1 && $_COOKIE["user-login"] == getFromArray($cookieDB, "cookie")) {
		$validated = Array("verified" => true, "status" => "verifed", "id" => getFromArray($cookieDB, "id"), "from_cookie" => true);
		setUser($db, $ip_addr, $validated, true);
		}
	else {
		session_start();
		$_SESSION["login-error"] = "cookie not found";
		$validated = false;
		}
	return $validated;
	}

function checkLogin($db, $ip_addr, $args) {
	// Checks the login
	global $ALL_QUERIES;
	if (!isset($_SESSION["user"]) && isset($args["user"]) && isset($args["pass"])) {
		$validated = login($db, $args["user"], $args["pass"]);
		if ($validated["verified"]) {
			return setUser($db, $ip_addr, $validated, getFromArray($args, "remember", false), getFromArray($args, "time", NULL));
			}
		}
	session_start();
	$_SESSION["login-error"] = isset($validated["status"])? $validated["status"]: "error"; 
	$redirectURL = get_url_path('/../login.php?username='.$args['user']);
	?>
	<script type = "text/javascript"> window.location = "<?php echo $redirectURL; ?>";</script> <?php
	}

function login($db, $user, $password) { // need a way to encrypt password before receiving
	// Checks to see if the password matches
	global $ALL_QUERIES, $NOT_FOUND, $CLIENT_IP;
	$selectQuery = $ALL_QUERIES["login"]["select"];
	$selectStmt = $db->prepare($selectQuery);
	$selectStmt->bindValue(":user", $user, PDO::PARAM_STR);
	$selectStmt->execute();
	$user_data = getFromDatabase($selectStmt);

	if ($user_data == $NOT_FOUND) {
		return Array("verified" => false, "status" => "not found");
		}
	else {
		$checkQuery = $ALL_QUERIES["login"]["attempts"];
		$checkStmt = $db->prepare($checkQuery);
		$checkStmt->bindValue(":user", getFromArray($user_data, "id", 0), PDO::PARAM_INT);
		$checkStmt->bindValue(":ip_addr", $CLIENT_IP, PDO::PARAM_STR);
		$checkStmt->execute();
		$matches = $checkStmt->fetchAll(PDO::FETCH_ASSOC);

		if (sizeof($matches) >= 5) {
			return Array("verified" => false, "status" => "attempts");
			}

		else {
			$validated = Array("verified" => passwordMatches($password, getFromArray($user_data, "salt", ""), getFromArray($user_data, "password", "")), "id" => getFromArray($user_data, "id", 0), "status" => "verified");
			if (!$validated["verified"]) {
				$validated["status"] = "incorrect";
				$insertQuery = $ALL_QUERIES["login"]["record"];
				$insertStmt = $db->prepare($insertQuery);
				$insertStmt->bindValue(":user", getFromArray($user_data, "id", 0), PDO::PARAM_INT);
				$insertStmt->bindValue(":ip_addr", $CLIENT_IP, PDO::PARAM_STR);
				$insertStmt->execute();
				}
			return $validated;
			}
		}
	}

function passwordMatches($password, $salt, $hashed_password) {
	// Checks if a password matches its hash
	return ($hashed_password == hash('sha512', $password . $salt));
	}

session_start();
checkCookie($DB, $CLIENT_IP);

if ($REQUEST_ARGS["action"] == "login") {
	checkLogin($DB, $CLIENT_IP, $REQUEST_ARGS);
	}

?>
