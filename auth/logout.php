<?php
	//start session and create connection with mysql
	session_start();
	if(isset($_SESSION['id'])){
		$connect = mysqli_connect("localhost", "root", "", "property");
		//took logout time
		$date = new DateTime();
		$time = $date -> format('Y-m-j');
		//sql for update logout time
		$sql = "UPDATE user SET lastlog = '$time', status = '0' WHERE user.ID = '$_SESSION[id]';";
		//execute sql command
		mysqli_query($connect, $sql);
		//log out massage
		$_SESSION['success'] = "Log out successful";
		//redirect to login page
		mysqli_close($connect);
		header("location:http://localhost/Property-Management/auth/log.php");
		exit;
	}
	else{
		header("location:http://localhost/Property-Management/auth");
		exit;
	}
?>