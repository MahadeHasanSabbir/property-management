<?php
	session_start();
	if(isset($_SESSION['aid'])){
		header("location:../admin");
		exit;
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<title> Sign up Form | Property-Management </title>
		<link rel="stylesheet" type="text/css" href="../style/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="../style/css/bootstrap-theme.min.css">
		<style>
			body {padding-top:60px;background-color:darkseagreen;}
		</style>
	</head>
	<body>
		<?php include 'header.php'; ?>
		<div class="container-fluid">
			<div class="jumbotron">
				<h2 class="col-sm-offset-1"> Sign up form </h2> <br/>
				<form class="form-horizontal" action="action.php" name="regform" onsubmit="return validate()" autocomplete="off" method="post">
					<div class="form-group">
						<label class="control-label col-sm-2"> Name :</label>
						<div class="col-sm-9">
							<input type="text" name="name" class="form-control" id="name" placeholder="Enter your name." required="" autofocus />
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2"> Mobile :</label>
						<div class="col-sm-9">
							<input type="text" name="number" class="form-control" id="number" placeholder="01........." required=""/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2"> E-mail :</label>
						<div class="col-sm-9">
							<input type="text" name="email" class="form-control" id="mail" placeholder="Enter a valid email." required=""/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2"> Password :</label>
						<div class="col-sm-9">
							<input type="password" name="password" class="form-control" id="pass" placeholder="Create a password in 4 to 8 character" title="alphanumeric and @,#,$,%,& are allow" required=""/>
						</div>
					</div>
					<br/>
					<button type="Submit" value="Submit" class="btn btn-md col-sm-offset-1 btn-default"> Sign up </button>
					<button type="Reset" value="Reset" class="btn btn-md btn-default"> Reset </button> <br/>
				</form>
			</div>
		</div>
		<div id="content_footer"></div>
		<script src="../style/js/jquery.min.js"></script>
		<script src="../style/js/bootstrap.min.js"></script>
		<script src="../style/js/jscript.js"></script>
		<script>
			function validate(){
				//Regular Expressions
				var namepattern = /^[A-Za-z \.]{3,35}$/i;
				var numberpattern = /^[0-9]{11}$/;
				var emailpattern = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
				var passwordpattern = /^[A-Za-z0-9\@\#\$\%\&]{4,8}$/i;
				
				//Values from user
				var namevalue = document.getElementById('name').value;
				var numbervalue = document.getElementById('number').value;
				var emailvalue = document.getElementById('mail').value;
				var passwordvalue = document.getElementById('pass').value;
				
				//Validate the value
				if(!namepattern.test(namevalue)){
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
					alert("Incorrect password! please follow instruction");
					return false;
				}
				else{
					if(confirm("Thank you, " + namevalue + ". Registration form fill-up!\nRemember Your password for future use.\nClick ok to proceed")){
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
	$ip = $_SERVER['REMOTE_ADDR'];
	if (filter_var($ip, FILTER_VALIDATE_IP)) {
		
		$connect = mysqli_connect("localhost", "root", "", "property");
		$sql = "INSERT INTO visitors (ip) VALUES ('$ip')";
		
		mysqli_query($connect, $sql);
		mysqli_close($connect);
	}
?>