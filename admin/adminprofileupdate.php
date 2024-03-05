<?php
	session_start();
	if(isset($_SESSION['aid'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");

		//sql query to find user information from database
		$sql = "SELECT * FROM admin WHERE admin.ID = '$_SESSION[aid]'";

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
				<title> Admin panel </title>
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap.min.css">
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap-theme.min.css">
				<style>
					body {padding-top:60px;background-color:darkseagreen;}
				</style>
			</head>
			<body>
				<?php include 'header.php';?>
				<div class="container">
					<div class="page-header"> <h4> Admin information update form </h4> </div>
					<div class="jumbotron">
						<form action="action.php" onsubmit="return namevalidate()" method="post" >
							<label class="control-label"> Admin ID </label>:  <input type="text" name="name" class="form-control" id="name" value="<?php echo $row['ID'];?>" required=""/><br/>
							<label class="control-label"> Password </label>:  <input type="password" name="password" class="form-control" id="pass"  placeholder="Enter the old password" title="alphanumeric and @,#,$,%,& are allow" required=""/> <br/>
							<button type="Submit" value="Update" class="btn btn-md btn-default"> Update </button> <br/>
						</form>
					</div>
					<div class="jumbotron">
						<form action="action.php" onsubmit="return passvalidate()" method="post" >
							<label class="control-label"> New password </label>: 
							<input type="password" name="npassword" class="form-control" id="npass" placeholder="Create a new password" title="alphanumeric and @,#,$,%,& are allow" required=""/>
							<br/>
							<label class="control-label"> Old password </label>: 
							<input type="password" name="password" class="form-control" id="opass"  placeholder="Enter the old password" title="alphanumeric and @,#,$,%,& are allow" required=""/>
							<br/>
							<button type="Submit" value="Update" class="btn btn-md btn-default"> Update </button> <br/>
						</form>
					</div>
				</div>
				<div id="footer"></div>
				<script src="../style/js/jquery.min.js"></script>
				<script src="../style/js/bootstrap.min.js"></script>
				<script src="../style/js/jscript.js"></script>
				<script>
					function namevalidate(){
						//Regular Expressions
						var namepattern = /^[A-Za-z0-9]{4,10}$/i;
						var passwordpattern = /^[A-Za-z0-9\@\#\$\%\&]{4,8}$/i;
						
						//Values from user
						var namevalue = document.getElementById('name').value;
						var passwordvalue = document.getElementById('pass').value;
						
						//Validate the value
						if(!namepattern.test(namevalue)){
							alert("Incorrect ID! ID can be alphanumeric only.");
							return false;
						}
						else if(!passwordpattern.test(passwordvalue)){
							alert("Incorrect old password! Please follow the pattern of the password.");
							return false;
						}
						else
							if(confirm("your information will update.\nClick ok to proceed")){
								return true;
							}else{
								return false;
							}
						}
					function passvalidate(){
						//Regular Expressions
						var passwordpattern = /^[A-Za-z0-9\@\#\$\%\&]{4,8}$/i;
						
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
						else
							if(confirm("your password will update.\nClick ok to proceed")){
								return true;
							}else{
								return false;
							}
						}
				</script>
			</body>
		</html>
<?php
	mysqli_close($connect);
	}
	else{
		$_SESSION['error'] = 'Request failed';
		header("location:http://localhost/Property-Management/admin/");
		exit;
	}
?>