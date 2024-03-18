<!DOCTYPE html>
<html>
	<script>
		alert("You don't have permission to access this folder!");
	</script>
	<?php
		session_start();
		if(isset($_SESSION['id'])){
			header("location:../auth");
			exit;
			
		}
		else if(isset($_SESSION['aid'])){
			header("location:../admin");
			exit;
		}
		else{
			header("location:../");
			exit;
		}
	?>
</html>