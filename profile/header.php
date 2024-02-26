		<nav class="navbar navbar-default navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="./"> Property-Management / User </a>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li <?php if(basename($_SERVER['PHP_SELF']) == 'index.php'){echo 'class="active"';} ?>>
							<a href="./"> Home </a>
						</li>
						<li <?php if((basename($_SERVER['PHP_SELF']) == 'about.php') || (basename($_SERVER['PHP_SELF']) == 'profileupdate.php') || (basename($_SERVER['PHP_SELF']) == 'view.php')){echo 'class="active"';} ?>>
							<a href="./about.php"> Profile </a>
						</li>
						<li onclick="giveinfo()" <?php if(basename($_SERVER['PHP_SELF']) == 'store.php'){echo 'class="active"';} ?>>
							<a href="./store.php"> Store property </a>
						</li>
						<li onclick="return givealert()" <?php if(basename($_SERVER['PHP_SELF']) == 'search.php'){echo 'class="active"';} ?>>
							<a href="./search.php"> Search property </a>
						</li>
						<li <?php if(basename($_SERVER['PHP_SELF']) == 'distribution.php'){echo 'class="active"';} ?>>
							<a href="./distribution.php"> Distribution </a>
						</li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li onclick="return permit()">
							<a href="http://localhost/Property-Management/auth/logout.php">
								<span class="glyphicon glyphicon-user"></span> Log out
							</a>
						</li>
					</ul>
				</div>
			</div>
		</nav>