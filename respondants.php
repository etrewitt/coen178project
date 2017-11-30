<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Student respondants</title>
	<link rel="stylesheet" type="text/css" href="project.css">
</head>
<body>
	<header>
		<h1>Course Availability Planning Survey</h1>
	</header>
	<nav>
		<ul>
			<li><a id="home" class="home" href="http://students.engr.scu.edu/~etrewitt/coen178project/">Home</a></li
			><li><a id="request" href="enter_course.html">Request Course</a></li
			><li><a id="viewRequests" href="requests.php">Requests</a></li
			><li><a id="priorities" href="priorities.php">Course Priorities</a></li
			><li><a id="respondants" class="current" href="respondants.php">Respondants</a></li
			><li><a id="student_info" href="student_info.php">Student Info</a></li>
		</ul>
	</nav>
	<div class="below-nav"></div>
	<main>

<?php

respondants();

function respondants() {
	$conn=oci_connect( 'etrewitt', '/* password here */', '//dbserver.engr.scu.edu/db11g' );
	if(!$conn) {
		print "<br> connection failed:";
		exit;
	}

	$resp = oci_parse(
		$conn,
		"SELECT   studentID,
							(lastName || ', ' || firstName) as Name,
							count(*) as Requests
		 FROM     CourseRequests natural join StudentList
		 GROUP BY studentID, (lastName || ', ' || firstName)
		 ORDER BY cast (studentID as int)"
	);
	$nonresp = oci_parse(
		$conn,
		"SELECT studentID,
						(lastName || ', ' || firstName) as Name
		 FROM   StudentList
		 WHERE  studentID NOT IN (
		   SELECT distinct studentID
		   FROM   CourseRequests
		 )
		 ORDER BY cast (studentID as int)"
	);

	echo '<h1 id="resp">Respondant students</h1>';
	oci_execute($resp);
	echo "\n<table>\n";
	echo "\t<tr><th>ID</th><th>Name</th><th>Requests</th></tr>\n\t";
	while (($row = oci_fetch_array($resp, OCI_BOTH)) != false) {
		echo "<tr>\n";
		// We can use either numeric indexed starting at 0
		// or the column name as an associative array index to access the colum value
		echo "\t\t<td>$row[0]</td>\n";
		echo "\t\t<td>$row[1]</td>\n";
		echo "\t\t<td>$row[2]</td>\n";
		echo "\t</tr>";
	}
	echo "\n</table>\n";

	echo '<h1 id="nonresp">Non-respondant students</h1>';
	oci_execute($nonresp);
	echo "\n<table>\n";
	echo "\t<tr><th>ID</th><th>Name</th></tr>\n\t";
	while (($row = oci_fetch_array($nonresp, OCI_BOTH)) != false) {
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
	</main>
</body>
</html>
