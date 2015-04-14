<?php

#error_reporting(E_ALL);
error_reporting(E_ERROR | E_PARSE);
#ini_set('display_errors', TRUE);
#ini_set('display_startup_errors', TRUE);
date_default_timezone_set('UTC'); 
ini_set('max_execution_time', 120);


$shutDown="FALSE";
$downRes="";

#  Start buffering
ob_start();

#  Connect to mysql
 $hostname="localhost";
$username="arSpam";
$password="Lio14lsx!";
$dbname="arspam";

mysql_connect($hostname,$username, $password);
mysql_select_db($dbname);

#  Included pages


#  Add slashes to GPC values
#if (get_magic_quotes_gpc() == 0) {
    reset($_POST);
    foreach ($_POST as $key => $value) {
        $_POST['$key'] = addslashes($value);
    }
    reset($_REQUEST);
    foreach ($_REQUEST as $key => $value) {
        $_REQUEST['$key'] = addslashes($value);
    }
    reset($_GET);
    foreach ($_GET as $key => $value) {
        $_GET['$key'] = addslashes($value);
    }
    reset($_COOKIE);
    foreach ($_COOKIE as $key => $value) {
        $_COOKIE['$key'] = addslashes($value);
    }
    reset($_ENV);
    foreach ($_ENV as $key => $value) {
        $_ENV['$key'] = addslashes($value);
    }
    reset($_POST);
    foreach ($_POST as $key => $value) {
        $_POST['$key'] = addslashes($value);
    } 
    reset($_SERVER);
    foreach ($_SERVER as $key => $value) {
        $_SERVER['$key'] = addslashes($value);
    }
    reset($_SESSION);
    foreach ($_SESSION as $key => $value) {
        $_SESSION['$key'] = addslashes($value);
    }
#}

#  Set settings
$M = "/arspam/";

#  Functions

function doPageOpen(){
    global $pagename,$M,$refresh;

	if(!isset($pagename)){$pagename="Welcome!";}
	$ip="{$_SERVER['REMOTE_ADDR']}";
	
	if($pagename==null)$pageTitle="AppRiver Spam Metadata"; else $pageTitle=$pagename." // arSpam!";
print <<< END
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>{$pageTitle}!</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="AppRiver Email MetaData Viewer">
		<meta name="author" content="Aaron Wilkins">

		<!-- Bootstrap Core CSS -->
		<link href="css/bootstrap.min.css" rel="stylesheet">

		<!-- Custom CSS -->
		<link href="css/grayscale.css" rel="stylesheet">

		<!-- Custom Fonts -->
		<link href="font-awesome-4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
		<link href='http://fonts.googleapis.com/css?family=Average+Sans' rel='stylesheet' type='text/css'>
		
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
END;
}                                 //This break exists so each page may have custom <head> data...
function doLayoutHeader(){
Print <<< END
	</head>
	<body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">
    <!-- Navigation -->
    <nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                    <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand page-scroll" href="#page-top">
                    <i class="fa fa-chevron-up"></i>  <span class="light">arSpam</span>
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">
                    <!-- Hidden li included to remove active class from about link when scrolled up past about section -->
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#filter">Filter</a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#classes">Classes</a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#tests">Tests</a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#location">Location</a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#servers">Servers</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>
	
	<!-- Intro Header -->
    <header class="intro">
        <div class="intro-body">
            <div class="container">
                <div class="row">
					<div class="col-md-10 col-md-offset-1">
						<p class="brand-heading">
							<img src="img/logo.png" style="width:350px;" /> 
							Spam email metadata visualizer, by Aaron Wilkins
						</p>
                    </div>
				</div>
				<div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <p>&nbsp;</p>
                        <a href="#classes" class="btn btn-circle page-scroll">
                            <i class="fa fa-angle-double-down animated"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
END;
	Return;
}



function dofooter(){
    global $pagename,$M,$loggedin;
	
Print <<< END
		<!-- Footer -->
		<footer>
			<div class="container text-center">
				<p>&copy; Aaron Wilkins, and AppRiver respectively</p>
			</div>
		</footer>
		
		<!-- jQuery Version 1.11.0 -->
		<script src="js/jquery-1.11.0.js"></script>

		<!-- Bootstrap Core JavaScript -->
		<script src="js/bootstrap.min.js"></script>

		<!-- Plugin JavaScript -->
		<script src="js/jquery.easing.min.js"></script>

		<!-- Custom Theme JavaScript -->
		<script src="js/grayscale.js"></script>
	</body>
</html>
END;
include_once("analyticstracking.php");
Print "</body></html>";
	return;
}

?>

