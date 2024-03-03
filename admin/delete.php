<?php
	session_start();
	if(isset($_SESSION['aid'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");

		//delete user data
		if(isset($_GET['id'])){

			$table = "user"."$_GET[id]";

			//sql query
			$sql1 = "DELETE FROM user WHERE ID = '$_GET[id]'";
			$sql2 = "DROP TABLE $table";

			//take data from database
			mysqli_query($connect, $sql1);
			mysqli_query($connect, $sql2);

			//method to redirect this page to another page
			mysqli_close($connect);
			header("location:http://localhost/Property-Management/admin/users.php");
			exit;
		}
		//delete todays visitor information
		else if(isset($_GET['dd']) && $_GET['dd'] == 0){
			$date = new DateTime();
			$day1 = $date -> format('Y-m');
			$day2 = $date -> format('j');
			if($day2 < 10){
				$day2 = "0$day2";
			}
			$day = $day1."-".$day2;
			
			$query = "DELETE FROM visitors WHERE time LIKE '$day%'";
			mysqli_query($connect, $query);
			
			mysqli_close($connect);
			header("location:http://localhost/Property-Management/admin/adminprofile.php");
			exit;
		}
		//delete all visitor information
		else if(isset($_GET['dd']) && $_GET['dd'] == 1){
			$query = "DELETE FROM visitors";
			mysqli_query($connect, $query);
			
			mysqli_close($connect);
			header("location:http://localhost/Property-Management/admin/adminprofile.php");
			exit;
		}
		//delete massages from user
		else if(isset($_GET['dm'])){
			$query = "DELETE FROM massage WHERE massage.time = '$_GET[dm]'";
			mysqli_query($connect, $query);
			
			mysqli_close($connect);
			header("location:http://localhost/Property-Management/admin/massage.php");
			exit;
		}
		//redirect if there has no job
		else{
			mysqli_close($connect);
			header("location:http://localhost/Property-Management/admin/users.php");
			exit;
		}
	}
	else{
		$_SESSION['error'] = 'Request failed';
		header("location:http://localhost/Property-Management/admin/");
		exit;
	}
?>