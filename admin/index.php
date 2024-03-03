<?php
	session_start();
	if(isset($_SESSION['aid']) && !isset($_SESSION['success'])){
		header('location: http://localhost/Property-Management/admin/adminprofile.php');
		exit;
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<title> Admin Log in page </title>
		<link rel="stylesheet" type="text/css" href="../style/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="../style/css/bootstrap-theme.min.css">
		<style>
			body {padding-top:60px;}
			.justify {display:grid;justify-content:center;text-align:center;width:70vw;}
		</style>
	</head>
	<body>
		<div class="container justify">
			<div class="page-header"> <h4> Admin log in form </h4> </div>
			<div class="jumbotron">
				<?php
					if(isset($_SESSION['error'])){
						echo "
							<div class='alert alert-danger'>
								<span class='glyphicon glyphicon-alert'></span>
								$_SESSION[error]
								<button type='button' class='close' data-dismiss='alert' area-label='close'>
									<span area-hidden='true'> &times; </span>
								</button>
							</div>";
						$_SESSION = array();
						session_destroy();
					}
					if(isset($_SESSION['success'])){
						echo "
							<div class='alert alert-success'>
								<span class='glyphicon glyphicon-info-sign'></span>
								$_SESSION[success]
								<button type='button' class='close' data-dismiss='alert' area-label='close'>
									<span area-hidden='true'> &times; </span>
								</button>
							</div>";
						$_SESSION = array();
						session_destroy();
					}

				?>
				<form class="form-horizontal" action="authentication.php" method="post">
					<div class="form-group">
						<label class="control-label"> Admin ID </label>
						<input type="text" name="id" class="form-control" id="id" placeholder="Enter admin ID" required="" autofocus />
					</div>
					<div class="form-group">
						<label class="control-label"> Password </label>
						<input type="password" name="password" class="form-control" id="pass" placeholder="Enter admin password" title="alphanumeric and @,#,$,%,& are allow" required=""/>
					</div>
					<button class="btn btn-md btn-default" type="Submit" value="Login"> Login </button>
					<button class="btn btn-md btn-default" type="Reset" value="Reset"> Reset </button> <br/>
				</form>
			</div>
		</div>
		<script src="../style/js/jquery.min.js"></script>
		<script src="../style/js/bootstrap.min.js"></script>
		<script>
			function valid(){
				var id = document.getElementById('id').value;
				var pass = document.getElementById('pass').value;

				var sampleID = /^[A-Za-z0-9]{4,10}$/i;
				var samplePass = /^[A-Za-z0-9\@\#\$\%\&]{4,8}$/i;

				if(!sampleID.test(id)){
					alert("!Invalid ID");
					return false;
				}
				else if(!samplePass.test(pass)){
					alert("!Invalid Password");
					return false;
				}
				return true;
			}
		</script>
	</body>
</html>