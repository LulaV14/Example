<?php
	include_once "common/base.php";
	$pageTitle = "Home";
	include_once "common/header.php";
?>

		<div id="main">
			<noscript>This site just doesn't work, period, without JavaScript</noscript>
<?php
if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username'])): 	            
	echo "\t\t\t<ul id=\"list\">\n";

	include_once 'inc/class.lists.inc.php';
	$lists = new ColoredListsItems($db);
	list($LID, $URL, $order) = $lists->loadListItemsByUser();

	echo "\t\t\t</ul>";
?>

			<br />

			<form action="db-interaction/lists.php" id="add-new" method="post">
				<input type="text" id="new-list-item-text" name="new-list-item-text" />

				<input type="hidden" id="current-list" name="current-list" value="<?php echo $LID; ?>" />
				<input type="hidden" id="new-list-item-position" name="new-list-item-position" value="<?php echo ++$order; ?>" />

				<input type="submit" id="add-new-submit" value="Add" class="button" />
				<input type="hidden" name="token" id="token"
					value="<?php echo $_SESSION['token']; ?>" />
			</form>

			<div class="clear"></div>

			<div id="share-area">
				<p>Public list URL: <a target="_blank" href="http://coloredlists.com/<?php echo $URL ?>.html">http://coloredlists.com/<?php echo $URL ?>.html</a>
				&nbsp; <small>(Nobody but YOU will be able to edit this list)</small></p>
			</div>

			<script type="text/javascript" src="js/jquery-ui-1.7.2.custom.min.js"></script>
			<script type="text/javascript" src="js/jquery.jeditable.mini.js"></script>
			<script type="text/javascript" src="js/lists.js"></script>
			<script type="text/javascript">
			         initialize();
			</script>

<?php
elseif(isset($_GET['list'])): 	            
	echo "\t\t\t<ul id='list'>\n";

	include_once 'inc/class.lists.inc.php';
	$lists = new ColoredListsItems($db);
	list($LID, $URL) = $lists->loadListItemsByListId();

	echo "\t\t\t</ul>";
else:
?>
		  	      
			<img src="images/newlist.jpg" alt="Your new list here!" />
                  
<?php endif; ?>
              		      		  
		</div>
		  
<?php
	include_once "common/ads.php";
	include_once "common/close.php";
?>