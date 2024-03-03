<?php
	session_start();
	if(isset($_SESSION['id'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");

		//sql query to find user information from database
		$sqlquery = "SELECT * FROM user WHERE ID = '$_SESSION[id]'";

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
				<title> <?php echo $row["name"];?>'s Profile | Property-Management </title>
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap.min.css" />
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap-theme.min.css" />
				<style>
					body {padding-top:30px;background-color:darkseagreen;}
					.mb {margin-bottom:10px;padding-right:0px;padding-left:0px;}
				</style>
			</head>
			<body>
				<?php include 'header.php'; ?>
				<div class="container-fluid">
					<div class="page-header">
						<h4 style="display:inline-block;padding-left:0px;" class="col-lg-9 col-md-8 col-sm-7 col-xs-12"> Your information </h4>
						<a href='./profileupdate.php' style="color:brown;font-weight:bold;" class="btn btn-sm bg-warning" onclick="return permit1()"> Edit profile </a>
						<a href='./delete.php' style="color:darkred;font-weight:bold;" class="btn btn-sm bg-danger" onclick="return permit2()"> Delete ID </a>
						<a href='./view.php' style="color:mediumblue;font-weight:bold;" class="btn btn-sm bg-info"> Property history </a>
					</div>
					<?php
					if(password_verify($_SESSION['id'], $row['password'])){
						echo "<div class='alert alert-danger' style='margin-bottom:10px;'>
								<span class='glyphicon glyphicon-alert'></span>
								Change your password in edit profile. It is default password!
								<button type='button' class='close' data-dismiss='alert' area-label='close'>
									<span area-hidden='true'> &times; </span>
								</button>
							</div>";
					}
					?>
					<div class="jumbotron" style="display:grid;">
						<div class="col-sm-12 mb">
							<b class="col-sm-3 col-xs-3" style="padding-right:0px;"> User ID </b>
							<span class="col-sm-9 col-xs-9" style="padding-right:0px;"> : &nbsp <?php echo $row["ID"];?></span>
						</div>
						<div class="col-sm-12 mb">
							<b class="col-sm-3 col-xs-3" style="padding-right:0px;"> Name </b>
							<span class="col-sm-9 col-xs-9" style="padding-right:0px;"> : &nbsp <?php echo $row["name"]; ?></span>
						</div>
						<div class="col-sm-12 mb">
							<b class="col-sm-3 col-xs-3" style="padding-right:0px;"> Mobile </b>
							<span class="col-sm-9 col-xs-9" style="padding-right:0px;"> : &nbsp <?php echo $row["phone"]; ?></span>
						</div>
						<div class="col-sm-12 mb">
							<b class="col-sm-3 col-xs-3" style="padding-right:0px;"> E-mail </b>
							<span class="col-sm-9 col-xs-9" style="padding-right:0px;"> : &nbsp <?php echo $row["mail"]; ?></span>
						</div>
						<div class="col-sm-12 mb">
							<b class="col-sm-3 col-xs-7" style="padding-right:0px;"> Available saved property </b>
							<span class="col-sm-9 col-xs-5" style="padding-right:0px;"> : &nbsp
							<?php
								$table = "user"."$_SESSION[id]";
								$sql = "SELECT COUNT(UID) FROM $table";

								$source = mysqli_query($connect, $sql);
								$number = mysqli_fetch_array($source);
											
								echo round($number[0]); 
							?>
							</span>
						</div>
						<div class="col-sm-12 mb">
							<b class="col-sm-3 col-xs-7" style="padding-right:0px;"> Total saved property </b>
							<span class="col-sm-9 col-xs-5" style="padding-right:0px;"> : &nbsp <?php echo round($row["property"]); ?></span>
						</div>
						<div class="col-sm-12 mb">
							<b class="col-sm-3 col-xs-7"> Last log-in </b>
							<span class="col-sm-9 col-xs-5"> : &nbsp
							<?php
								if($row['lastlog'] == '0000-00-00'){
									echo "N/A </span> </br>";
								}
								else{
									$date = new DateTime();
									$today = $date -> format('Y-m-j');
									echo round((strtotime($today) - strtotime($row['lastlog']))/60/60/24), " day ago </span> </br>";
								}
							?>
						</div>
					</div>
				</div>
				<script src="../style/js/jquery.min.js"></script>
				<script src="../style/js/bootstrap.min.js"></script>
				<script src="../style/js/jscript.js"></script>
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