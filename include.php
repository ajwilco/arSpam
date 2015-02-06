<?php

#error_reporting(E_ALL);
error_reporting(E_ERROR | E_PARSE);
#ini_set('display_errors', TRUE);
#ini_set('display_startup_errors', TRUE);

$shutDown="FALSE";
$downRes="";

#  Start buffering
ob_start();

#  Connect to mysql
 $hostname="localhost";
$username="warfactions";
$password="74523698753";
$dbname="warfactions";

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
END;
}                                 //This break exists so each page may have custom <head> data...
function doLayoutHeader(){
Print <<< END
	</head>
	<body>
		<h1 style="text-align:center;">AppRiver Spam MetaData Tool</h1>
		<div style="float:left;height:95%;width:16%;">&nbsp;</div>
		<div style="border:solid #CCC 1px;float:left;height:95%;padding:5px;width:66%;min-width:290px;">
END;
	Return;
}



function dofooter(){
    global $pagename,$M,$loggedin;
	
Print <<< END
		</div>
		<div style="position:absolute;right:15px;top:15px;">
			Created by Aaron Wilkins.
		</div>
	</body>
</html>
END;
include_once("analyticstracking.php");
Print "</body></html>";
	return;
}

?>

