DROP TABLE majorReqs;
DROP TABLE passedCourses;
Drop table CourseRequests;
Drop table StudentList;
Drop table CourseList;
set serveroutput on;

Create table CourseList (
  dept            varchar(4),
  courseNo        varchar(3),
  name            varchar(127),
  units           number,
  prereq_dept     varchar(4),
  prereq_courseNo varchar(3),
  primary key (dept, courseNo),
  CONSTRAINT courseFkey FOREIGN KEY (prereq_dept, prereq_courseNo) REFERENCES CourseList(dept, courseNo)
);

Create table StudentList (
  studentID varchar(9) PRIMARY KEY,
  firstName varchar(20),
  lastName  varchar(30),
  major     varchar(20),
  startYear int
);

Create table CourseRequests (
  studentID  varchar(9),
  dept       varchar(4),
  courseNo   varchar(3),
  quarter    varchar(6),
  year       int,
  CONSTRAINT student Foreign Key (studentID)      REFERENCES StudentList(studentID),
  CONSTRAINT course  Foreign Key (dept, courseNo) REFERENCES CourseList(dept, courseNo),
  CHECK (quarter in ('Fall', 'Winter', 'Spring')),
  PRIMARY KEY (studentID, dept, courseNo)
);

CREATE TABLE passedCourses(
  studentID varchar(9),
  dept      varchar(4),
  courseNo  varchar(3),
  quarter   varchar(6),
  year      int,
  CHECK (quarter in ('Fall', 'Winter', 'Spring')),
  CONSTRAINT passedCourses_fkey1 FOREIGN KEY (studentID) REFERENCES StudentList(studentID),
  CONSTRAINT passedCourses_fkey2 FOREIGN KEY (dept, courseNo) REFERENCES CourseList(dept, courseNo)
);

Create Table majorReqs(
  dept varchar(4),
  courseNo varchar(3),
  CONSTRAINT majorReqs_fkey1 FOREIGN KEY (dept, courseNo) REFERENCES CourseList(dept, courseNo)
);

CREATE OR REPLACE TRIGGER prereqTTrigger
  FOR INSERT OR UPDATE ON CourseRequests
COMPOUND TRIGGER

  l_ID StudentList.studentID%type;
  l_prereq_dept CourseList.prereq_dept%type;
  l_prereq_courseNo CourseList.prereq_courseNo%type;
  l_dept CourseList.dept%type;
  l_courseNo CourseList.courseNo%type;
  l_year  CourseRequests.year%type;
  l_quarter  CourseRequests.quarter%type;
  l_passed_count NUMBER(1,0);
  l_planned_count NUMBER(1,0);
  l_taken NUMBER(1,0);

BEFORE EACH ROW IS
BEGIN
  SELECT  prereq_dept, prereq_courseNo
    INTO  l_prereq_dept, l_prereq_courseNo
    FROM  CourseList
    WHERE dept = :new.dept
    AND   courseNo = :new.courseNo;
  l_ID       := :new.studentID;
  l_dept     := :new.dept;
  l_courseNo := :new.courseNo;
  l_year     := :new.year;
  l_quarter  := :new.quarter;
END BEFORE EACH ROW;

AFTER STATEMENT IS
BEGIN
  SELECT  count(*)
    INTO  l_taken
    FROM  passedCourses
    WHERE studentID = l_ID AND
          dept      = l_dept AND
          courseNo  = l_courseNo;

  -- spring needs to come last for comparisons, so that it works out to be fall < winter < spring
  if l_quarter = 'Spring' THEN
    l_quarter := 'ZSpr';
  END IF;

  -- if a prereq exists and they haven't taken the requested course yet
  if l_prereq_dept <> 'N/A' AND l_taken = 0 THEN
    -- see if student has already taken the prereq
    SELECT  count(*)
      INTO  l_passed_count
      FROM  passedCourses
      WHERE studentID = l_ID AND
            dept      = l_prereq_dept AND
            courseNo  = l_prereq_courseNo;
    -- see if student PLANS to take the prereq prior to the requested course
    SELECT  count(*)
      INTO  l_planned_count
      FROM  courseRequests
      WHERE studentID  = l_ID AND
            dept       = l_prereq_dept AND
            courseNo   = l_prereq_courseNo AND (
              year      < l_year OR (
                year    = l_year AND
                quarter < l_quarter
              )
            );

    if (l_passed_count = 0 AND l_planned_count = 0) THEN
      RAISE_APPLICATION_ERROR (-20000, 'Cannot take course before prereq');
    END IF;
  ELSIF l_taken <> 0 THEN
    RAISE_APPLICATION_ERROR (-20001, 'Cannot add course previously completed');
  END IF;

