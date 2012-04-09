<?php
	include_once "common/base.php";
    $pageTitle = "Bye Bye";
	include_once "common/header.php";
?>

		<h2>Are You Sure?</h2>
		<p>If you delete your account, <em>all</em> of your 
		information will be permanently deleted.</p>

		<form action="db-interaction/users.php" method="post">
			<div>
				<input type="hidden" name="action"
					value="deleteaccount" />
				<input type="hidden" name="user-id"
					value="<?php echo $_POST['user-id'] ?>" />
				<input type="submit" class="button"
					value="I'm Sure&mdash;Delete My Account" />
				<input type="hidden" name="token"
					value="<?php echo $_SESSION['token']; ?>" />
			</div>
		</form>
<?php
	include_once "common/ads.php";
	include_once "common/close.php";
?>