<?php
	session_start();
	//route for user access
	if(isset($_SESSION['id'])){
		header("location:../profile/");
		exit;
	}
	//route for admin access
	else if(isset($_SESSION['aid'])){
		header("location:../admin");
		exit;
	}
	//route for unauthorized access
	else{
		$_SESSION['error'] = 'Request failed';
		header("location:./log.php");
		exit;
	}
?>