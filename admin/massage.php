<?php
	session_start();
	if(isset($_SESSION['aid'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");

		//sql query to find user information from database
		$sql = "SELECT * FROM massage";

		//take data from database
		$data = mysqli_query($connect, $sql);
?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="UTF-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
				<title> Massages from user </title>
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap.min.css">
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap-theme.min.css">
				<style>
					body {padding-top:60px;background-color:darkseagreen;}
				</style>
			</head>
			<body>
				<?php include 'header.php';?>
				<div class="container">
					<div class="jumbotron" style="display:flow-root;">
                    <?php    
                    if(mysqli_num_rows($data) > 0){
						$row = mysqli_fetch_assoc($data);
                        do{
                    ?>
						<div class="col-sm-4 text-center">
							<div class="panel panel-info">
								<div class="panel-heading">
									<h3 class="panel-title"> Massage from <?php echo"$row[name]";?></h3>
								</div>
								<div class="panel-body">
									<div style="text-align:left;">
                                    <label> Email </label>:
                                        <a href='mailto:<?php echo "$row[email]";?>'><?php echo "$row[email]";?></a>
                                    <br/>
                                    <label> Massage </label> <br/>
                                    <?php echo "'$row[text]'";?>
									</div> <br/>
									<a class="btn btn-md btn-sm btn-danger" onclick="return permit4()" href="<?php echo "./delete.php?dm=$row[time]";?>"> Delete massage </a>
								</div>
							</div>
						</div>
                    <?php
                        }while($row = mysqli_fetch_assoc($data));
                    }
                    else{
                        echo "<h3 class='text-center'> You don't have any massage from user. </h3>";
                    }
					?>
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