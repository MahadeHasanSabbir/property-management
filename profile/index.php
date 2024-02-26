<?php
	session_start();
	if(isset($_SESSION['aid'])){
		header("location:http://localhost/Property-Management/admin");
		exit;
	}
	if(isset($_SESSION['id'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");

		//sql query to find user information from database
		$sqlquery = "SELECT name FROM user WHERE ID = '$_SESSION[id]'";

		//take data from database
		$data = mysqli_query($connect, $sqlquery);

		//convert 2D array to 1D array
		$row = mysqli_fetch_assoc($data);
?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="UTF-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
				<title> <?php echo $row['name'];?> | Property-Management </title>
				<link rel="stylesheet" type="text/css" href="http://localhost/Property-Management/style/css/bootstrap.min.css">
				<link rel="stylesheet" type="text/css" href="http://localhost/Property-Management/style/css/bootstrap-theme.min.css">
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
							<strong> Welcome! <q> <?php echo $row['name'];?> </q> </strong> We are here to help you manage and distribute your property as you want.
						</p>
						</div>
						<blockquote class="bg-warning" style="color:black;">
							<p>
								Before starting the measurement we want to clear one thing, we will help you to manage your property depending on your input. We can not help you to own or complete paper work along with government procedure, it will be done by you.
							</p>
							<footer> From Property-Management </footer>
						</blockquote>
					</div>
					<div>
						<ul class="list-group" style="margin-bottom:10px;">
							<li class="list-group-item">
								<h4 class="list-group-item-heading">
									property insertion process
								</h4>
								<p class="list-group-item-text text-justify">
									Simply enter all the property data in the given form input field rest of the thing will be manage by us.
								</p>
							</li>
							<li class="list-group-item">
								<h4 class="list-group-item-heading">
									property search process
								</h4>
								<p class="list-group-item-text text-justify">
									Simply enter the data you know and leave the unknown inout field. We will help you to find all the data related to your input.
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
				<div class="sitefooter"></div>
				<script src="http://localhost/Property-Management/style/js/jquery.min.js"></script>
				<script src="http://localhost/Property-Management/style/js/bootstrap.min.js"></script>
				<script src="http://localhost/Property-Management/style/js/jscript.js"></script>
			</body>
		</html>
<?php
	mysqli_close($connect);
	}
	else{
		$_SESSION['error'] = 'Request failed';
		header("location:http://localhost/Property-Management/auth/log.php");
		exit;
	}
?>