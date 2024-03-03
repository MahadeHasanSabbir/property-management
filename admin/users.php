<?php
	session_start();
	if(isset($_SESSION['aid'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");

		//sql query to find user information from database
		$sqlquery = "SELECT * FROM user";

		//take data from database
		$data = mysqli_query($connect, $sqlquery);
?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="UTF-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
				<title> All user information page </title>
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap.min.css" />
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap-theme.min.css" />
				<style>
					body {padding-top:60px;background-color:darkseagreen;}
				</style>
			</head>
			<body>
				<?php include 'header.php'; ?>
				<div class="container">
					<?php
						if(mysqli_num_rows($data) > 0){
					?>
							<div class="jumbotron">
								<table class="table table-bordered">
									<caption> <h4 class="text-center"> All registered user info </h4> </caption>
									<thead>
										<tr>
											<th class="col-md-2"> User ID </th>
											<th> Name </th>
											<th class="col-md-2"> Number </th>
											<th class="col-md-2"> Number of property </th>
											<th class="col-md-2"> View profile </th>
										</tr>
									</thead>
									<tbody>
										<?php
										while($row=mysqli_fetch_assoc($data)){
										echo "<tr>
												<td> $row[ID] </td>
												<td> $row[name] </td>
												<td> $row[phone] </td>
												<td> $row[property] </td>
												<td>
													<a href='userview.php?key=$row[ID]'> <span class='glyphicon glyphicon-user'></span> Profile </a>
												</td>
											</tr>";
										}
										?>
									</tbody>
								</table>
							</div>
					<?php
						}
						else{
							echo "<div class='jumbotron'> <h4 class='text-center'> You don't have any user yet! </h4> <br/>  </div>";
							$sql = "UPDATE admin SET users = '000' WHERE admin.ID = '$_SESSION[aid]'";
							mysqli_query($connect, $sql);
							
						}
					?>
				</div>
				<div id="content_footer"></div>
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
		header("location:http://localhost/Property-Management/admin/");
		exit;
	}
?>