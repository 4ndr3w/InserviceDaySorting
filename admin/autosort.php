<?php

if ( !array_key_exists("run", $_POST) )
{
?>
	<form action="" method="post">
		Running an automatic sort will overwrite any existing placement data.<br>
		<button type='button' onclick='runAutosort()'>Run Automated Sort</button>
	</form>
<?php
	die();
}

require_once "../db.php";
$startTime = microtime(true);
$itsRan = 0;
function uniqueIteration($a, $b, $c, $d)
{
	$values = array($a, $b, $c, $d);
	foreach ( $values as $kA => $vA )
	{
		foreach ( $values as $kB => $vB )
		{
			if ( $kA != $kB && $vA == $vB )
				return false;
		}
	}
	return true;
}

class Placement
{
	function __construct($id, $weight, $isStatic = false)
	{
		$this->id = $id;
		$this->weight = $weight;
		$this->isStatic = $isStatic;
	}
	
	function canReplace($otherPlacement, $threshold)
	{
		if ( $this->otherPlacement->isStatic )
			return false;
		if ( $this->weight-$threshold > $otherPlacement->weight )
			return false;
		return true;
	}
	
	function isStatic()
	{
		return $this->isStatic;
	}
}

class Choice
{
	function __construct($id, $weight, $group)
	{
		global $database;
		
		$this->id = $id;
		$this->weight = $weight;
		$this->possible = true;
		if ( $group == 0 )
			$group = $database->getGroupForCareer($id);
		$this->group = $group;
	}
	
	function getGroup()
	{
		return $this->group;
	}

	function getWeight()
	{
		return $this->weight;
	}
	
	function getAsPlacement()
	{
		return new Placement($this->id, $this->weight, false);
	}
	
	function isPossible()
	{
		return $this->possible;
	}
}


class Student
{
	function __construct($id, $c1, $c2, $c3, $c4)
	{
		$this->id = $id;
		$this->choices = array();
		$this->choices[0] = new Choice($c1, 4, 0);
		$this->choices[1] = new Choice($c2, 3, 0);
		$this->choices[2] = new Choice($c3, 2, 0);
		$this->choices[3] = new Choice($c4, 1, 0);		
		$this->placements = array();
		$this->placements[0] = new Placement(0, -1);
		$this->placements[1] = new Placement(0, -1);
		$this->placements[2] = new Placement(0, -1);
		$this->placements[3] = new Placement(0, -1);
	}
	
	function getMostPopularChoiceGroup()
	{
		$choiceGroups = array();
		for ($i = 0; $i < 4; $i++ )
		{
			$choiceGroups[$this->choices[$i]->getGroup()]++;
		}
		$highestCGCount = 0;
		$highestCGID = 0;
		foreach ( $choiceGroups as $k => $count )
		{
			if ( $count > $highestCGCount && $k != 0 )
			{
				$highestCGID = $k;
				$highestCGCount = $count;
			}
		}
		return $highestCGID;
	}
	
	function getChoiceGroupsByPopularity()
	{
		$choiceGroups = array();
		for ($i = 0; $i < 4; $i++ )
		{
			$choiceGroups[$this->choices[$i]->getGroup()]++;
		}
		array_flip($choiceGroups);
		return $choiceGroups;
	}
	
	function assignBlock($blockNum, $placement)
	{
		$this->placements[$blockNum] = $placement;
	}
	
	function blockIsOpen($blockNum)
	{
		if ( !is_object($this->placements[$blockNum]) )
			return true;
		if ( $this->placements[$blockNum]->id > 0 )
			return false;
		return true;
	}
	
	function isFullySorted()
	{
		foreach ( $this->placements as $placement )
		{
			if ( $placement->id == 0 )
				return false;
		}
		return true;
	}
	
	function getHighestChoiceNumber()
	{
		foreach ($this->choices as $k=>$choice)
		{
			$isThisChoice = true;
			if ( $choice->isPossible() )
			{
				foreach ( $this->placements as $p )
				{
					if ( $p->id == $choice->id )
						$isThisChoice = false;
				}
				if ( $isThisChoice )
					return $k;
			}
		}
		return -1;
	}
}

