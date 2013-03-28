<?php
require_once("db.php");
$_POST['first'][0] = strtoupper($_POST['first'][0]);
$_POST['last'][0] = strtoupper($_POST['last'][0]);

if ( $id = $database->addStudent($_POST['first'], $_POST['last'], $_POST['location']) )
{
	if ( !$database->setStudentChoices($id, $_POST['c1'], $_POST['c2'], $_POST['c3'], $_POST['c4'], $_POST['c5']) )
	{
		die("fail");
	}
}
else
{
	echo mysql_error();
	die("fail");

}
?>
