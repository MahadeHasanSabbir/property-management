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
				<title> Save property information | Property-Management </title>
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap.min.css" />
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap-theme.min.css" />
				<style>
					body {padding-top:60px;background-color:darkseagreen;}
				</style>
			</head>
			<body>
				<?php include 'header.php'; ?>
				<div class="container-fluid">
					<div class="page-header text-center"> <h4> Save your property information </h4> </div>
					<div class="jumbotron">
						<form class="form-horizontal" <?php if(isset($row)){echo 'action="upload.php?id='.$row['UID'].'"';}else{ echo 'action="upload.php"';}?> name="saveinfo" onsubmit="return inputCheck()" method="post">
							<h4> <u> Mark no: </u> </h4>
							<div class="form-group">
								<label class="control-label col-sm-1">Old:</label>
								<div class="col-sm-5">
									<input type="text" min="0" name="pdag" class="form-control" id="pdag"<?php if(isset($row)){ echo "value = '$row[pdagno]'";}?> placeholder="Enter your old mark no Example(1234,56...)" title="please add ',' after each number if you have more than one" required="" autofocus/>
								</div>
								<label class="control-label col-sm-1">New:</label>
								<div class="col-sm-5">
									<input type="text" min="0" name="dag" class="form-control" id="dag"<?php if(isset($row)){ echo "value = '$row[dagno]'";}?> placeholder="enter your new mark no Example(1234,56...)" title="please add ',' after each number if you have more than one" required=""/>
								</div>
							</div>
							<h4> <u> Ledger: </u> </h4>
							<div class="form-group">
								<label class="control-label col-sm-1">Old:</label>
								<div class="col-sm-5">
									<input type="text" min="0" name="pkhotiyan" class="form-control" id="pkhotiyan" <?php if(isset($row)){ echo "value = '$row[pkhatian]'";}?> placeholder="Enter your old ledger no Example(1234,56...)" title="please add ',' after each number if you have more than one" required=""/>
								</div>
								<label class="control-label col-sm-1">New:</label>
								<div class="col-sm-5">
									<input type="text" min="0" name="khotiyan" class="form-control" id="khotiyan" <?php if(isset($row)){ echo "value = '$row[khatian]'";}?> placeholder="enter your new ledger no Example(1234,56...)" title="please add ',' after each number if you have more than one" required=""/>
								</div>
							</div>
							<h4> <u> Owner: </u> </h4>
							<div class="form-group">
								<label class="control-label col-sm-1">Old:</label>
								<div class="col-sm-5">
									<input type="text" name="oldowner" class="form-control" id="oldowner" <?php if(isset($row)){echo "value = '$row[oldowner]'";} ?> placeholder="Enter your old owner name" required=""/>
								</div>
								<label class="control-label col-sm-1">New:</label>
								<div class="col-sm-5">
									<input type="text" name="newowner" class="form-control" id="newowner" <?php if(isset($row)){echo "value = '$row[newowner]'";} ?> placeholder="enter your new owner name" required=""/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-1">Deed no:</label>
								<div class="col-sm-5">
									<input type="number" min="0" name="dolil" class="form-control" id="dolil" <?php if(isset($row)){echo "value = '$row[dnum]'";} ?> placeholder="Enter your deed no" required=""/>
								</div>
								<label class="control-label col-sm-1">Mouja:</label>
								<div class="col-sm-5">
									<input type="text" name="mouja" class="form-control" id="mouja" <?php if(isset($row)){echo "value = '$row[mouja]'";} ?> placeholder="enter your mouja name" required=""/>
								</div>
								<label class="control-label col-sm-1">Size:</label>
								<div class="col-sm-5">
									<input type="number" min="0.0" name="size" class="form-control" id="size" <?php if(isset($row)){echo "value = '$row[size]'";} ?> placeholder="enter your land size in cent" required=""/>
								</div>
							</div> <br/>
							<button type="submit" value="submit" class="btn btn-md btn-default"> Save info </button>
							<button type="reset" value="reset" class="btn btn-md btn-default"> Reset info </button>
						</form>
					</div>
				</div>
				<div class="sitefooter"></div>
				<script src="../style/js/jquery.min.js"></script>
				<script src="../style/js/bootstrap.min.js"></script>
				<script src="../style/js/jscript.js"></script>
				<script>
					function inputCheck(){
						//Regular Expressions
						var textPattern = /^[A-Za-z \.]{3,35}$/i;
						var numberPattern = /^\s*\d{1,4}(?:\s*,\s*\d{1,4})*\s*$/;
						
						//Values from user
						var pdagValue = document.getElementById('pdag').value;
						var dagValue = document.getElementById('dag').value;
						var pkhotiyanValue = document.getElementById('pkhotiyan').value;
						var khotiyanValue = document.getElementById('khotiyan').value;
						var oldOwnerValue = document.getElementById('oldowner').value;
						var newOwnerValue = document.getElementById('newowner').value;
						
						//Validate the value
						if(!numberPattern.test(pdagValue)){
							document.getElementById('pdag').style.color="red";
							alert("Incorrect value of old mark or formate error");
							return false;
						}
						if(!numberPattern.test(dagValue)){
							document.getElementById('dag').style.color="red";
							alert("Incorrect value of mark or formate error");
							return false;
						}
						if(!numberPattern.test(pkhotiyanValue)){
							document.getElementById('pkhotiyan').style.color="red";
							alert("Incorrect value of old ledger or formate error");
							return false;
						}
						if(!numberPattern.test(khotiyanValue)){
							document.getElementById('khotiyan').style.color="red";
							alert("Incorrect value of ledger or formate error");
							return false;
						}
						if(!textPattern.test(oldOwnerValue)){
							document.getElementById('oldowner').style.color="red";
							alert("Incorrect old owner name or formate error");
							return false;
						}
						if(!textPattern.test(newOwnerValue)){
							document.getElementById('newowner').style.color="red";
							alert("Incorrect new owner name or formate error");
							return false;
						}
						else{
							if(confirm(" your information will store into database.\nClick ok to proceed")){
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
	}
	else{
		header("location:http://localhost/Property-Management/auth");
		exit;
	}
?>