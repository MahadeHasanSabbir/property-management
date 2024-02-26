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
				<link rel="stylesheet" type="text/css" href="http://localhost/Property-Management/style/css/bootstrap.min.css" />
				<link rel="stylesheet" type="text/css" href="http://localhost/Property-Management/style/css/bootstrap-theme.min.css" />
				<style>
					body {padding-top:60px;background-color:darkseagreen;}
				</style>
			</head>
			<body>
				<?php include 'header.php'; ?>
				<div class="container-fluid">
					<div class="page-header">
						<h4> Search your property </h4>
					</div>
					<div class="well" style="display:none;" id="result">
						<?php include 'result.php'; ?>
					</div>
					<div class="jumbotron">
						<form class="form-horizontal" name="search" method="post" onsubmit="return show()">
							<h4>Dag:</h4>
							<div class="form-group">
								<div class="col-sm-5">
									<input type="number" min="0" name="dag" class="form-control" id="dag" autofocus="" placeholder="Enter your dag no"/>
								</div>
								<div class="col-sm-5">
									<select name="dagvalue" id="dagvalue">
										<option value="0">Puraton</option>
										<option value="1">Notun</option>
									</select>
								</div>
							</div>
							<h4> Khotiyan:</h4>
							<div class="form-group">
								<div class="col-sm-5">
									<input type="number" min="0" name="khotiyan" class="form-control" id="khotiyan" placeholder="Enter your khotiyan no"/>
								</div>
								<div class="col-sm-5">
									<select name="khotiyanvalue" id="khotiyanvalue">
										<option value="0">Puraton</option>
										<option value="1">Notun</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-1">Dolil no:</label>
								<div class="col-sm-5">
									<input type="number" min="0" name="dolil" class="form-control" id="dolil" placeholder="Enter your dolil no"/>
								</div>
								<label class="control-label col-sm-1">Mouja:</label>
								<div class="col-sm-5">
									<input type="text" name="mouja" class="form-control" id="mouja" placeholder="enter your mouja name"/>
								</div>
							</div> <br/>
							<button type="submit" value="submit" class="btn btn-md btn-default"> Search info </button>
							<button type="reset" value="reset" class="btn btn-md btn-default"> Reset </button>
						</form>
					</div>
				</div>
				<div class="sitefooter"></div>
				<script src="http://localhost/Property-Management/style/js/jquery.min.js"></script>
				<script src="http://localhost/Property-Management/style/js/bootstrap.min.js"></script>
				<script src="http://localhost/Property-Management/style/js/jscript.js"></script>
				<script>
					function show(){
						document.getElementById("result").style="display:inline-block;";
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