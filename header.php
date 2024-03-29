		<nav class="navbar navbar-default navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				<a class="navbar-brand" href="./">Property-Management</a>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li <?php if(basename($_SERVER['PHP_SELF']) == 'index.php'){echo 'class="active"';} ?>>
							<a href="./"> Home </a>
						</li>
						<li <?php if(basename($_SERVER['PHP_SELF']) == 'about.php'){echo 'class="active"';} ?>>
							<a href="./about.php"> About </a>
						</li>
						<li <?php if(basename($_SERVER['PHP_SELF']) == 'distribution.php'){echo 'class="active"';} ?>>
							<a href="./distribution.php"> Distribution </a>
						</li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li>
							<a href="./auth/register.php">
								<span class="glyphicon glyphicon-user"></span> Sign up
							</a>
						</li>
						<li>
							<a href="./auth/log.php">
								<span class="glyphicon glyphicon-user"></span> Sign in
							</a>
						</li>
					</ul>
				</div>
			</div>
		</nav>