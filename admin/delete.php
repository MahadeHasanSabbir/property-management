<?php
	session_start();
	if(isset($_SESSION['aid'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");

		//delete user data
		if(isset($_GET['id'])){
			$id = mysqli_real_escape_string($connect, $_GET['id']);
			$table = "user"."$id";

			//sql query
			$sql1 = "DELETE FROM user WHERE ID = '$id'";
			$sql2 = "DROP TABLE $table";

			//take data from database
			mysqli_query($connect, $sql1);
			mysqli_query($connect, $sql2);

			//method to redirect this page to another page
			mysqli_close($connect);
			header("location:./users.php");
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
			header("location:./adminprofile.php");
			exit;
		}
		//delete all visitor information
		else if(isset($_GET['dd']) && $_GET['dd'] == 1){
			$query = "DELETE FROM visitors";
			mysqli_query($connect, $query);
			
			mysqli_close($connect);
			header("location:./adminprofile.php");
			exit;
		}
		//delete massages from user
		else if(isset($_GET['dm'])){
			$dm = mysqli_real_escape_string($connect, $_GET['dm']);
			$query = "DELETE FROM massage WHERE massage.time = '$dm'";
			mysqli_query($connect, $query);
			
			mysqli_close($connect);
			header("location:./massage.php");
			exit;
		}
		//redirect if there has no job
		else{
			mysqli_close($connect);
			header("location:./users.php");
			exit;
		}
	}
	else{
		$_SESSION['error'] = 'Request failed';
		header("location:./");
		exit;
	}
?>