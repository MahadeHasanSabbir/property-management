<?php
	session_start();
	if(isset($_SESSION['id']) or isset($_SESSION['aid'])){
		header("location:http://localhost/Property-Management/auth");
		exit;
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<title> About | Property-Management </title>
		<link rel="stylesheet" type="text/css" href="./style/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="./style/css/bootstrap-theme.min.css">
		<style>
			body {padding-top:60px;background-color:darkseagreen;}
			.jumbotron {margin-bottom:10px;}
			.jumbotron p {margin-bottom:0px;}
		</style>
	</head>
	<body>
		<?php include 'header.php'; ?>
		<div class="container-fluid" role="main">
			<!--website info-->
			<div class="well" style="margin-bottom:05px;padding-top:05px;">
				<div class="jumbotron" style="background-color:unset;">
				<p class="text-center">
					<strong> Welcome! </strong> We are here to help you manage and distribute your property as you want.
				</p>
				</div>
				<blockquote class="bg-warning" style="color:black;">
					<p>
						Before starting we want to clear one thing, we will help you to manage your property depending on your input. We can not help you to own or complete paper work along with government procedure, it will be done by you.
					</p>
					<footer> From Property Management </footer>
				</blockquote>
			</div>
			<div>
				<ul class="list-group" style="margin-bottom:10px;">
					<li class="list-group-item">
						<h4 class="list-group-item-heading">
							property insertion process
						</h4>
						<p class="list-group-item-text text-justify">
							Simply enter all the property data in the given form input field as define there and rest of the thing will be manage by us.
						</p>
					</li>
					<li class="list-group-item">
						<h4 class="list-group-item-heading">
							property search process
						</h4>
						<p class="list-group-item-text text-justify">
							Simply enter the data you know and leave the unknown input field blank. We will help you to find out all the data related to your input. You can find only the data saved by you.
						</p>
					</li>
					<li class="list-group-item">
						<h4 class="list-group-item-heading">
							property distribution process
						</h4>
						<p class="list-group-item-text text-justify">
							Simply fill out the form data and be sure about your data. Your property will be distributed according to your input.
						</p>
					</li>
				</ul>
			</div>
		</div>
		<div class="footer"></div>
		<script src="./style/js/jquery.min.js"></script>
		<script src="./style/js/bootstrap.min.js"></script>
		<script src="./style/js/jscript.js"></script>
	</body>
</html>
<?php
	$ip = $_SERVER['REMOTE_ADDR'];
	if (filter_var($ip, FILTER_VALIDATE_IP)) {
		
		$connect = mysqli_connect("localhost", "root", "", "property");
		$sql = "INSERT INTO visitors (ip) VALUES ('$ip')";
		
		mysqli_query($connect, $sql);
		mysqli_close($connect);
	}
?>