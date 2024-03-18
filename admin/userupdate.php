<?php
	session_start();
	if(isset($_SESSION['aid'])){
		//create connection with database
		$connect = mysqli_connect("localhost", "root", "", "property");
		if(isset($_GET['id'])){
			$id = mysqli_real_escape_string($connect, $_GET['id']);
		}
		if(isset($_GET['pass']) && $_GET['pass'] == 0){
			//local variable
			$name = mysqli_real_escape_string($connect, $_POST['name']);
			$number = mysqli_real_escape_string($connect, $_POST['number']);
			$email = mysqli_real_escape_string($connect, $_POST['email']);

			//sql query
			$sql = "UPDATE user SET name = '$name', mail = '$email', phone = '$number' WHERE user.ID = '$id'";
			
			//method to update data from database
			mysqli_query($connect, $sql);
		}
		if(isset($_GET['pass']) && $_GET['pass'] == 1){
			$password = password_hash($id, PASSWORD_BCRYPT);

			//sql query
			$sql = "UPDATE user SET password = '$password' WHERE user.ID = '$id'";
			
			//method to update data from database
			mysqli_query($connect, $sql);
		}
		//method to redirect this page to another page
		mysqli_close($connect);
		header("location:./userview.php?key=$id");
		exit;
	}
	else{
		$_SESSION['error'] = 'Request failed';
		header("location:./");
		exit;
	}
?>