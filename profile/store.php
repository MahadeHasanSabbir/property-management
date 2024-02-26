<?php
	session_start();
	if(isset($_SESSION['id'])){
		if(isset($_GET['key'])){
			//create connection with database
			$connect = mysqli_connect("localhost", "root", "", "property");
			if (!$connect) {
				die("Connection failed: " . mysqli_connect_error());
			}
			$table = "user".$_SESSION['id'];
			$query = "SELECT * FROM $table WHERE UID = '$_GET[key]';";

			$data = mysqli_query($connect, $query);
			$row = mysqli_fetch_assoc($data);
		}
?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="UTF-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
				<title> Save property info | Property-Management </title>
				<link rel="stylesheet" type="text/css" href="http://localhost/Aminship/style/css/bootstrap.min.css" />
				<link rel="stylesheet" type="text/css" href="http://localhost/Aminship/style/css/bootstrap-theme.min.css" />
				<style>
					body {padding-top:60px;background-color:darkseagreen;}
				</style>
			</head>
			<body>
				<?php include 'header.php'; ?>
				<div class="container-fluid">
					<div class="page-header"> <h4> Save your property information </h4> </div>
					<div class="jumbotron">
						<form class="form-horizontal" <?php if(isset($row)){echo 'action="upload.php?id='.$row['UID'].'"';}else{ echo 'action="upload.php"';}?> name="saveinfo" onsubmit="return result()" method="post">
							<h4> <u> Dag: </u> </h4>
							<div class="form-group">
								<label class="control-label col-sm-1">Puraton:</label>
								<div class="col-sm-5">
									<input type="number" min="0" name="pdag" class="form-control" id="pdag"<?php if(isset($row)){ echo "value = '$row[pdagno]'";}?> placeholder="Enter your puraton dag no" title="please add ',' after each number if you have more than one" required=""/>
								</div>
								<label class="control-label col-sm-1">Notun:</label>
								<div class="col-sm-5">
									<input type="text" min="0" name="dag" class="form-control" id="dag"<?php if(isset($row)){ echo "value = '$row[dagno]'";}?> placeholder="enter your notun dag no" title="please add ',' after each number if you have more than one" required=""/>
								</div>
							</div>
							<h4> <u> Khotiyan: </u> </h4>
							<div class="form-group">
								<label class="control-label col-sm-1">Puraton:</label>
								<div class="col-sm-5">
									<input type="number" min="0" name="pkhotiyan" class="form-control" id="pkhotiyan"<?php if(isset($row)){ echo "value = '$row[pkhatian]'";}?> placeholder="Enter your puraton khotiyan no" title="please add ',' after each number if you have more than one" required=""/>
								</div>
								<label class="control-label col-sm-1">Notun:</label>
								<div class="col-sm-5">
									<input type="number" min="0" name="khotiyan" class="form-control" id="khotiyan"<?php if(isset($row)){ echo "value = '$row[khatian]'";}?> placeholder="enter your notun khotiyan no" title="please add ',' after each number if you have more than one" required=""/>
								</div>
							</div>
							<h4> <u> Owner: </u> </h4>
							<div class="form-group">
								<label class="control-label col-sm-1">Puraton:</label>
								<div class="col-sm-5">
									<input type="text" name="oldowner" class="form-control" id="pkhotiyan" <?php if(isset($row)){echo "value = '$row[oldowner]'";} ?> placeholder="Enter your old owner name" required=""/>
								</div>
								<label class="control-label col-sm-1">Notun:</label>
								<div class="col-sm-5">
									<input type="text" name="newowner" class="form-control" id="khotiyan" <?php if(isset($row)){echo "value = '$row[newowner]'";} ?> placeholder="enter your new owner name" required=""/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-1">Dolil no:</label>
								<div class="col-sm-5">
									<input type="number" min="0" name="dolil" class="form-control" id="dolil" <?php if(isset($row)){echo "value = '$row[dnum]'";} ?> placeholder="Enter your dolil no" required=""/>
								</div>
								<label class="control-label col-sm-1">Mouja:</label>
								<div class="col-sm-5">
									<input type="text" name="mouja" class="form-control" id="mouja" <?php if(isset($row)){echo "value = '$row[mouja]'";} ?> placeholder="enter your mouja name" required=""/>
								</div>
								<label class="control-label col-sm-1">Size:</label>
								<div class="col-sm-5">
									<input type="text" name="size" class="form-control" id="size" <?php if(isset($row)){echo "value = '$row[size]'";} ?> placeholder="enter your land size" required=""/>
								</div>
							</div> <br/>
							<button type="submit" value="submit" class="btn btn-md btn-default"> Save info </button>
						</form>
					</div>
				</div>
				<div class="sitefooter"></div>
				<script src="http://localhost/Aminship/style/js/jquery.min.js"></script>
				<script src="http://localhost/Aminship/style/js/bootstrap.min.js"></script>
				<script src="http://localhost/Aminship/style/js/jscript.js"></script>
				<script>
					function result(){
						if(confirm("Are you sure to save information")){
							return true;
						}else{
							return false;
						}
					}
				</script>
			</body>
		</html>
<?php
	}
	else{
		header("location:http://localhost/aminship/auth");
		exit;
	}
?>