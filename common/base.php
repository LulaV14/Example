<?php
	// Set the error reporting level
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	// Start a PHP session
	session_start();

	// Include site constants
	include_once "inc/constants.inc.php";

	// FirePHP logging
	require_once('inc/FirePHPCore/FirePHP.class.php');
	require_once('inc/FirePHPCore/fb.php');
	FB::log("FirePHP successfully loaded.");
	FB::setEnabled(FALSE); // Only set to true if debugging

	if ( !isset($_SESSION['token']) || time()-$_SESSION['token_time']>=300 )
	{
		$_SESSION['token'] = md5(uniqid(rand(), TRUE));
		$_SESSION['token_time'] = time();
	}
	
	// Create a database object
	try {
		$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
		$db = new PDO($dsn, DB_USER, DB_PASS);
	} catch (PDOException $e) {
		echo 'Connection failed: ' . $e->getMessage();
		exit;
	}
?>
