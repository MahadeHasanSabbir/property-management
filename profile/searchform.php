<?php
if(isset($_SESSION['id'])) {
?>
	<div class="page-header">
		<h4 class="text-center"> Search your property </h4>
	</div>
    <div class="jumbotron">
		<form class="form-horizontal" name="search" method="post" onsubmit="return show()">
			<div class="form-group">
				<label class="control-label col-sm-1"> Dag: </label>
				<div class="col-sm-5">
					<input type="number" min="0" name="dag" class="form-control" id="dag" autofocus placeholder="Enter your dag no"/>
				</div>
				<div class="col-sm-5">
					<select name="dagvalue" id="dagvalue">
						<option value="0">Puraton</option>
						<option value="1">Notun</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-1"> Khotiyan: </label>
				<div class="col-sm-5">
				    <input type="number" min="0" name="khotiyan" class="form-control" id="khotiyan" placeholder="Enter your khotiyan no"/>
				</div>
				<div class="col-sm-5">
					<select name="khotiyanvalue" id="khotiyanvalue">
						<option value="0">Puraton</option>
						<option value="1">Notun</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-1">Dolil no:</label>
				<div class="col-sm-5">
					<input type="number" min="0" name="dolil" class="form-control" id="dolil" placeholder="Enter your dolil no"/>
				</div>
				<label class="control-label col-sm-1">Mouja:</label>
				<div class="col-sm-5">
					<input type="text" name="mouja" class="form-control" id="mouja" placeholder="enter your mouja name"/>
				</div>
			</div> <br/>
			<button type="submit" value="submit" class="btn btn-md btn-default"> Search info </button>
			<button type="reset" value="reset" class="btn btn-md btn-default"> Reset </button>
	    </form>
	</div>
<?php
}
else {
    header("location:http://localhost/Property-Management/auth");
    exit;
}
?>