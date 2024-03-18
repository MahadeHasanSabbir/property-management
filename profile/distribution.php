<?php
	session_start();
	if(isset($_SESSION['id'])){
?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="UTF-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
				<title> Property distribute | Property-Management </title>
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap.min.css">
				<link rel="stylesheet" type="text/css" href="../style/css/bootstrap-theme.min.css">
				<style>
					body {padding-top:60px;background-color:darkseagreen;}
					#msg {display:none;}
				</style>
			</head>
			<body>
				<?php include 'header.php'; ?>
				<div class="container-fluid" role="main">
					<div class="page-header"> <h4> Distribution of your land </h4> </div>
					<div id="msg" class="alert alert-success col-sm-12">
						<span class="glyphicon glyphicon-info-sign"></span>
						<span id="result"> </span>
					</div>
					<div class="jumbotron">
						<form name="areadistribute" onsubmit="return calculate()" method="post">
							<h4> Total children</h4>
							<label class="control-label"> Number of son: </label>
								<input type="number" min="0" name="known" class="form-control" id="k1" required=""  value="0" autofocus>
							<label class="control-label"> Number of daughter: </label>
								<input type="number" min="0" name="aknown" class="form-control" id="k2" required="" value="0">
							<h4>Total land area</h4>
							<label class="control-label">Cent:</label>
								<input type="number" min="0" name="area" class="form-control" id="a" required=""/>
							<br/> <br/>
							<button type="submit" value="submit" class="btn btn-md btn-default" > Submit </button>
							<button type="reset" value="reset" class="btn btn-md btn-default" onclick="document.getElementById('msg').style.display='none';"> Reset </button>
						</form>
					</div>
				</div>
				<div class="sitefooter"></div>
				<script src="../style/js/jquery.min.js"></script>
				<script src="../style/js/bootstrap.min.js"></script>
				<script src="../style/js/jscript.js"></script>
				<script>
					function calculate(){
						var kid1 = document.getElementById('k1').value * 1;
						var kid2 = document.getElementById('k2').value * 1;
						
						var area = document.getElementById('a').value * 1;
						
						
						document.getElementById('msg').style.display="block";
						if(kid1 > 0 || kid2 > 0){
							var wife = area / 8;
							var child = (area - wife) / ((kid1 * 2) + kid2);
							if(kid2 == 0){
								document.getElementById('result').innerHTML="Distribution of your land. <br/> Amount of land for your wife: "+ wife.toFixed(3) +" cent<br/> Amount of land for your Son: "+ (child * 2).toFixed(3) +" cent";
							}
							else{
								document.getElementById('result').innerHTML="Distribution of your land. <br/> Amount of land for your wife: "+ wife.toFixed(3) +" cent<br/> Amount of land for your Son: "+ (child * 2).toFixed(3) +" cent<br/> Amount of land for your Daughter: "+ child.toFixed(3) +" cent";
							}
						}else{
							document.getElementById('result').innerHTML=" Due to having no child your wife can not get your land. But it up's to you to give land to your wife.";
						}
						return false;
					}
				</script>
			</body>
		</html>
<?php
	}
	else{
		header("location:../auth");
		exit;
	}
?>