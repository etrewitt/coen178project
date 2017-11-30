<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Change an existing course request</title>
	<link rel="stylesheet" type="text/css" href="project.css">
</head>
<body>
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
  <form method="post" action="change_course.php">
    <fieldset>
      <legend>Old course</legend>
      <?php
        if (isset($_GET)) {
          $id = $_GET['id'];
          $dept = $_GET['dept'];
          $course = $_GET['course'];
          $year = $_GET['year'];
          $quarter = $_GET['quarter'];
          echo '<label for "old_id">Student ID</label><input type="text" id="old_id" name="id" value="' . $id  . '" readonly><br>' . "\n";
          echo '<label for "old_dept">Department</label><input type="text" id="old_dept" name="old_dept" value="' . $dept  . '" readonly><br>' . "\n";
          echo '<label for "old_courseNo">Course No.</label><input type="text" id="old_courseNo" name="old_courseNo" value="' . $course  . '" readonly><br>' . "\n";
          echo '<label for "old_year">Year</label><input type="text" id="old_year" name="old_year" value="' . $year  . '" readonly>' . "\n";
          echo '<label for "old_quarter">Quarter</label><input type="text" id="old_quarter" name="old_quarter" value="' . $quarter  . '" readonly><br>' . "\n";
        }
       ?>
       <!-- end PHP script -->
    </fieldset>
    <fieldset>
      <legend>Course input</legend>
      <label for="dept">Department</label>
      <select name="dept" id="dept">
        <option value="">Select a department</option>
        <option value="COEN">COEN</option>
        <option value="ENGR">ENGR</option>
        <option value="ELEN">ELEN</option>
        <!-- etc -->
      </select>
      <br>
      <label for="courseNo">Course No.</label>
      <input type="text" name="courseNo" value="", id="courseNo">
      <br>
      <label for="year">Year</label>
      <select name="year" id="year">
        <option value="2018">2018-2019</option>
        <option value="2019">2019-2020</option>
        <option value="2020">2020-2021</option>
        <option value="2021">2021-2022</option>
      </select>
      <label for="quarter">Quarter</label>
      <select name="quarter" id="quarter">
        <option value="Fall">Fall</option>
        <option value="Winter">Winter</option>
        <option value="Spring">Spring</option>
      </select>
    </fieldset>

    <input type="submit" value="Submit">
    <input type="reset" value="Reset">
  </form>
</body>
</html>
