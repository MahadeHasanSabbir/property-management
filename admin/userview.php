<?php
	session_start();
	if(isset($_SESSION['aid']) && isset($_GET['key'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");

		//sql query to find user information from database
		$id = mysqli_real_escape_string($connect, $_GET['key']);
		$sqlquery = "SELECT * FROM user WHERE ID = '$id'";

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
				</style>
			</head>
			<body>
				<?php include 'header.php'; ?>
				<div class="container-fluid">
					<div class="page-header">
						<h4 style="display:inline-block;padding-left:2px;" class="col-lg-9 col-md-8 col-sm-7 col-xs-12"> Information of <?php echo $row["name"];?> </h4>
						<a href='<?php echo "./profileupdate.php?id=$id";?>' style="color:brown;font-weight:bold;" class="btn btn-sm bg-warning" onclick="return apermit1()">
							Edit profile
						</a>
						<a href='<?php echo "./userupdate.php?id=$id&pass=1";?>' style="color:darkred;font-weight:bold;" class="btn btn-sm bg-danger" onclick="return apermit1()">
							Reset password
						</a>
						<a href='<?php echo "./delete.php?id=$id";?>' style="color:darkred;font-weight:bold;" class="btn btn-sm bg-danger" onclick="return apermit2()">
							Delete ID
						</a>
					</div>
					<div class="jumbotron" style="line-height: 2;">
						<?php
							echo "<b class='col-sm-3 col-xs-3' style='padding-right:0px;'> User ID </b> <span class='col-sm-9'> : &nbsp", $row["ID"];
							if($row['status'] == 1){
								echo "  (active)";
							}
							echo "</span> </br>";
							echo "<b class='col-sm-3 col-xs-3'> Name </b> <span class='col-sm-9'> : &nbsp", $row["name"], "</span> </br>";
							echo "<b class='col-sm-3 col-xs-3'> Mobile </b> <span class='col-sm-9'> : &nbsp", $row["phone"], "</span> </br>";
							echo "<b class='col-sm-3 col-xs-3'> E-mail </b> <span class='col-sm-9'> : &nbsp", $row["mail"], "</span> </br>";
							echo "<b class='col-sm-3 col-xs-5' style='padding-right:0px;'> Property count </b> <span class='col-sm-9'> : &nbsp", round($row["property"]), "</span> </br>";
							echo "<b class='col-sm-3 col-xs-4' style='padding-right:0px;'> Last log-in </b> <span class='col-sm-9'> : &nbsp";
							if($row['lastlog'] == NULL){
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
				<script src="../style/js/jquery.min.js"></script>
				<script src="../style/js/bootstrap.min.js"></script>
				<script src="../style/js/jscript.js"></script>
			</body>
		</html>
<?php
	mysqli_close($connect);
	}
	else{
		$_SESSION['error'] = 'Request failed';
		header("location:./");
		exit;
	}
?>