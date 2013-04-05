<?php
require_once "../db.php";
if ( array_key_exists("key", $_POST) && array_key_exists("value", $_POST) )
{
	$database->setConfig($_POST['key'], $_POST['value']);
	die();
}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Config Editor</title>
		<link rel="stylesheet" href="admin.css" />
		<script src="../jquery.js"></script>
		<script type="text/javascript">
		function editValue(_key)
		{
			_value = prompt("New value for '"+_key+"':");
			if ( _value != null )
			{
				var req = $.ajax({
					url: "configEdit.php",
					type: "POST",
					data: {key:_key, value:_value},
					success: function(res)
					{
						window.location.reload();
					}
				});
			}
		}
		
		</script>
	</head>
	<body>
		<div id="container">
			<br><br>
			Site Name: <?php echo $database->getConfig("siteName"); ?> <button onClick="editValue('siteName')">Edit</button><br>
			Banner Message: <?php echo $database->getConfig("bannerMsg"); ?> <button onClick="editValue('bannerMsg')">Edit</button>
		</div>
	</body>
</html>

