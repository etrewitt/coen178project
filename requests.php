<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Query course requests</title>
	<link rel="stylesheet" type="text/css" href="project.css">
</head>
<body>
	<nav>
		<ul>
			<li><a id="home" class="home" href="http://students.engr.scu.edu/~etrewitt/coen178project/">Home</a></li
			><li><a id="request" href="enter_course.html">Request Course</a></li
			><li><a id="viewRequests" class="current" href="requests.php">Requests</a></li
			><li><a id="priorities" href="priorities.php">Course Priorities</a></li
			><li><a id="respondants" href="respondants.php">Respondants</a></li
			><li><a id="student_info" href="student_info.php">Student Info</a></li>
		</ul>
	</nav>

  <form method="post" action="requests.php">
    <label for="id">Student ID</label>
    <input type="text" name="id" id="id" value="">
    <input type="submit" value="Submit">
  </form>

<?php

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  requests($id);
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
	// collect input data
	$id = $_POST['id'];
	requests($id);
}

function requests($id) {
	$conn=oci_connect( 'etrewitt', '/* password */', '//dbserver.engr.scu.edu/db11g' );
	if(!$conn) {
		print "<br> connection failed:";
		exit;
	}
	$query = oci_parse(
		$conn,
		"SELECT   dept,
              courseNo,
							quarter,
              year
		 FROM     CourseRequests
		 WHERE    studentID = '$id'
		 ORDER BY year, quarter desc"
	);

	// Execute the query
	echo "<h1>Requested courses</h1>\n";
	oci_execute($query);
	echo "\n<table id=\"requests\">\n";
	echo "\t<tr><th>Course</th><th>Term</th><th></th></tr>\n\t";
	while (($row = oci_fetch_array($query, OCI_BOTH)) != false) {
		echo "<tr>\n";
		echo "\t\t<td>" . $row["DEPT"]    . " " . $row["COURSENO"] . "</td>\n";
		echo "\t\t<td>" . $row["QUARTER"] . " " . $row["YEAR"]     . "</td>\n";
    echo "\t\t<td><a class=\"button\" href=\""
       . 'modify_request.php?id=' . $id
       . '&dept='    . $row["DEPT"]
       . '&course='  . $row["COURSENO"]
       . '&quarter=' . $row["QUARTER"]
       . '&year='    . $row["YEAR"]
       . "\">MODIFY</a></td>\n";
		// echo "\t\t<td>" . $row[2] . "</td>\n";
		echo "\t</tr>";
	}
	echo "\n</table>\n";


	$passed = oci_parse(
		$conn,
		"SELECT   dept,
              courseNo,
							quarter,
              year
		 FROM     passedCourses
		 WHERE    studentID = '$id'
		 ORDER BY year, quarter desc"
	);

	// Execute the query
	oci_execute($passed);
	echo "<h1>Previous courses</h1>\n";
	echo "\n<table id=\"passed\">\n";
	echo "\t<tr><th>Course</th><th>Term</th></tr>\n\t";
	while (($row = oci_fetch_array($passed, OCI_BOTH)) != false) {
		echo "<tr>\n";
		echo "\t\t<td>" . $row["DEPT"]    . " " . $row["COURSENO"] . "</td>\n";
		echo "\t\t<td>" . $row["QUARTER"] . " " . $row["YEAR"]     . "</td>\n";
		echo "\t</tr>";
	}
	echo "\n</table>\n";

	OCILogoff($conn);
}

?>
<!-- end PHP script -->
</body>
</html>
