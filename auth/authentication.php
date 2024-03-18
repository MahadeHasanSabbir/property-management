<?php
	// Start the session
	session_start();

	// Connect to the database
	$con = mysqli_connect("localhost", "root", "", "property");
	
	// Check if the form was submitted
	if(isset($_POST['id']) && isset($_POST['password'])) {
		// Get the user-entered information
		$id = mysqli_real_escape_string($con, $_POST['id']);
		$password = mysqli_real_escape_string($con, $_POST['password']);

		// Prepare the SQL statement
		$sql = "SELECT id, password FROM user WHERE id = '$id'";

		// Execute the statement
		$result = mysqli_query($con, $sql);

		// Get the user data from the database
		$row = mysqli_fetch_assoc($result);

		// Check if the user exists in the database
		if($row) {
			// Check if the password is correct
			if(password_verify($password, $row['password'])) {
				// Login successful
				$_SESSION['id'] = $id;
				$query = "UPDATE user SET status = '1' WHERE user.ID = '$_SESSION[id]';";
				mysqli_query($con, $query);
				mysqli_close($con);
				header('location:../profile/');
				exit;
			} else {
				// Login failed - incorrect password
				$_SESSION['error'] = 'Incorrect password';
				mysqli_close($con);
				header('location:./log.php');
				exit;
			}
		} else {
			// Login failed - user not found
			$_SESSION['error'] = 'User not found';
			mysqli_close($con);
			header('location:./log.php');
			exit;
		}
	} else {
		// Login failed - request failed
		mysqli_close($con);
		header('location:./');
		exit;
	}
?>
