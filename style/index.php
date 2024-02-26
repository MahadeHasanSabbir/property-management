<?php
	session_start();
	if(isset($_SESSION['id'])){
		header("location:http://localhost/Aminship/auth");
		exit;
	}
	else{
		header("location:http://localhost/Aminship");
		exit;
	}
?>
<!DOCTYPE html>
<html>
	<script>
		alert("You don't have permision to access this folder!");
	</script>
</html>