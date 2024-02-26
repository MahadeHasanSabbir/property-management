<?php
	session_start();
	//route for user access
	if(isset($_SESSION['id'])){
		header("location:http://localhost/Property-Management/profile/");
		exit;
	}
	//route for admin access
	else if(isset($_SESSION['aid'])){
		header("location:http://localhost/Property-Management/admin");
		exit;
	}
	//route for unauthorized access
	else{
		$_SESSION['error'] = 'Request failed';
		header("location:http://localhost/Property-Management/auth/log.php");
		exit;
	}
?>