class Career
{
	function __construct($id, $max)
	{
		$this->id = $id;
		$this->max = $max;
		
		$this->blockSizes = array(0,0,0,0);
	}
	
	function addToBlock($blockNum)
	{
		$this->blockSizes[$blockNum]++;
	}
	
	function removeFromBlock($blockNum)
	{
		$this->blockSizes[$blockNum]--;
	}
	
	function blockIsFull($blockNum)
	{
		return $this->blockSizes[$blockNum] >= $this->max;
	}
}


$_students = $database->getStudents();
$students = array();
$_careers = $database->getCareers();
$careers = array();

foreach ( $_careers as $career )
{
	$careers[$career['id']] = new Career($career['id'], $career['maxStudents'], $career['group']);
}

// Convert mysql data to an array of Student objects, also assign the assembly block as static

$fourthOfSignups = floor(count($_students)/4);
$currentFairBlock = 0;
$i = 0;
foreach ( $_students as $k => $student )
{
	$choices = $database->getStudentChoices($student['id']);
	$placement = $database->getStudentPlacement($student['id']);
	$thisStudent = new Student($student['id'], $choices['s1'], $choices['s2'], $choices['s3'], $choices['s4']);

	
	$thisStudent->assignBlock($currentFairBlock, new Placement($fairID, 100, true));
	
	if ( $i >= $fourthOfSignups*($currentFairBlock+1) && $currentFairBlock < 3 )
		$currentFairBlock++; 
	$i++;
	$students[$thisStudent->id] = $thisStudent;
}

function attemptSchedule($scheduledCareers, $newCareerID, $student, $careers, &$itsRan, $isChoice = true)
{
	foreach ( $student->placements as $k => $placement )
	{
		if ( $placement->id != 0 )
			$careers[$placement->id]->removeFromBlock($k);
	}
	$thisStudentSortSuccess = false;
	for ( $_a = 0; $_a < 4; $_a++ )
	{
		$a = $_a;
		if ( $scheduledCareers[0]->isStatic() ) // Don't move static events!
					$a = 0;
		for ( $_b = 0; $_b < 4; $_b++ )
		{
			$b = $_b;
			if ( $scheduledCareers[1]->isStatic() ) // Don't move static events!
						$b = 1;
			for ( $_c = 0; $_c < 4; $_c++ )
			{
				$c = $_c;
				if ( $scheduledCareers[2]->isStatic() ) // Don't move static events!
							$c = 2;
				for ( $_d = 0; $_d < 4; $_d++ )
				{
					$d = $_d;
					if ( $scheduledCareers[3]->isStatic() ) // Don't move static events!
								$d = 3;		
					if ( uniqueIteration($a, $b, $c, $d) )
					{
						$itsRan++;
						$invalid = false;
						//echo "In This Iteration: ".$a." - ".$b." - ".$c."\n";
						$thisScheduleIteration = array($a => $scheduledCareers[0], $b => $scheduledCareers[1], $c => $scheduledCareers[2], $d => $scheduledCareers[3]);					
						for ($k = 0; $k < 4; $k++ )
						{
							$careerObj = $thisScheduleIteration[$k];
							$blockNum = $k;
							$careerID = 0;
							if ( is_object($careerObj) )
								$careerID = $careerObj->id;
							if ( $careerID != 0 )
							{
								if ( $careers[$careerID]->blockIsFull($blockNum) )
								{
									$invalid = true;
								}
							}
											
						}
						if ( !$invalid )
						{
							$thisStudentSortSuccess = true;
							$student->placements = $thisScheduleIteration;									
							break;
						}
					}
				}
				if ( $thisStudentSortSuccess ) break;					
			}
			if ( $thisStudentSortSuccess ) break;
		}
		if ( $thisStudentSortSuccess ) break;
	}
	
	foreach ( $student->placements as $k => $placement )
	{
		if ( $placement->id != 0 )
			$careers[$placement->id]->addToBlock($k);
	}
	
	return $thisStudentSortSuccess;
}

