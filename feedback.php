<?php
	include_once "common/base.php";
    $pageTitle = "Feedback";
	include_once "common/header.php";
?>
			
		<div style="width: 650px;">
			<script type="text/javascript">
				var host = "https:"==document.location.protocol ? "https://secure." : "http://";
				document.write(unescape("%3Cscript src='" 
						+ host 
						+ "wufoo.com/scripts/embed/form.js' "
						+ "type='text/javascript'%3E%3C/script%3E"));
			</script>

			<script type="text/javascript">
				var q7p6q5 = new WufooForm();
				q7p6q5.initialize({
					'userName':'chriscoyier', 
					'formHash':'q7p6q5', 
					'autoResize':true,
					'height':'510', 
					'ssl':true});
				q7p6q5.display();
			</script>
		</div>
<?php
	include_once "common/ads.php";
	include_once "common/close.php";
?>