END AFTER STATEMENT;
END;
/
Show Errors;

Insert into StudentList values('0', 'Dorthea', 'Dillon', 'COEN', 2018);
Insert into StudentList values('1', 'Genia', 'Goris', 'COEN', 2018);
Insert into StudentList values('2', 'Werner', 'Wexler', 'COEN', 2018);
Insert into StudentList values('3', 'Verona', 'Viens', 'COEN', 2018);
Insert into StudentList values('4', 'Tad', 'Town', 'COEN', 2018);
Insert into StudentList values('5', 'Rachal', 'Rohloff', 'COEN', 2018);
Insert into StudentList values('6', 'Hailey', 'Haile', 'COEN', 2018);
Insert into StudentList values('7', 'Freddie', 'Fugate', 'COEN', 2018);
Insert into StudentList values('8', 'Lyda', 'Lamantia', 'COEN', 2018);
Insert into StudentList values('9', 'Cira', 'Collin', 'COEN', 2018);
Insert into StudentList values('10', 'Wilson', 'Welden', 'COEN', 2018);
Insert into StudentList values('11', 'Leo', 'Laguardia', 'COEN', 2018);
Insert into StudentList values('12', 'Derick', 'Dammann', 'COEN', 2018);
Insert into StudentList values('13', 'Claribel', 'Cade', 'COEN', 2018);
Insert into StudentList values('14', 'Jenette', 'Jury', 'COEN', 2018);
Insert into StudentList values('15', 'Bambi', 'Bobo', 'COEN', 2018);
Insert into StudentList values('16', 'Dena', 'Delcid', 'COEN', 2018);
Insert into StudentList values('17', 'Renata', 'Rocchio', 'COEN', 2018);
Insert into StudentList values('18', 'Jaymie', 'Jose', 'COEN', 2018);
Insert into StudentList values('19', 'Gena', 'Galasso', 'COEN', 2018);
Insert into StudentList values('20', 'Donette', 'Delamater', 'COEN', 2019);
Insert into StudentList values('21', 'Dotty', 'Dark', 'COEN', 2019);
Insert into StudentList values('22', 'Gail', 'Gregori', 'COEN', 2019);
Insert into StudentList values('23', 'Dorcas', 'Darlington', 'COEN', 2019);
Insert into StudentList values('24', 'Willodean', 'Warkentin', 'COEN', 2019);
Insert into StudentList values('25', 'Loma', 'Leday', 'COEN', 2019);
Insert into StudentList values('26', 'Tarra', 'Turk', 'COEN', 2019);
Insert into StudentList values('27', 'Carlyn', 'Caddell', 'COEN', 2019);
Insert into StudentList values('28', 'Hazel', 'Hamby', 'COEN', 2019);
Insert into StudentList values('29', 'Annika', 'Agular', 'COEN', 2019);
Insert into StudentList values('30', 'Lilla', 'Lacross', 'COEN', 2019);
Insert into StudentList values('31', 'Shera', 'Stanford', 'COEN', 2019);
Insert into StudentList values('32', 'Alan', 'Arno', 'COEN', 2019);
Insert into StudentList values('33', 'Dwana', 'Dennison', 'COEN', 2019);
Insert into StudentList values('34', 'Chanell', 'Cail', 'COEN', 2019);
Insert into StudentList values('35', 'Felipe', 'Frank', 'COEN', 2019);
Insert into StudentList values('36', 'Olimpia', 'Ortner', 'COEN', 2019);
Insert into StudentList values('37', 'Nobuko', 'Nott', 'COEN', 2019);
Insert into StudentList values('38', 'Olivia', 'Ocallaghan', 'COEN', 2019);
Insert into StudentList values('39', 'Aida', 'Auger', 'COEN', 2019);
Insert into StudentList values('40', 'Delta', 'Dooling', 'COEN', 2019);
Insert into StudentList values('41', 'Tanner', 'Thao', 'COEN', 2020);
Insert into StudentList values('42', 'Micheline', 'Mccane', 'COEN', 2020);
Insert into StudentList values('43', 'Carlota', 'Cate', 'COEN', 2020);
Insert into StudentList values('44', 'Santiago', 'Stimson', 'COEN', 2020);
Insert into StudentList values('45', 'Charlsie', 'Cornette', 'COEN', 2020);
Insert into StudentList values('46', 'Tabitha', 'Trace', 'COEN', 2020);
Insert into StudentList values('47', 'Sherrie', 'Sebastian', 'COEN', 2020);
Insert into StudentList values('48', 'Loreen', 'Laforest', 'COEN', 2020);
Insert into StudentList values('49', 'Margrett', 'Mccarroll', 'COEN', 2020);

