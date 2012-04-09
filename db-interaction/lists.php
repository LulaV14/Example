<?php

session_start();

include_once "../inc/constants.inc.php";
include_once "../inc/class.lists.inc.php";

if ( isset($_POST['token'])
	&& $_SESSION['token'] == $_POST['token']
	&& !empty($_POST['action'])
	&& isset($_SESSION['LoggedIn'])
	&& $_SESSION['LoggedIn']==1 )
{
	$listObj = new ColoredListsItems();
	switch($_POST['action'])
	{
		case 'add':
			echo $listObj->addListItem();
			break;
		case 'update':
			$listObj->updateListItem();
			break;
		case 'sort':
			$listObj->changeListItemPosition();
			break;
		case 'color':
			echo $listObj->changeListItemColor();
			break;
		case 'done':
			echo $listObj->toggleListItemDone();
			break;
		case 'delete':
			echo $listObj->deleteListItem();
			break;
		default:
			header("Location: /");
			break;
	}
}
else
{
	header("Location: /");
	exit;
}

?>