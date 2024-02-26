        <nav class="navbar navbar-default navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
					<a class="navbar-brand" href="./adminprofile.php"> Property-Management / Admin </a>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li <?php if(basename($_SERVER['PHP_SELF']) == 'adminprofile.php'){echo 'class="active"';} ?>>
                            <a href="./adminprofile.php"> Dashboard </a>
                        </li>
						<li <?php if((basename($_SERVER['PHP_SELF']) == 'users.php')||(basename($_SERVER['PHP_SELF']) == 'userview.php')||(basename($_SERVER['PHP_SELF']) == 'userupdate.php')||(basename($_SERVER['PHP_SELF']) == 'view.php')||(basename($_SERVER['PHP_SELF']) == 'profileupdate.php')){echo 'class="active"';} ?> onclick="return apermit()">
                            <a href="./users.php"> View users </a>
                        </li>
						<li <?php if(basename($_SERVER['PHP_SELF']) == 'massage.php'){echo 'class="active"';} ?>>
                            <a href="./massage.php"> View massages </a>
                        </li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li onclick="return permit()">
                            <a href="http://localhost/Property-Management/admin/logout.php">Log out</a>
                        </li>
					</ul>
				</div>
			</div>
		</nav>