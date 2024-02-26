<?php
	session_start();
	if(isset($_SESSION['aid'])){
		//create connection with database
		$connect = mysqli_connect("localhost", "root", "", "property");
		if(isset($_GET['id'])){
			$id = $_GET['id'];
		}
		if(isset($_GET['pass']) && $_GET['pass'] == 0){
			//local variable
			$name = $_POST['name'];
			$number = $_POST['number'];
			$email = $_POST['email'];

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
		header("location:http://localhost/Property-Management/admin/userview.php?key=$id");
		exit;
	}
	else{
		$_SESSION['error'] = 'Request failed';
		header("location:http://localhost/Property-Management/admin/");
		exit;
	}
?>