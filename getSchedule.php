<?php
require_once "db.php";
$schedule = array();
$events = $database->getCareers(true, true);
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Get Schedule</title>
		<link rel="stylesheet" href="bootstrap.min.css" />
		<style type="text/css">
		.centered
		{
			margin: auto;
			text-align: center;
		}
		
		#container
		{
			margin-top: 50px;
		}
		
		.dataTable
		{
			width: 400px;
			margin:auto;
		}
		</style>
	</head>
	<body>
		<div id="container" class="centered">
				
				<?php
				if ( array_key_exists("id", $_POST) )
				{
					$personInfo = $database->getStudent($_POST['id']);
					echo "<table class=\"table table-bordered dataTable\"><tr><td><strong>Name:</strong></td><td>".$personInfo['first']." ".$personInfo['last']."</td></tr><br>\n";
				
					$schedule = $database->getStudentPlacement($_POST['id']);
				
					if ( empty($schedule) )
						echo "Schedules have not been generated yet.";
					else
					{
						for ( $i = 0; $i < 3; $i++ )
						{
							$event = $events[$schedule["p".($i+1)]];
							echo "<tr><td><strong>".($i+1)."</strong></td><td>".$event['name']."</td></tr><br>\n";
						}
					}
					echo "</table>";
				}
				if ( !array_key_exists("id", $_POST) )
				{
				?>
					<form action="" method="post">
						ID: <input type="text" name="id"><br>
						<input type="submit" class="btn">
					</form>
				<?php
				}
				?>
				</table>
		</div>
	</body>
</html>

