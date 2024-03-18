<?php
	session_start();
	if(isset($_SESSION['id'])){
		//create connection with database
		$connect = mysqli_connect("localhost", "root", "", "property");

		$query = "SELECT ID, password FROM user WHERE user.ID = '$_SESSION[id]';";
		$data = mysqli_query($connect, $query);
		$row = mysqli_fetch_assoc($data);
		
		$password = mysqli_real_escape_string($connect, $_POST['password']);

		if(password_verify($password, $row['password']) && !isset($_POST['npassword'])){
			//local variable
			$name = mysqli_real_escape_string($connect, $_POST['name']);
			$number = mysqli_real_escape_string($connect, $_POST['number']);
			$email = mysqli_real_escape_string($connect, $_POST['email']);

			//sql query
			$sql = "UPDATE user SET name = '$name', mail = '$email', phone = '$number' WHERE user.ID = '$_SESSION[id]';";
			
			//method to update data from database
			mysqli_query($connect, $sql);
		}
		else if(password_verify($password, $row['password']) && isset($_POST['npassword'])){
			//local variable
			$npass = password_hash(mysqli_real_escape_string($connect, $_POST['npassword']), PASSWORD_BCRYPT);

			//keep track last password change
			$date = new DateTime();
			$passlast = $date -> format('Y-m-j');
			
			//sql query
			$sql = "UPDATE user SET password = '$npass', passlast = '$passlast' WHERE user.ID = '$_SESSION[id]';";
			
			//method to update data from database
			mysqli_query($connect, $sql);
		}
		//method to redirect this page to another page
		mysqli_close($connect);
		header("location:./about.php");
		exit;
	}
	else{
		header("location:../auth");
		exit;
	}
?>