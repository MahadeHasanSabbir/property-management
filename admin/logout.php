<?php
	session_start();
	if(isset($_SESSION['aid'])){
		$connect = mysqli_connect("localhost", "root", "", "property");
	
		$date = new DateTime();
		$time = $date -> format('Y-m-j');
	
		$sql = "UPDATE admin SET lastlog = '$time' WHERE admin.ID = '$_SESSION[aid]';";
	
		mysqli_query($connect, $sql);

		$_SESSION['success'] = "Log out successful";
		mysqli_close($connect);
		header("location:./");
		exit;
	}
	else{
		$_SESSION['error'] = 'Request failed';
		header("location:./");
		exit;
	}
?>