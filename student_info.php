<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Query student info</title>
	<link rel="stylesheet" type="text/css" href="project.css">
</head>
<body>
	<nav>
		<ul>
			<li><a id="home" class="home" href="http://students.engr.scu.edu/~etrewitt/coen178project/">Home</a></li
			><li><a id="request" href="enter_course.html">Request Course</a></li
			><li><a id="viewRequests" href="requests.php">Requests</a></li
			><li><a id="priorities" href="priorities.php">Course Priorities</a></li
			><li><a id="respondants" href="respondants.php">Respondants</a></li
			><li><a id="student_info" class="current" href="student_info.php">Student Info</a></li>
		</ul>
	</nav>

	<form method="post" action="student_info.php">
    <label for="id">Student ID</label>
    <input type="text" name="id" id="id" value="">
    <input type="submit" value="Submit">
  </form>
  <br>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// collect input data
	$id = $_POST['id'];
	info($id);
	coursesNeeded($id);
}

function info($id) {
	$conn=oci_connect( 'etrewitt', '/* password */', '//dbserver.engr.scu.edu/db11g' );
	if(!$conn) {
		print "<br> connection failed:";
		exit;
	}
	$student = oci_parse(
		$conn,
		"SELECT studentID, lastName || ', ' || firstName as name, major, startYear
		 FROM   StudentList
		 WHERE  studentID = '$id'"
	);

	oci_execute($student);
	// OCI_BOTH flag fetches associative array as well
	while (($row = oci_fetch_array($student, OCI_BOTH)) != false) {
		echo "<b>Name:</b> "          . $row['NAME']      . "<br>\n";
		echo "<b>Student ID:</b> "    . $row['STUDENTID'] . "<br>\n";
		echo "<b>Major:</b> "         . $row['MAJOR']     . "<br>\n";
		echo "<b>Year of entry:</b> " . $row['STARTYEAR'] . "<br>\n";

		// TODO: list num of completed units?
		// $compunits = oci_parse(
		// 	$conn,
		// 	"SELECT   sum(units) as units
		// 	 FROM     Transcript natural join CourseList
		// 	 WHERE    studentID = '$id'
		// 	 GROUP BY studentID"
		// );
		// oci_execute($compunits);
		// while (($row = oci_fetch_array($compunits, OCI_BOTH)) != false) {
		// 	echo "<b>Completed units:</b> " . $row['UNITS'] . "<br>";
		// }

		$requnits = oci_parse(
			$conn,
			"SELECT   sum(units) as units
			 FROM     CourseRequests natural join CourseList
			 WHERE    studentID = '$id'
			 GROUP BY studentID"
		);
		oci_execute($requnits);
		while (($row = oci_fetch_array($requnits, OCI_BOTH)) != false) {
			echo "<b>Requested units:</b> " . $row['UNITS'] . "<br>\n";
		}
	}

	OCILogoff($conn);
}

function coursesNeeded($id) {
	$conn=oci_connect( 'etrewitt', '/* password */', '//dbserver.engr.scu.edu/db11g' );
	if(!$conn) {
		print "<br> connection failed:";
		exit;
	}
	$needed = oci_parse(
		$conn,
		"SELECT   distinct mr.dept, mr.courseNo
		 FROM     majorReqs mr
		 WHERE    (mr.dept, mr.courseNo) not in (
								Select dept, courseNo
								from   passedCourses
								where  studentID = '4'
							) and
			 				(mr.dept, mr.courseNo) not in (
								Select dept, courseNo
								from   courseRequests
								where  studentID = '4'
							)
		 ORDER BY mr.dept, mr.courseNo"
	);

	oci_execute($needed);

	echo "<h1>Required courses to graduate</h1>";
	echo "\n<table>\n";
	echo "\t<tr><th>Course No.</th><th>Title</th></tr>\n\t";
	while (($row = oci_fetch_array($needed, OCI_BOTH)) != false) {
		echo "<tr>\n";
		echo "\t\t<td>$row[0]</td>\n";
		echo "\t\t<td>$row[1]</td>\n";
		echo "\t</tr>";
	}
	echo "\n</table>\n";

	OCILogoff($conn);
}

?>
<!-- end PHP script -->
</body>
</html>