Insert into CourseList values ('ENGR', '1',   'Introduction to Engineering', 2, 'N/A', '');
Insert into CourseList values ('COEN', '10',  'Introduction to Programming', 5, 'ENGR', '1');
Insert into CourseList values ('COEN', '11',  'Advanced Programming', 5, 'ENGR', '1');
Insert into CourseList values ('COEN', '12',  'Abstract Data Types and Data Structures', 5, 'ENGR', '1');
Insert into CourseList values ('COEN', '19',  'Discrete Mathematics', 4, 'ENGR', '1');
Insert into CourseList values ('COEN', '20',  'Embedded Systems', 5, 'ENGR', '1');
Insert into CourseList values ('COEN', '21',  'Logic Design', 5, 'ENGR', '1');
Insert into CourseList values ('COEN', '79',  'OO Programming and Advanced Data Structures', 5, 'ENGR', '1');
Insert into CourseList values ('COEN', '122', 'Computer Architecture', 5, 'ENGR', '1');
Insert into CourseList values ('COEN', '146', 'Computer Networks', 5, 'ENGR', '1');
Insert into CourseList values ('COEN', '171', 'Design and Implementation of Programming Languages', 4, 'ENGR', '1');
Insert into CourseList values ('COEN', '174', 'Software Engineering', 5, 'ENGR', '1');
Insert into CourseList values ('COEN', '175', 'Formal Language Theory and Compiler Construction', 5, 'ENGR', '1');
Insert into CourseList values ('COEN', '177', 'Operating Systems', 5, 'ENGR', '1');
Insert into CourseList values ('COEN', '179', 'Theory of Algorithms', 4, 'ENGR', '1');
Insert into CourseList values ('COEN', '194', 'Design Project I', 2, 'ENGR', '1');
Insert into CourseList values ('COEN', '195', 'Design Project II', 2, 'ENGR', '1');
Insert into CourseList values ('COEN', '196', 'Design Project III', 2, 'ENGR', '1');

Insert into majorReqs values ('ENGR', '1');
Insert into majorReqs values ('COEN', '10');
Insert into majorReqs values ('COEN', '11');
Insert into majorReqs values ('COEN', '12');
Insert into majorReqs values ('COEN', '19');
Insert into majorReqs values ('COEN', '20');
Insert into majorReqs values ('COEN', '21');
Insert into majorReqs values ('COEN', '79');
Insert into majorReqs values ('COEN', '122');
Insert into majorReqs values ('COEN', '146');
Insert into majorReqs values ('COEN', '171');
Insert into majorReqs values ('COEN', '174');
Insert into majorReqs values ('COEN', '175');
Insert into majorReqs values ('COEN', '177');
Insert into majorReqs values ('COEN', '179');
Insert into majorReqs values ('COEN', '194');
Insert into majorReqs values ('COEN', '195');
Insert into majorReqs values ('COEN', '196');

