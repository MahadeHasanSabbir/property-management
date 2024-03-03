<?php
	session_start();
	if(isset($_SESSION['id'])){
?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="UTF-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
				<title> Property search | Property-Management </title>
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap.min.css" />
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap-theme.min.css" />
				<style>
					body {padding-top:60px;background-color:darkseagreen;}
				</style>
			</head>
			<body>
				<?php include 'header.php'; ?>
				<div class="container-fluid">
				<?php
					if($_SERVER['REQUEST_METHOD'] == 'POST'){
						echo "<div class='well'>";
							include 'result.php';
				?>
				<div class="row">
					<div class="col-xs-12" style="text-align:center;">
						<a class="btn btn-default" href="<?php echo $_SERVER['PHP_SELF']?>">Search again</a>
					</div>
				</div>
				<?php
						echo "</div>";
					}
					if($_SERVER['REQUEST_METHOD'] != 'POST'){
						include 'searchform.php';
					}
				?>
				</div>
				<div class="sitefooter"></div>
				<script src="../style/js/jquery.min.js"></script>
				<script src="../style/js/bootstrap.min.js"></script>
				<script src="../style/js/jscript.js"></script>
				<script>
					function show(){
						document.getElementById("result").style="display:block;";
						return false;
					}
				</script>
			</body>
		</html>
<?php
	}
	else{
		header("location:http://localhost/Property-Management/auth");
		exit;
	}
?>