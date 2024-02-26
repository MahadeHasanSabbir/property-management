<?php
	session_start();
	if(isset($_SESSION['id'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");

		if(!isset($_GET['key'])){
			$table = "user"."$_SESSION[id]";
			
			//sql query
			$sql1 = "DELETE FROM user WHERE ID = '$_SESSION[id]'";
			$sql2 = "DROP TABLE $table";

			//take data from database
			mysqli_query($connect, $sql1);
			mysqli_query($connect, $sql2);

			//method to redirect this page to another page
			$_SESSION['error'] = "Your ID deleted!";
			mysqli_close($connect);
			header("location:http://localhost/Property-Management/auth/log.php");
			exit;
		}
		else{
			$table = "user"."$_SESSION[id]";
			
			//sql query
			$sql = "DELETE FROM $table WHERE UID = '$_GET[key]'";

			//take data from database
			mysqli_query($connect, $sql);
			mysqli_close($connect);
			header("location:http://localhost/Property-Management/profile/view.php");
			exit;
		}
	}
	else{
		header("location:http://localhost/Property-Management/auth");
		exit;
	}
?>