Insert into passedCourses values ('0', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('1', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('2', 'ENGR', '1', 'Fall', 2018);
Insert into passedCourses values ('3', 'ENGR', '1', 'Fall', 2018);
Insert into passedCourses values ('4', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('5', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('6', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('7', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('8', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('9', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('10', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('11', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('12', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('13', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('14', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('15', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('16', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('17', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('18', 'ENGR', '1', 'Fall', 2018);
-- Insert into passedCourses values ('19', 'ENGR', '1', 'Fall', 2018);

Insert into CourseRequests values ('0', 'COEN', '122', 'Fall', 2019);
Insert into CourseRequests values ('1', 'COEN', '122', 'Fall', 2019);
Insert into CourseRequests values ('2', 'COEN', '122', 'Fall', 2019);
Insert into CourseRequests values ('3', 'COEN', '122', 'Spring', 2020);
Insert into CourseRequests values ('4', 'COEN', '122', 'Winter', 2020);
Insert into CourseRequests values ('5', 'COEN', '122', 'Fall', 2019);
Insert into CourseRequests values ('6', 'COEN', '122', 'Winter', 2019);
Insert into CourseRequests values ('7', 'COEN', '122', 'Winter', 2020);
Insert into CourseRequests values ('8', 'COEN', '122', 'Spring', 2019);
Insert into CourseRequests values ('9', 'COEN', '122', 'Fall', 2020);

Insert into CourseRequests values ('0', 'COEN', '177', 'Winter', 2021);
Insert into CourseRequests values ('1', 'COEN', '177', 'Fall', 2020);
Insert into CourseRequests values ('2', 'COEN', '177', 'Fall', 2020);
Insert into CourseRequests values ('3', 'COEN', '177', 'Spring', 2020);
Insert into CourseRequests values ('4', 'COEN', '177', 'Spring', 2020);


Insert into majorReqs values ('ENGR', '1');
Insert into majorReqs values ('COEN', '10');
Insert into majorReqs values ('COEN', '11');
Insert into majorReqs values ('COEN', '12');
Insert into majorReqs values ('COEN', '19');
Insert into majorReqs values ('COEN', '20');
Insert into majorReqs values ('COEN', '21');
Insert into majorReqs values ('COEN', '79');
Insert into majorReqs values ('COEN', '122');
Insert into majorReqs values ('COEN', '146');
Insert into majorReqs values ('COEN', '171');
Insert into majorReqs values ('COEN', '174');
Insert into majorReqs values ('COEN', '175');
Insert into majorReqs values ('COEN', '177');
Insert into majorReqs values ('COEN', '179');
Insert into majorReqs values ('COEN', '194');
Insert into majorReqs values ('COEN', '195');
Insert into majorReqs values ('COEN', '196');

-- SELECT   distinct mr.dept, mr.courseNo
--   FROM     majorReqs mr
--   WHERE    (mr.dept, mr.courseNo) not in (
--     Select dept, courseNo
--     from   passedCourses
--     where  studentID = '4'
--   ) and    (mr.dept, mr.courseNo) not in (
--     Select dept, courseNo
--     from   courseRequests
--     where  studentID = '4'
--   )
--   ORDER BY mr.dept, mr.courseNo;

-- Insert into CourseRequests values ('6', 'COEN', '177', 'Spring', 2020);
--
-- SELECT  count(*)
--   FROM  courseRequests
--   WHERE studentID = '6' AND
--         dept      = 'ENGR'  AND
--         courseNo  = '1';
--
-- Insert into CourseRequests values ('4', 'ENGR', '1', 'Spring', 2020);


-- Select * from courseRequests where studentID = '17';
Insert into courseRequests values ('17', 'ENGR', '1', 'Fall', 2020);
-- Insert into courseRequests values ('17', 'COEN', '177', 'Fall', 2020);
-- Delete from courseRequests where studentID = '17' AND courseNo = '177';
-- Insert into courseRequests values ('17', 'COEN', '177', 'Fall', 2019);
-- Insert into courseRequests values ('17', 'COEN', '177', 'Winter', 2020);
--
-- Select * from courseRequests where studentID = '18';
Insert into courseRequests values ('18', 'ENGR', '1', 'Winter', 2020);
-- Insert into courseRequests values ('18', 'COEN', '177', 'Spring', 2020);
-- Delete from courseRequests where studentID = '18' AND courseNo = '177';
-- Insert into courseRequests values ('18', 'COEN', '177', 'Fall', 2019);
-- Insert into courseRequests values ('18', 'COEN', '177', 'Winter', 2020);

-- SELECT *
-- FROM   CourseRequests
-- WHERE  studentID = '17' and
--        dept      = 'ENGR' and
--        courseNo  = '1' and not
--        (year     = 2018 and
--        quarter   = 'Fall');
