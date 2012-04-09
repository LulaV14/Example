<?php

session_start();

// FirePHP logging
require_once('../inc/FirePHPCore/FirePHP.class.php');
require_once('../inc/FirePHPCore/fb.php');
FB::log("FirePHP successfully loaded.");


include_once "../inc/constants.inc.php";
include_once "../inc/class.users.inc.php";
$userObj = new ColoredListsUsers();

if ( $_POST['token'] == $_SESSION['token']
	&& !empty($_POST['action'])
	&& isset($_SESSION['LoggedIn'])
	&& $_SESSION['LoggedIn']==1)
{	
	switch($_POST['action'])
	{
		case 'changeemail':
			$status = $userObj->updateEmail() ? "changed" : "failed";
			header("Location: /account.php?email=$status");
			break;
		case 'changepassword':
			$status = $userObj->updatePassword() ? "changed" : "nomatch";
			header("Location: /account.php?password=$status");
			break;
		case 'deleteaccount':
			$userObj->deleteAccount();
			break;
		default:
			header("Location: /");
			break;
	}
}
elseif($_POST['action']=="resetpassword")
{
	if($resp=$userObj->resetPassword()===TRUE)
	{
		header("Location: /resetpending.php");
	}
	else
	{
		echo $resp;
	}
	exit;
}
else
{
	header("Location: /");
	exit;
}

?>