for ( $i = 0; $i <= 5; $i++ )
{
	foreach ( $students as $studentKey => $student )
	{
		$skip = $student->isFullySorted();
		$blocksFilled = 0;
		for ( $z = 0; $z < 4; $z++ )
		{
			if ( !$student->blockIsOpen($z) )
				$blocksFilled++;
		}
		$skip = ($blocksFilled==4);
		$highestChoiceNumber = $i;
		if ( $highestChoiceNumber == -1 )
			$skip = true;
			
		if ( !$skip )
		{
			$highestChoiceNumber = $student->getHighestChoiceNumber();
			$highestChoiceID = $student->choices[$highestChoiceNumber];
			
			if ( $highestChoiceID != -1)
			{
				$thisChoice = new Placement($highestChoiceID->id, $highestChoiceNumber);
				$scheduledCareers = array();
						
				for ( $z = 0; $z < 4; $z++ ) // Fill empty slots
				{
					if ( !$student->blockIsOpen($z) )
						$scheduledCareers[$z] = $student->placements[$z];
					else
						$scheduledCareers[$z] = new Placement(0, 0);
				}
						
				for ( $z = 0; $z < 4; $z++ ) // Add next choice to list
				{
					if ( $scheduledCareers[$z]->id == 0 )
					{
						$scheduledCareers[$z] = $thisChoice;
						break;
					}
				}
				
				if ( !attemptSchedule($scheduledCareers, $highestChoiceID->id, $student, $careers, $itsRan) )
				{
					$student->choices[$highestChoiceNumber]->possible = false;
				}
			}
		}
	}
}

/*
foreach ( $students as $student )
{
	if ( !$student->isFullySorted() )
	{
		$choiceGroup = $student->getMostPopularChoiceGroup();
		$_careersInGroup = $database->getCareersInGroup($choiceGroup);
		$careersInGroup = array();
		foreach ( $_careersInGroup as $career )
		{
			$careersInGroup[$career['id']] = $careers[$career['id']];
		}
		foreach ( $careersInGroup as $career )
		{
			if ( $student->isFullySorted() )
				break;
			$scheduledCareers = $student->placements;
			for ( $z = 0; $z < 4; $z++ ) // Add next choice to list
			{
				if ( $scheduledCareers[$z]->id == 0 )
				{
					$scheduledCareers[$z] = new Placement($career->id, 1, false);
					break;
				}
			}
			attemptSchedule($scheduledCareers, $career->id, $student, $careers, $itsRan, false);
		}
	}
}
*/

echo "--------------------<br>\n";
$stats = array("success"=>0, "failed"=>0, "total"=>0);
mysql_query("DELETE FROM `placements`");
foreach ( $students as $student )
{
	$stats['total']++;
	( $student->isFullySorted() ? $stats['success']++ : $stats['failed']++ );
	mysql_query("INSERT INTO `placements` (id, p1, p2, p3, p4) VALUES(".$student->id.", ".$student->placements[0]->id.", ".$student->placements[1]->id.", ".$student->placements[2]->id.", ".$student->placements[3]->id.")");
	foreach ( $student->placements as $placement )
	{
		if ( $placement->id == $student->choices[0]->id )
		{
			$stats['gotFirstChoice']++;
			break;
		}
	}
}

echo "Statistics:<br>\n";

$database->resetStatistics();

$database->addStatistic("Total", $stats['total']);
$database->addStatistic("Fully Sorted", (($stats['success']/$stats['total'])*100)."%");
$database->addStatistic("Partially Sorted", (($stats['failed']/$stats['total'])*100)."%");
$database->addStatistic("Got First Choice", (($stats['gotFirstChoice']/$stats['total'])*100)."%");
$database->addStatistic("Iterations", $itsRan);
$database->addStatistic("Time to complete", round((microtime(true)-$startTime), 5)." sec");

$stats = $database->getStatistics();
foreach ( $stats as $stat )
{
	echo $stat['name']." - ".$stat['value']."<br>\n";
}
echo "--------------------\n";
?>
<br>
<button type='button' onclick='runAutosort()'>Run Again</button>