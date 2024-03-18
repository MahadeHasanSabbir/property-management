<?php
if(isset($_SESSION['id'])) {
?>
	<div class="page-header">
		<h4 class="text-center"> Search your property </h4>
	</div>
    <div class="jumbotron">
		<form class="form-horizontal" name="search" method="post">
			<div class="form-group">
				<label class="control-label col-sm-1"> Mark: </label>
				<div class="col-sm-5">
					<input type="number" min="0" name="dag" class="form-control" id="dag" autofocus placeholder="Enter your mark no"/>
				</div>
				<div class="col-sm-2">
					<select class="form-control" name="dagvalue" id="dagvalue">
						<option value="0">Old</option>
						<option value="1">New</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-1"> Ledger: </label>
				<div class="col-sm-5">
				    <input type="number" min="0" name="khotiyan" class="form-control" id="khotiyan" placeholder="Enter your ledger no"/>
				</div>
				<div class="col-sm-2">
					<select class="form-control" name="khotiyanvalue" id="khotiyanvalue">
						<option value="0">Old</option>
						<option value="1">New</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-1">Deed:</label>
				<div class="col-sm-5">
					<input type="number" min="0" name="dolil" class="form-control" id="dolil" placeholder="Enter your deed no"/>
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
    header("location:../auth");
    exit;
}
?>