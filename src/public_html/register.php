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
	<title>Register</title>
	<body>
		<div class = "row">
			<div class = "col-md-2"></div>
			<div class = "col-md-8">
				<?php if (!isset($_SESSION["id"])) { ?>
					<div class="row" id="errors-display" style="height:0px"></div>
					<form name = "register-form" onsubmit = "return verifyRegisterInput();" action = "scripts/submit.php" method = "post">
						<input type = "hidden" name = "submit-type" value = "user"/>
						<h1>User Registration</h1>
						<div class = "row">
							<h2>Account</h2>
							<input type = "text" class = "input-box" name = "username" id = "submit-username" placeholder = "Username" size = "40em"/> <br/>
							<h3>Password</h3>
							<input type = "password" class = "input-box" name = "password" id = "submit-password" placeholder = "Password" size = "20em"/>
							<h3>Confirm Password</h3>
							<input type = "password" class = "input-box" name = "password-confirm" id = "submit-password-confirm" placeholder = "Password" size = "20em"/> <br/>
						</div>
						<div class = "row">
							<h2>Personal</h2>
							<input type = "text" class = "input-box" name = "first_name" id = "submit-first_name" placeholder = "First Name" size = "40em"/> <br/>
							<input type = "text" class = "input-box" name = "last_name" id = "submit-last_name" placeholder = "Last Name" size = "40em"/> <br/>
							<input type = "text" class = "input-box" name = "email" id = "submit-email" placeholder = "Email Address" size = "40em"/> <br/>
						</div>
						<div class="row">
							<button type="submit">Register</button>
						</div>
					</form>
				<?php } 
				else { ?>
					<h2>You are already logged in!</h2>
				<?php } ?>
			</div>
			<div class = "col-md-2"></div>
		</div>
		<script type = "text/javascript">
			function exists(value) {
				// Check if the value exists
				return (value != null && value != "");
				}

			function uniqueUsername(username) {
				// Checks if a username is taken
				var url = "scripts/submit.php";
				var response = jQuery.ajax({url: url, async: false, dataType: 'json', data: {username: username, "submit-type": "check-username"}}).responseText;
				return response == "true";
				}

			function verifyRegisterInput() { // if submits, need to log in to new user
				// Verifies the user submit information
				$("#errors-display").css("height", "0px")[0].innerHTML = "";
				$(".input-box").css("border-color", "green");
				var error_disp = $("#errors-display")[0];
				var inputs = ["submit-username","submit-email", "submit-first_name", "submit-last_name"];
				var has_errors = false;

				inputs.forEach(function (input_id) {
					var input_elem = document.getElementById(input_id);
					if (!exists(input_elem.value)) {
						has_errors = true;
						$(input_elem).css("border-color", "red");
						error_span = document.createElement("span");
						error_span.innerHTML = input_elem.placeholder + " not provided. <br/>";
						error_disp.appendChild(error_span);
						}

					if (input_id == "submit-username" && exists(input_elem.value) && !uniqueUsername(input_elem.value)) {
						has_errors = true;
						$(input_elem).css("border-color", "red");
						error_span = document.createElement("span");
						error_span.innerHTML = "This username is already taken. Please use something else. <br/>";
						error_disp.appendChild(error_span);
						}
					});

				var password_field = $("#submit-password")[0];
				var password_confirm_field = $("#submit-password-confirm")[0];

				if (password_field.value != password_confirm_field.value) {
					has_errors = true;
					$(password_confirm_field).css("border-color", "red");
					var error_span = document.createElement("span");
					error_span.innerHTML = "Passwords do not match. <br/>";
					error_disp.appendChild(error_span);
					}

				if (!exists(password_field.value) || password_field.value.length < 8) {
					has_errors = true;
					$(password_field).css("border-color", "red");
					var error_span = document.createElement("span");
					error_span.innerHTML = "Password must be at least 8 characters in length. <br/>";
					error_disp.appendChild(error_span);
					}

				if (has_errors) {
					error_disp.style.height = "auto";
					return false;
					}
				return true;
				}

		</script>
	</body>
</html>
