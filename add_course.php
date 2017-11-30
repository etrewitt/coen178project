<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title>Enter course preferences</title>
		<link rel="stylesheet" type="text/css" href="project.css">
	</head>
	<body>
		<header>
			<h1>Course Availability Planning Survey</h1>
		</header>
		<nav>
      <ul>
        <li><a id="home" class="home" href="http://students.engr.scu.edu/~etrewitt/coen178project/">Home</a></li
        ><li><a id="request" class="current" href="enter_course.html">Request Course</a></li
        ><li><a id="viewRequests" href="requests.php">Requests</a></li
        ><li><a id="priorities" href="priorities.php">Course Priorities</a></li
        ><li><a id="respondants" href="respondants.php">Respondants</a></li
        ><li><a id="student_info" href="student_info.php">Student Info</a></li>
      </ul>
    </nav>
		<div class="below-nav"></div>
		<main>
<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// collect input data
	$id = $_POST['id'];
	$dept = $_POST['dept'];
	$courseNo = $_POST['courseNo'];
	$year = $_POST['year'];
	$quarter = $_POST['quarter'];

	// validate selection
	$valid = validateInput($id, $dept, $courseNo, $year, $quarter);

	if ($valid) {
		// add course
		addCourse($id, $dept, $courseNo, $year, $quarter);
	}
}

function validateInput($id, $dept, $courseNo, $year, $quarter) {
	$conn = oci_connect( 'etrewitt', '/* password */', '//dbserver.engr.scu.edu/db11g' );
	if (!$conn) {
		print "<br> connection failed:";
		exit;
	}

	// make sure the course exists
	$query = oci_parse(
		$conn,
		"SELECT *
		 FROM   CourseList
		 WHERE  dept = '$dept' and courseNo = '$courseNo'"
	);
	oci_execute($query);
	$i = 0;
	while (($row = oci_fetch_array($query, OCI_BOTH)) != false) {
		$i++;
	}
	if ($i == 0) {
		print "<br><b>$dept $courseNo</b> doesn't exist; valid courses in $dept are:";
		$query = oci_parse(
			$conn,
			"SELECT (dept || ' ' || courseNo) as course, name, units
			 FROM   CourseList
			 WHERE  dept = '$dept'"
		);
		oci_execute($query);

		// print valid courses in the selected department
		echo "\n<table>\n";
		echo "\t<tr><th>Course No.</th><th>Course title</th><th>Units</th></tr>\n\t";
		while (($row = oci_fetch_array($query, OCI_BOTH)) != false) {
			echo "<tr>\n";
			echo "\t\t<td>" . $row[0] . "</td>\n";
			echo "\t\t<td>" . $row[1] . "</td>\n";
			echo "\t\t<td>" . $row[2] . "</td>\n";
			echo "\t</tr>";
		}
		echo "\n</table>\n";
		OCILogoff($conn);
		return false;
	}

	// make sure the student isn't already taking this course
	$query = oci_parse(
		$conn,
		"SELECT *
		 FROM   CourseRequests
		 WHERE  studentID = '$id' and
		        dept      = '$dept' and
						courseNo  = '$courseNo'"
	);
	oci_execute($query);
	$i = 0;
	while (($row = oci_fetch_array($query, OCI_BOTH)) != false) {
		$i++;
	}
	if ($i != 0) {
		print "<br> $id is already registered for $dept $courseNo";
		OCILogoff($conn);
		return false;
	}

	OCILogoff($conn);
	return true;
}

function addCourse($id, $dept, $courseNo, $year, $quarter) {
	// connect to your database. Type in your username, password and the DB path
	$conn = oci_connect( 'etrewitt', '/* password */', '//dbserver.engr.scu.edu/db11g' );
	if (!$conn) {
		print "<br> connection failed:";
		exit;
	}

	// TODO: replace with SQL procedure?
	$query = oci_parse(
		$conn,
		"INSERT INTO CourseRequests values ('$id', '$dept', '$courseNo', '$quarter', $year)"
	);
	oci_execute($query);

	$nextYear = $year + 1;
	print "requested $dept $courseNo for $id in the $quarter $year-$nextYear quarter";

	OCILogoff($conn);
}

?>
		</main>
	</body>
</html>
