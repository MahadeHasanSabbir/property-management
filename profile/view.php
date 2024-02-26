<?php
	session_start();
	if(isset($_SESSION['id'])){
		//create connection with database
		$connect = mysqli_connect("localhost","root","","property");

		$table = "user"."$_SESSION[id]";

		//sql query to find user information from database
		$sqlquery = "SELECT * FROM $table";

		//take data from database
		$data1 = mysqli_query($connect, $sqlquery);
?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="UTF-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
				<title> All saved property information page </title>
				<link rel="stylesheet" type="text/css" href="http://localhost/Property-Management/style/css/bootstrap.min.css" />
				<link rel="stylesheet" type="text/css" href="http://localhost/Property-Management/style/css/bootstrap-theme.min.css" />
				<style>
					body {padding-top:60px;background-color:darkseagreen;}
					.center {display:grid;justify-content:center;}
					.pagination>.active>a {background-color:darkseagreen;}
				</style>
			</head>
			<body>
				<?php include 'header.php'; ?>
				<div class="container-fluid">
					<div class="form-group">
						<label for="itemsPerPage">Items per page:</label>
						<select class="form-control" id="itemsPerPage" onchange="changeItemsPerPage()">
							<option value="10" <?php if(isset($_GET['itemPerPage']) && $_GET['itemPerPage'] == 10){ echo "selected";}?>>10</option>
							<option value="15" <?php if(isset($_GET['itemPerPage']) && $_GET['itemPerPage'] == 15){ echo "selected";}?>>15</option>
							<option value="20" <?php if(isset($_GET['itemPerPage']) && $_GET['itemPerPage'] == 20){ echo "selected";}?>>20</option>
						</select>
					</div>
				<?php
					if(mysqli_num_rows($data1) > 0){
						$items_per_page = isset($_GET['itemsPerPage']) ? $_GET['itemsPerPage'] : 10;
						$total_items_query = "SELECT COUNT(*) as total FROM $table";
						$total_items_result = mysqli_query($connect, $total_items_query);
						$total_items = mysqli_fetch_assoc($total_items_result);
						$total_pages = ceil($total_items['total'] / $items_per_page);

						$page = isset($_GET['page']) ? $_GET['page'] : 1;
						$page = max(1, min($total_pages, $page));
						$offset = ($page - 1) * $items_per_page;

						$query = "SELECT * FROM $table LIMIT $offset, $items_per_page";
						$data = mysqli_query($connect, $query);

				?>
						<div class="well" style="margin-bottom:0px;">
							<table class="table table-bordered">
								<caption style="text-align:center;padding-top:0px;">
									<h4> All saved measurement </h4>
								</caption>
								<thead>
									<tr>
										<th> NO </th>
										<th> Dolil NO </th>
										<th> Dag No </th>
										<th> Khotiyan No </th>
										<th> Old Owner </th>
										<th> Area of land (cent) </th>
										<th class="col-md-2 text-center"> Edit </th>
										<th class="col-md-2 text-center"> Delete </th>
									</tr>
								</thead>
								<tbody>
									<?php
									while($row=mysqli_fetch_assoc($data)){
									echo "<tr>";
										echo "<td>" . $row['UID'] . "</td>";
										echo "<td>" . $row['dnum'] . "</td>";
										echo "<td>" . $row['dagno'] . "</td>";
										echo "<td>" . $row['khatian'] . "</td>";
										echo "<td>" . $row['oldowner'] . "</td>";
										echo "<td>" . $row['size'] . "</td>";
										echo "<td class='text-center'>
											<a href='store.php?key=$row[UID]' onclick='permit1()'>
												<span class='glyphicon glyphicon-upload-alt'></span> edit
											</a>
										</td>
										<td class='text-center'>
											<a href='delete.php?key=$row[UID]' onclick='return permit3()'>
												<span class='glyphicon glyphicon-trash'></span> Delete measurement
											</a>
										</td>
									</tr>";
									}
									?>
								</tbody>
							</table>
				<?php
							if ($total_pages > 1) {
								echo '<div class="center"><ul class="pagination">';
								for ($i = 1; $i <= $total_pages; $i++) {
									echo "<li";
									if ($i == $page) {
										echo " class='active'";
									}
									echo '><a href="?page=' . $i . '">' . $i . '</a></li>';
								}
								echo "</ul></div>";
							}
						echo "</div>";
					}
					else{
				?>
						<div class='jumbotron'>
							<h4 class='text-center'> You don't save any property yet! </h4> <br/> <br/>
							<h5 class='text-center'> Save property for visit those. </h5>
						</div>
				<?php
						$sql = "UPDATE user SET property = '000' WHERE user.ID = '$_SESSION[id]'";
						mysqli_query($connect, $sql);
					}
				?>
				</div>
				<div id="content_footer"></div>
				<script src="http://localhost/Property-Management/style/js/jquery.min.js"></script>
				<script src="http://localhost/Property-Management/style/js/bootstrap.min.js"></script>
				<script src="http://localhost/Property-Management/style/js/jscript.js"></script>
				<script>
					function changeItemsPerPage() {
						var selectedValue = document.getElementById("itemsPerPage").value;
						var url = window.location.href;

						// Check if URL already contains GET parameters
						if (url.indexOf('?') > -1) {
							// URL already has parameters, append new parameter
							url += '&itemsPerPage=' + selectedValue;
							/* if(url.indexOf('0') > -1 || url.indexOf('5') > -1){
								url = url - (sizeof(url) - 2);
								url += selectedValue;
							} */
						} else {
							// URL doesn't have parameters, add new parameter
							url += '?itemsPerPage=' + selectedValue;
						}

						// Redirect to the updated URL
						window.location.href = url;
					}
				</script>
			</body>
		</html>
<?php
	mysqli_close($connect);
	}
	else{
		header("location:http://localhost/Property-Management/auth");
		exit;
	}
?>