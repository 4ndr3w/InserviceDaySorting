<?php
require_once("db.php");
$id = $_POST['id'];
$_POST['first'][0] = strtoupper($_POST['first'][0]);
$_POST['last'][0] = strtoupper($_POST['last'][0]);

if ( $database->getStudent($_POST['id']) )
	die("dup");

if ( $database->addStudent($id, $_POST['first'], $_POST['last'], $_POST['location']) )
{
	if ( !$database->setStudentChoices($id, $_POST['c1'], $_POST['c2'], $_POST['c3'], $_POST['c4']) )
	{
		die("fail");
	}
}
else
{
	die("fail");

}
?>
