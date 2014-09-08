<?php
require_once(__DIR__."/../parkar-lib.php");
session_start();

if (isset($_SESSION["user"])) {
	$indexLocation = get_url_path() . "/index.php";
	header("Location: $indexLocation");
	}

$redirectURL = getFromArray($REQUEST_ARGS, "redirect", full_url());

?>

<html>
	<head><?php include("header.php"); ?></head>
	<title>Login Required</title>
	<body>
		<div class = "col-md-2"></div>
		<div class = "col-md-8">
			<h2>Sorry! This a members-only area.</h2>
			<h3>To obtain access, please <a href="login.php?redirect=<?php echo $redirectURL; ?>">login</a> or <a href="register.php?redirect=<?php echo $redirectURL; ?>">register now</a> (it's free!)</h3>
		</div>
		<div class = "col-md-2"></div>
	</body>
</html>
