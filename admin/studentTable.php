<?php
require_once("../db.php");
$students = $database->getStudents();
$pivot = array();
foreach ( $students as $k=>$v )
{
	$pivot[$k] = $v['homeroom'];
}

array_multisort($pivot, SORT_DESC, $students);

?>
<br>
<h3>Current List of Participants</h3>
Printable lists: <a href="printables.php?by=student">by Participants</a> - <a href="printables.php?by=career">by Class</a> | <a href="viewSelections.php">View Selections</a><br>
<table id="table-Students">

<?php
foreach ( $students as $student )
{
	$placements = $database->getStudentPlacement($student['id']);
	echo "<th colspan='4'>ID: ".$student['id']." ".$student['first']." ".$student['last']."</th><tr>";
	for ( $i = 1; $i < 5; $i++ )
	{
		$career = $database->getCareer($placements["p".$i]);
		echo "<td colspan='1'>".$career['name']." - ".$career['location']."</td>";
	}
	echo "</tr></tr>";
?>
<?php
}
?>
</table>
