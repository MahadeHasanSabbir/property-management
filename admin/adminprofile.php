<?php
	session_start();
	if(isset($_SESSION['aid'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");

		//sql query to find user information from database
		$sql = "SELECT * FROM admin WHERE admin.id = '$_SESSION[aid]'";

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
					<?php
						$date = new DateTime();
						$today = $date -> format('Y-m-j');
						if((strtotime($today) - strtotime($row['passlast']))/60/60/24 > 90){
							echo "<div class='alert alert-danger' style='margin-bottom:10px;'>
									<span class='glyphicon glyphicon-alert'></span>
									Change your password. It is older than 90 day!
									<button type='button' class='close' data-dismiss='alert' area-label='close'>
										<span area-hidden='true'> &times; </span>
									</button>
								</div>";
						}
						echo "<div class='jumbotron' style='margin-bottom:10px;'>";
						echo "<b class='col-sm-3 col-xs-4'> Admin </b> <span class='col-sm-9'> : &nbsp", $row["ID"], "</span></br>";
						echo "<b class='col-sm-3 col-xs-4'> Last password change </b> <span class='col-sm-9'> : &nbsp", $row["passlast"], "</span></br>";
						echo "<b class='col-sm-3 col-xs-4'> Last time log-in </b> <span class='col-sm-9'> : &nbsp";
						if($row['lastlog'] == NULL){
							echo "N/A </span> </br>";
						}
						else{
							echo round((strtotime($today) - strtotime($row['lastlog']))/60/60/24), " day ago </span> </br>";
						}
						echo "<hr/>";
					?>
					<a href="./adminprofileupdate.php" class="btn btn-md btn-sm btn-warning" onclick="return permit1()"> Edit info </a>
					</div>
					<div class="well">
						<p class="text-center"> Website reach information </p>
						<div class="row">
							<div class="col-sm-4 text-center">
								<div class="panel panel-info">
									<div class="panel-heading">
										<h3 class="panel-title">Number of page view today</h3>
									</div>
									<div class="panel-body" >
										<?php
											$day1 = $date -> format('Y-m');
											$day2 = $date -> format('j');
											if($day2 < 10){
												$day2 = "0$day2";
											}
											$day = $day1."-".$day2;
											$sql = "SELECT COUNT(ip) FROM visitors WHERE time LIKE '$day%'";
											
											$source = mysqli_query($connect, $sql);
											$number = mysqli_fetch_array($source);
											
											echo $number[0];
										?>
									</div>
								</div>
							</div>
							<div class="col-sm-4 text-center">
								<div class="panel panel-info">
									<div class="panel-heading">
										<h3 class="panel-title">Number of visitor today</h3>
									</div>
									<div class="panel-body">
										<?php
											$sql = "SELECT COUNT(DISTINCT ip) FROM visitors WHERE time LIKE '$day%'";

											$source = mysqli_query($connect, $sql);
											$number = mysqli_fetch_array($source);
											
											echo $number[0];
										?>
									</div>
								</div>
							</div>
							<div class="col-sm-4 text-center">
								<div class="panel panel-info">
									<div class="panel-heading">
										<h3 class="panel-title">Number of visitor in lifetime</h3>
									</div>
									<div class="panel-body">
										<?php
											$sql = "SELECT COUNT(DISTINCT ip) FROM visitors";

											$source = mysqli_query($connect, $sql);
											$number = mysqli_fetch_array($source);
											
											echo $number[0];
										?>
									</div>
								</div>
							</div>
							<div class="btn-group col-sm-offset-5 col-xs-offset-4">
								<a class="btn btn-md btn-sm btn-info" onclick="return permit4()" href="./delete.php?dd=0"> Clear today's info</a>
								<a class="btn btn-md btn-sm btn-warning" onclick="return permit4()" href="./delete.php?dd=1"> Clear all info</a>
							</div>
						</div> <hr/>
						<p class="text-center"> Website user information </p>
						<div class="row">
							<div class="col-sm-4 text-center">
								<div class="panel panel-success">
									<div class="panel-heading">
										<h3 class="panel-title">Number of user log-in today</h3>
									</div>
									<div class="panel-body">
										<?php
											$now1 = $date -> format('Y-m-');
											$now2 = $date -> format('j');
											if($now2 < 10){
												$now2 = "0$now2";
											}
											$now = $now1.$now2;
											$sql = "SELECT COUNT(ID) FROM user WHERE lastlog LIKE '$now'";

											$source = mysqli_query($connect, $sql);
											$number = mysqli_fetch_array($source);
											
											echo $number[0];
										?>
									</div>
								</div>
							</div>
							<div class="col-sm-4 text-center">
								<div class="panel panel-success">
									<div class="panel-heading">
										<h3 class="panel-title">Number of active user</h3>
									</div>
									<div class="panel-body">
										<?php
											$sql = "SELECT COUNT(ID) FROM user";

											$source = mysqli_query($connect, $sql);
											$number = mysqli_fetch_array($source);
											
											echo $number[0];
										?>
									</div>
								</div>
							</div>
							<div class="col-sm-4 text-center">
								<div class="panel panel-success">
									<div class="panel-heading">
										<h3 class="panel-title">Number of registered user</h3>
									</div>
									<div class="panel-body">
										<?php echo round($row["users"]); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="footer"></div>
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