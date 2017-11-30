<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Class priorities</title>
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
			><li><a id="priorities" class="current" href="priorities.php">Course Priorities</a></li
			><li><a id="respondants" href="respondants.php">Respondants</a></li
			><li><a id="student_info" href="student_info.php">Student Info</a></li>
		</ul>
	</nav>
	<div class="below-nav"></div>
  <h1>Full list of class priorities</h1>
<?php

classPriorities();

function classPriorities() {
	$conn=oci_connect( 'etrewitt', '/* password */', '//dbserver.engr.scu.edu/db11g' );
	if(!$conn) {
		print "<br> connection failed:";
		exit;
	}
	$query = oci_parse(
		$conn,
		"SELECT   (dept || courseNo) as Course,
							(quarter || ' ' || year) as Term,
							count(*) as Requests
		 FROM     CourseRequests
		 GROUP BY dept, courseNo, quarter, year
		 ORDER BY Requests desc"
	);

	// Execute the query
	oci_execute($query);
	echo "\n<table>\n";
	echo "\t<tr><th>Course</th><th>Term</th><th>Requests</th></tr>\n\t";
	while (($row = oci_fetch_array($query, OCI_BOTH)) != false) {
		echo "<tr>\n";
		// We can use either numeric indexed starting at 0
		// or the column name as an associative array index to access the colum value
		echo "\t\t<td>$row[0]</td>\n";
		echo "\t\t<td>$row[1]</td>\n";
		echo "\t\t<td>$row[2]</td>\n";
		echo "\t</tr>";
	}
	echo "\n</table>\n";

	OCILogoff($conn);
}

?>
</body>
</html>
