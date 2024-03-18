<?php
	session_start();
	if(isset($_SESSION['id'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");
		//local variable
		$pddata = str_replace(' ', '' , trim($_POST['pdag']));
		$pddata = str_replace(' ', '' , trim($_POST['pdag']));
		$nddata = str_replace(' ', '' , trim($_POST['dag']));
		$pkdata = str_replace(' ', '' , trim($_POST['pkhotiyan']));
		$nkdata = str_replace(' ', '' , trim($_POST['khotiyan']));
		$oodata = trim($_POST['oldowner']);
		$nodata = trim($_POST['newowner']);
		$ddata = $_POST['dolil'];
		$mdata = mysqli_real_escape_string($connect, trim($_POST['mouja']));
		$sdata = $_POST['size'];
		
		if(isset($_GET['id'])){
			$id = mysqli_real_escape_string($connect, $_GET['id']);
		}
		else{
			//sql query to find user information from database
			$sqlquery = "SELECT property FROM user WHERE ID = '$_SESSION[id]'";

			//take data from database
			$data = mysqli_query($connect, $sqlquery);
			
			//convert 2D array to 1D array
			$row = mysqli_fetch_assoc($data);
			
			//create a unique id for measurement
			$id = $row['property'] + 1;
			if($id < 10){
				$id = "00$id";
			}else if($id < 100){
				$id = "0$id";
			}
		}

		$table = "user"."$_SESSION[id]";

		if(isset($_GET['id'])){
			$sqlquery1 = "UPDATE $table SET pdagno = '$pddata', dagno = '$nddata', pkhatian = '$pkdata', khatian = '$nkdata', oldowner = '$oodata', newowner = '$nodata', dnum = '$ddata', mouja = '$mdata', size = '$sdata' WHERE $table.UID = '$id';";
		}
		else{
			//sql query for upload data to database
			$sqlquery1 = "INSERT INTO $table (UID, pdagno, dagno, pkhatian, khatian, oldowner, newowner, dnum, mouja, size) VALUES ('$id', '$pddata', '$nddata', '$pkdata', '$nkdata', '$oodata', '$nodata', '$ddata', '$mdata', '$sdata');";
			
			$sqlquery2 = "UPDATE user SET property = '$id' WHERE ID = '$_SESSION[id]'";
		}
		//method for upload data to database
		mysqli_query($connect, $sqlquery1);
		if(!isset($_GET['id'])){
			mysqli_query($connect, $sqlquery2);
		}
		//method to redirect this page to another page
		mysqli_close($connect);
		header("location:./view.php");
		exit;
	}
	else{
		header("location:../auth");
		exit;
	}
?>