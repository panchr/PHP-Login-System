<?php
require_once(__DIR__."/../user-lib.php");
session_start();

$username = getFromArray($REQUEST_ARGS, "username", "");
$redirectURL = getFromArray($REQUEST_ARGS, "redirect", full_url());
$errorMsg = NULL;

if (isset($_SESSION["user"])) {
	$indexLocation = get_url_path() . "/index.php";
	header("Location: $indexLocation");
	}
elseif (isset($_SESSION["login-error"])) {
	$loginError = $_SESSION["login-error"];
	if ($loginError == "not found") {
		$errorMsg = "Username/email $username could not be found.";  
		}
	elseif ($loginError == "attempts") {
		$errorMsg = "You have exceeded the maximum number of login attempts this hour.";
		}
	elseif ($loginError == "incorrect") {
		$errorMsg = "Password is incorrect.";
		}
	}
?>

<html>
	<head><?php include("header.php"); ?></head>
	<title>Login</title>
	<body>
		<div class = "col-md-2"></div>
		<div class = "col-md-8">
			<?php if (!is_null($errorMsg)) { ?>
				<h2 style = "color: red; text-align: center;"><?php echo $errorMsg; ?></h2>
				<?php	} ?>
			<form action="scripts/user-login.php" method = "post">
				<input type = "hidden" name = "action" value = "login">
				<input type = "hidden" name = "redirect" value = "<?php echo $redirectURL; ?>">
				<input type = "text" name = "user" placeholder = "username/email" value = "<?php echo $username; ?>"> <br/>
				<input type = "password" name = "pass" placeholder = "password"> <br/>
				<!-- <input type = "checkbox" name = "remember" value = "true"> Remember Me</input> <br/> -->
				<button type="submit">Login</button>
			</form>
		</div>
		<div class = "col-md-2"></div>
	</body>
</html>
