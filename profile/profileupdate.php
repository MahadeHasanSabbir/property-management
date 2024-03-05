<?php
	session_start();
	if(isset($_SESSION['id'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");

		//sql query to find user information from database
		$sql= "SELECT name, phone, mail FROM user WHERE user.ID = '$_SESSION[id]'";

		//take data from database
		$data = mysqli_query($connect, $sql);

		//convert 2D array to 1D array
		$row = mysqli_fetch_assoc($data);
?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="UTF-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
				<title> <?php echo $row['name'];?>'s Information update Form | Property-Management </title>
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap.min.css" />
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap-theme.min.css" />
				<style>
					body {padding-top:60px;background-color:darkseagreen;}
				</style>
			</head>
			<body>
				<?php include 'header.php'; ?>
				<div class="container-fluid" role="main">
					<div class="page-header"> <h4> Information update form </h4> </div>
					<div class="jumbotron">
						<form class="form-horizontal" action="update.php" name="bgregform" onsubmit="return infovalidate()" autocomplete="off" method="post">
							<div class="form-group">
								<label class="control-label col-sm-2"> Name :</label>
								<div class="col-sm-10">
									<input type="text" name="name" class="form-control" id="name" value="<?php echo $row['name'];?>" required=""/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2"> Mobile :</label>
								<div class="col-sm-10">
									<input type="text" name="number" class="form-control" id="number" value="<?php echo $row['phone'];?>" required=""/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2"> E-mail :</label>
								<div class="col-sm-10">
									<input type="text" name="email" class="form-control" id="mail" value="<?php echo $row['mail'];?>" required=""/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2"> Password :</label>
								<div class="col-sm-10">
									<input type="password" name="password" class="form-control" id="pass" placeholder="Give the old password" title="alphanumeric and @,#,$,%,& are allow" required=""/>
								</div>
							</div>
							<button type="Submit" value="Update" class="btn btn-md btn-default col-sm-offset-1"> Update </button> <br/>
						</form>
					</div>
					<div class="jumbotron">
						<form class="form-horizontal" action="update.php" name="bgregform" onsubmit="return mailvalidate()" autocomplete="off" method="post">
							<div class="form-group">
								<label class="control-label col-md-2 col-sm-3"> New password :</label>
								<div class="col-md-10 col-sm-9">
									<input type="password" name="npassword" class="form-control" id="npass" placeholder="Creat a new password" title="alphanumeric and @,#,$,%,& are allow" required=""/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-2 col-sm-3"> Old password :</label>
								<div class="col-md-10 col-sm-9">
									<input type="password" name="password" class="form-control" id="opass" placeholder="Give the old password" title="alphanumeric and @,#,$,%,& are allow" required=""/>
								</div>
							</div>
							<button type="Submit" value="Update" class="btn btn-md btn-default col-md-offset-1 col-sm-offset-2"> Update </button> <br/>
						</form>
					</div>
				</div>
				<div id="content_footer"></div>
				<script src="../style/js/jquery.min.js"></script>
				<script src="../style/js/bootstrap.min.js"></script>
				<script src="../style/js/jscript.js"></script>
				<script>
					function infovalidate(){
						//Regular Expressions
						var namepattern = /^[A-Za-z \.]{3,35}$/i;
						var numberpattern = /^[0-9]{11}$/;
						var emailpattern = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
						var passwordpattern = /^[a-z0-9\@\#\$\%\&]{4,8}$/i;
						
						//Values from user
						var namevalue = document.getElementById('name').value;
						var numbervalue = document.getElementById('number').value;
						var emailvalue = document.getElementById('mail').value;
						var passwordvalue = document.getElementById('pass').value;
						
						//Validate the value
						if(!namepattern.test(namevalue)){
							/* document.getElementById('name').style.color="red"; */
							alert("Incorrect name");
							return false;
						}
						else if(!numberpattern.test(numbervalue)){
							alert("Incorrect number");
							return false;
						}
						else if(!emailpattern.test(emailvalue)){
							alert("Incorrect E-mail");
							return false;
						}
						else if(!passwordpattern.test(passwordvalue)){
							alert("Incorrect password");
							return false;
						}
						else{
							if(confirm("Dear, " + namevalue + ". Your information will update.\nClick ok to proceed")){
								return true;
							}else{
								return false;
							}
						}
					}
					function mailvalidate(){
						//Regular Expressions
						var passwordpattern = /^[a-z0-9\@\#\$\%\&]{4,8}$/i;
						
						//Values from user
						var passwordvalue = document.getElementById('opass').value;
						var npasswordvalue = document.getElementById('npass').value;
						
						//Validate the value
						if(!passwordpattern.test(passwordvalue)){
							alert("Incorrect password! Please follow the pattern of the password.");
							return false;
						}
						else if(!passwordpattern.test(npasswordvalue)){
							alert("Incorrect password! Please follow the pattern of the password.");
							return false;
						}
						else{
							if(confirm("Dear, " + namevalue + ". Your password will update.\nClick ok to proceed")){
								return true;
							}else{
								return false;
							}
						}
					}
				</script>
			</body>
		</html>
<?php
	mysqli_close($connect);
	}
	else{
		header("location:http://localhost/Property-Management/auth");
		exit;
	}
?>