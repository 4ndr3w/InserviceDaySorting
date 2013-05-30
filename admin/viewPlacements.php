<?php
require_once "../db.php";
?>
<!DOCTYPE html>
<html>
	<head>
		<title>View Placements</title>
		<link rel="stylesheet" href="admin.css" />
	</head>
	<body>
		<a href="index.html">Back</a><br><br>
		<div id="container">
			<br><br>
			<table id="info" border="1">
			
				<tr>
					<td><strong>ID</strong></td>
					<td><strong>First</strong></td>
					<td><strong>Last</strong></td>
					<td><strong>Block #1</strong></td>
					<td><strong>Block #2</strong></td>
					<td><strong>Block #3</strong></td>
					<td><strong>Block #4</strong></td>
				</tr>
				
				<?php
				$classes = $database->getCareers(true, true);
				$students = $database->getStudents();
				
				$pivot = array();
				foreach ( $students as $k=>$v )
				{
					$pivot[$k] = $v['last'];
				}
				array_multisort($pivot, SORT_ASC, $students);
				
				foreach ( $students as $student )
				{
					$choices = $database->getStudentPlacement($student['id']);
				?>
				<tr>
					<td><?php echo $student['id']; ?></td>
					<td><?php echo $student['first']; ?></td>
					<td><?php echo $student['last']; ?></td>
					<?php
					for ( $i = 0; $i < 4; $i++ )
					{
						echo "<td>".$classes[$choices["p".($i+1)]]['name']."</td>\n";
					}
					?>
				</tr>
				<? 
				}
				?>
			</table>
		</div>
	</body>
</html>

