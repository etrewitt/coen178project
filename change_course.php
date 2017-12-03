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
        ><li><a id="request" href="enter_course.html">Request Course</a></li
        ><li><a id="viewRequests" class="current" href="requests.php">Requests</a></li
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

  $old_dept = $_POST['old_dept'];
	$old_courseNo = $_POST['old_courseNo'];
	$old_year = $_POST['old_year'];
	$old_quarter = $_POST['old_quarter'];

	$dept = $_POST['dept'];
	$courseNo = $_POST['courseNo'];
	$year = $_POST['year'];
	$quarter = $_POST['quarter'];

	// validate selection
	$valid = courseExists($dept, $courseNo)
				 & isRequesting($id, $old_dept, $old_courseNo, $old_year, $old_quarter)
				 & notRequesting($id, $dept, $courseNo, $old_year, $old_quarter);

	if ($valid) {
		// add course
		changeCourse($id, $old_dept, $old_courseNo, $old_year, $old_quarter, $dept, $courseNo, $year, $quarter);
	}
}

function courseExists($dept, $courseNo) {
	$conn = oci_connect( 'etrewitt', '/* password here */', '//dbserver.engr.scu.edu/db11g' );
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
		print "<p><b>$dept $courseNo</b> doesn't exist; valid courses in $dept are:</p>";
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

	OCILogoff($conn);
	return true;
}

function isRequesting($id, $dept, $courseNo, $year, $quarter) {
  $conn = oci_connect( 'etrewitt', '/* password here */', '//dbserver.engr.scu.edu/db11g' );
  if (!$conn) {
    print "<br> connection failed:";
    exit;
  }

  // make sure the student is already requesting this course
	$query = oci_parse(
		$conn,
		"SELECT *
		 FROM   CourseRequests
		 WHERE  studentID = '$id'       and
		        dept      = '$dept'     and
						courseNo  = '$courseNo' and
            year      = '$year'     and
            quarter   = '$quarter'"
	);
	oci_execute($query);
	$i = 0;
	while (($row = oci_fetch_array($query, OCI_BOTH)) != false) {
		$i++;
	}
	if ($i == 0) {
		print "<p>$id isn't currently requesting $dept $courseNo</p>";
		OCILogoff($conn);
		return false;
	}

  OCILogoff($conn);
  return true;
}

function notRequesting($id, $dept, $courseNo, $year, $quarter) {
  $conn = oci_connect( 'etrewitt', '/* password here */', '//dbserver.engr.scu.edu/db11g' );
  if (!$conn) {
    print "<br> connection failed:";
    exit;
  }

  // make sure the student is not already requesting this course (except if it's the same year/quarter that's already being moved)
	$query = oci_parse(
		$conn,
		"SELECT *
		 FROM   CourseRequests
		 WHERE  studentID = '$id'       and
		        dept      = '$dept'     and
						courseNo  = '$courseNo' and not (
		 					year    = '$year'     and
							quarter = '$quarter'
						)"
	);
	oci_execute($query);
	$i = 0;
	while (($row = oci_fetch_array($query, OCI_BOTH)) != false) {
		$i++;
	}
	if ($i != 0) {
		print "<p> $id is already requesting $dept $courseNo </p>";
		OCILogoff($conn);
		return false;
	}

  OCILogoff($conn);
  return true;
}

function changeCourse($id, $old_dept, $old_courseNo, $old_year, $old_quarter, $dept, $courseNo, $year, $quarter) {
	$conn = oci_connect( 'etrewitt', '/* password here */', '//dbserver.engr.scu.edu/db11g' );
	if (!$conn) {
		print "<br> connection failed:";
		exit;
	}

	$query = oci_parse(
		$conn,
		"UPDATE CourseRequests
     SET    dept = '$dept',
            courseNo = '$courseNo',
            year = $year,
            quarter = '$quarter'
     WHERE  studentID = '$id'          and
            dept = '$old_dept'         and
            courseNo = '$old_courseNo' and
            year = $old_year           and
            quarter = '$old_quarter'"
	);
	if (! @oci_execute($query)) {
		$err = oci_error($query);
		$code = $err["code"];
		if ($code == 20000) {
			echo "<p><b>Error $code:</b> Failed to meet the prerequisite(s) for $dept $courseNo.</p>\n";
		} elseif ($code == 20001) {
			echo "<p><b>Error $code:</b> Already completed $dept $courseNo.</p>\n";
		}
	} else {
		$nextYear = $year + 1;
		print "<p>Successfully requested $dept $courseNo for $id in the $quarter $year-$nextYear quarter.</p>";
	}

	OCILogoff($conn);
}

?>
		</main>
	</body>
</html>
