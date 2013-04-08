<?php
require_once "../db.php";

if ( array_key_exists("student", $_POST) && array_key_exists("block", $_POST) && array_key_exists("newValue", $_POST) )
{
	$database->updateSpecificPlacementBlock($_POST['student'], $_POST['block'], $_POST['newValue']);
	die();
}

$students = $database->getStudents();
$pivot = array();
foreach ( $students as $k=>$v )
{
	$pivot[$k] = $v['homeroom'];
}

array_multisort($pivot, SORT_DESC, $students);
$careers = $database->getCareers(true, true);
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Manual Sort</title>
		<link rel="stylesheet" href="admin.css" />
		<style type="text/css">
		.normalCell
		{
			font-size: 12px;
			padding: 5px;
		}
		</style>
		
		<script src="../jquery.js"></script>
		<script type="text/javascript">
		function change(_student, _block, _newValue)
		{
			send = {student:_student, block:_block, newValue:_newValue};
			$.ajax({
				type: "POST",
				url: "studentManualTable.php",
				data: send
			});
		}
		</script>
	</head>
	<body>
		<br><br><br>
		<div id="container">
			<table>
				
				<tr>
					<th>ID</th>
					<th>Choice #1</th>
					<th>Choice #2</th>
					<th>Choice #3</th>
					<th>Choice #4</th>
					<th>Choice #5</th>
					<th>Placement #1</th>
					<th>Placement #2</th>
					<th>Placement #3</th>
					<th>Placement #4</th>
				</tr>
				
				<?php
				
				foreach ( $students as $student )
				{
					$choices = $database->getStudentChoices($student['id']);
					$placements = $database->getStudentPlacement($student['id']);
				?>
					<tr>
						<th class="normalCell"><?php echo $student['id']; ?></th>
						<?php
						for ( $i = 1; $i < 6; $i++ )
						{
							echo "<th class=\"normalCell\">".$careers[$choices['s'.$i]]['name']."</th>";
						}

						for ( $i = 1; $i < 5; $i++ )
						{
							?>
							<th class="normalCell">
								<select onChange="change(<?php echo $student['id']; ?>, <?php echo $i; ?>, this.value)">
									<?php 
									foreach ( $careers as $career )
									{
										if ( $career['id'] == $placements['p'.$i] )
										{
											echo "<option selected='selected' value='".$career['id']."'>".$career['name']."</option>";
										}
										else
										{
											echo "<option value='".$career['id']."'>".$career['name']."</option>";
										}
									}
									?>
								</select>
							</th>
						<?php
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

