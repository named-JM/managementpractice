<?php
    include "../db_connection.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
</head>
<body>
    <h1>Employee Management</h1>
    <!-- EMP FIRSTNAME -->
    <form action="add_employee.php" method="post">
    <label for="emp_fname">First Name</label>
    <input type="text" id="emp_fname" name="emp_fname" required>
    <br><br>

    <!-- EMP MIDDLE NAME -->

    <label for="emp_mname">Middle Name</label>
    <input type="text" id="emp_mname" name="emp_mname" required>
    <br><br>
    <!-- EMP LAST NAME -->
    <label for="emp_lname">Last Name</label>
    <input type="text" id="emp_lname" name="emp_lname" required>
    <br><br>

    <!-- EMP POSITION  TAWAGAIN SA POSITION TABLE AS A NUMBER NA LANG-->
    <label for="emp_position">Position</label>
    <input type="text" id="emp_position" name="emp_position" required>
    <br><br>
    <!-- EMP EMAIL -->
    <label for="emp_email">Email</label>
    <input type="text" id="emp_email" name="emp_email" required>
    <br><br>
    <!-- EMP NUMBER -->
    <label for="emp_number">Phone Number</label>
    <input type="text" id="emp_number" name="emp_number" required>
    <br><br>

    <!-- EMP ZIP CODE PLACE -->
    <label for="emp_zip">Zip Code</label>
    <input type="text" id="emp_zip" name="emp_zip" required>
    <br><br>

    <!-- EMP FILE/MOVE UPLOAD, WAG NA SA DATABASE -->
    <label for="emp_file">Upload CV/Resume</label>
    <input type="text" id="emp_file" name="emp_file" required>
    <br><br>
    </form>

    
    
</body>
</html>