<?php
	session_start();
	if(isset($_SESSION['id']) or isset($_SESSION['aid'])){
		header("location:http://localhost/Property-Management/auth");
		exit;
	}
	//create connection with database
	$connect = mysqli_connect("localhost", "root", "", "property");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<title> Property-Management </title>
		<link rel="stylesheet" type="text/css" href="./style/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="./style/css/bootstrap-theme.min.css">
		<style>
			body {padding-top:60px;background-color:darkseagreen;}
			input {padding:10px;}
			textarea {padding:10px;resize:none;}
			.center {display:grid;justify-content:center;}
			.center>div {padding:10px;}
		</style>
	</head>
	<body>
		<?php include 'header.php'; ?>
		<div class="container-fluid" role="main">
			<div class="jumbotron" style="margin-bottom:05px;">
				<p class="text-center"> <strong> Welcome to Property-Management project.</strong> We will help to manage your property.</p>
			</div>
			<!--features description-->
			<div class="well" style="margin-top:05px;margin-bottom:05px;">
				<p class="lead text-justify"> Here is the description of our features. We hope this will help you to manage your property properly and efficiently.</p>
				<div class="row">
					<div class="col-sm-4">
						<div class="panel panel-info">
						<div class="panel-heading">
							<h3 class="panel-title text-center">Save your property information</h3>
						</div>
						<div class="panel-body text-justify row-md-4" >
							Simply enter all the property data in the given form input field rest of the thing will be manage by us.
						</div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="panel panel-success">
						<div class="panel-heading">
							<h3 class="panel-title text-center">Manage your property</h3>
						</div>
						<div class="panel-body text-justify">
							Simply enter the data you know and leave the unknown inout field. We will help you to find all the data related to your input.
						</div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="panel panel-info">
						<div class="panel-heading">
							<h3 class="panel-title text-center">visit your property</h3>
						</div>
						<div class="panel-body text-justify">
							Simply fill out the form data and be sure about your data. Your property will be distributed according to your input.
						</div>
						</div>
					</div>
				</div>
				<p class="text-center"> To know more visit <a href="./about.php" class="btn btn-sm btn-link"> About </a> </p>
			</div>
			<!--contact us-->
			<div class="well" style="display:flow-root;margin-bottom:05px;">
				<div class="col-md-6" style="margin-bottom:35px;">
					<h4 class="text-center" style="margin-bottom:20px;"> Contact with us </h4>
					<div class="center">
						<div>
							<span class="glyphicon glyphicon-envelope"></span>
							<a href="mailto:info@aminship.com" > info@property-management.com </a>
						</div>
						<div>
							<span class="glyphicon glyphicon-phone"></span>
							<b>+8801000000000</b>
						</div>
						<div>
							<span class="glyphicon glyphicon-map-marker"></span>
							<i> city, upzila, district </i>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<h4 class="text-center" style="padding-bottom:10px;"> Massage with us</h4>
					<div>
						<?php
							if(isset($_POST['name'])){
								//local variable
								$name = $_POST['name'];
								$email = $_POST['email'];
								$text = $_POST['text'];
								
								//sql query for upload data to database
								$sqlquery = "INSERT INTO massage (name, email, text) VALUES ('$name', '$email', '$text')";
								
								//method for upload data to database
								mysqli_query($connect, $sqlquery);
								
								//success massage
						?>
								<div class='alert alert-success'>
									<span class='glyphicon glyphicon-info-sign'></span>
									Your massage send successfully. We will contact you within a day.
									<button type='button' class='close' data-dismiss='alert' area-label='close'>
										<span area-hidden='true'> &times; </span>
									</button>
								</div>
						<?php
							}
						?>
					</div>
					<form class="form-horizontal" name="contact" method="post">
						<div class="form-group">
							<label class="control-label col-sm-2"> Name:</label>
							<div class="col-sm-10">
								<input type="text" name="name" placeholder="Enter your name here" class="form-control" required=""/>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2"> E-mail:</label>
							<div class="col-sm-10">
								<input type="text" name="email" placeholder="Enter your e-mail here" class="form-control" required=""/>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2"> Massage:</label>
							<div class="col-sm-10">
								<textarea name="text" placeholder="Enter your massage here" class="form-control" rows="5" required=""></textarea>
							</div>
						</div>
						<button type="submit" value="submit" class="btn btn-md btn-sm btn-default col-sm-offset-2"> Send massage </button>
					</form>
				</div>
			</div>
		</div>
		<div class="sitefooter"></div>
		<script src="./style/js/jquery.min.js"></script>
		<script src="./style/js/bootstrap.min.js"></script>
		<script src="./style/js/jscript.js"></script>
	</body>
</html>
<?php
	$ip = $_SERVER['REMOTE_ADDR'];
	if (filter_var($ip, FILTER_VALIDATE_IP)) {
		
		$sql = "INSERT INTO visitors (ip) VALUES ('$ip')";
		
		mysqli_query($connect, $sql);
		mysqli_close($connect);
	}
?>