<?php

require_once(__DIR__."/../user-lib.php");
session_start();

if (isset($_SESSION["user"])) {
	session_destroy();
	$cookieValue = $_COOKIE["user-login"];
	setcookie("user-login", "", 0);
	unset($_COOKIE["user-login"]);
	$deleteStmt = $DB->prepare($ALL_QUERIES["login"]["cookie"]["delete"]);
	$deleteStmt->bindValue(":ip_addr", $CLIENT_IP, PDO::PARAM_STR);
	$deleteStmt->bindValue(":cookie", $cookieValue, PDO::PARAM_STR);
	$deleteStmt->execute();
	}

if (isset($REQUEST_ARGS["redirect"])) { ?>
		<script type = "text/javascript"> window.location = "<?php echo $REQUEST_ARGS['redirect']; ?>"; </script>
		<?php }
?>
