<?php

/* submit.php
Rushy Panchal */

require_once(__DIR__."/../../parkar-lib.php");
session_start();

function registerUser($db) {
	// Registers a user in the database
	global $REQUEST_ARGS, $ALL_QUERIES, $CLIENT_IP;
	$insertQuery = $ALL_QUERIES["login"]["register"];
	$insertStmt = $db->prepare($insertQuery);
	$hashedPassword = hashPassword(getFromArray($REQUEST_ARGS, "password", ""));
	$insertStmt->bindValue(":username", getFromArray($REQUEST_ARGS, "username", ""), PDO::PARAM_STR);
	$insertStmt->bindValue(":email", getFromArray($REQUEST_ARGS, "email", ""), PDO::PARAM_STR);
	$insertStmt->bindValue(":password", $hashedPassword["hash"], PDO::PARAM_STR);
	$insertStmt->bindValue(":salt", $hashedPassword["salt"], PDO::PARAM_STR);
	$insertStmt->bindValue(":ip_addr", $CLIENT_IP, PDO::PARAM_STR);
	$insertStmt->bindValue(":first_name", getFromArray($REQUEST_ARGS, "first_name", ""), PDO::PARAM_STR);
	$insertStmt->bindValue(":last_name", getFromArray($REQUEST_ARGS, "last_name", ""), PDO::PARAM_STR);
	$insertStmt->execute();
	return Array("user" => getFromArray($REQUEST_ARGS, "username", ""), "pass" => getFromArray($REQUEST_ARGS, "password", ""));
	}

function checkUsername($db) {
	// Checks if a username exists already
	global $REQUEST_ARGS, $ALL_QUERIES;
	$selectQuery =$ALL_QUERIES["login"]["check-username"];
	$selectStmt = $db->prepare($selectQuery);
	$selectStmt->bindValue(":username", getFromArray($REQUEST_ARGS, "username", ""));
	$selectStmt->execute();
	$results = $selectStmt->fetchAll(PDO::FETCH_ASSOC);
	return sizeof($results) == 0 ? "true": "false";
	}

$submitType = getFromArray($REQUEST_ARGS, "submit-type", "");
$redirectURL = getFromArray($REQUEST_ARGS, "redirect", get_url_path() . "/../index.php");
$submitSuccess = true;

if ($submitType == "user") {
	$user_data = registerUser($DB, true);
	$user_data["remember"] = true;
	include("user-login.php");
	checkLogin($DB, $CLIENT_IP, $user_data);
	}
elseif ($submitType == "check-username") {
	echo checkUsername($DB);
	$submitSuccess = false;
	}
else {
	$submitSuccess = false;
	}

?>

<html>
<?php if ($submitSuccess) { ?>
	<script type = "text/javascript">
		window.location = "<?php echo $redirectURL; ?>";
	</script>
<?php } else { ?>
	<head><?php include("header.php"); ?></head>
	<title>Submission Error</title>
	<body>
		<div class = "col-md-2"></div>
		<div class = "col-md-8">
			<h2>Something went wrong!</h2>

		</div>
		<div class = "col-md-2"></div>
	</body>
<?php } ?>
<html>
