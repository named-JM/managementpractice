<?php
    include "../db_connection.php";

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        // retrieve form data
        $emp_fname = $_POST['emp_fname'];
        $emp_mname = $_POST['emp_mname'];
        $emp_lname = $_POST['emp_lname'];
        $emp_position = $_POST['emp_position'];
        $emp_email = $_POST['emp_email'];
        $emp_number = $_POST['emp_number'];
        $emp_zip = $_POST['emp_zip'];
        // $emp_file = $_POST['emp_file'];

        // AFTER RETRIVING THE FORM THEN INSERTING DATA INTO EMPLOYEE DATA TABLE IN DATABASE
        $stmt = $conn->prepare("INSERT INTO employee_table (emp_fname, emp_mname, emp_lname, emp_position, emp_email, emp_number, emp_zip) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssi", $emp_fname,$emp_mname, $emp_lname, $emp_position, $emp_email, $emp_number, $emp_zip);
        if ($stmt->execute()) {
            $successMessage = "New record created successfully.";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
        $stmt->close();



    }
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
    <!-- EMPLOYEE FORM MANAGEMENT -->
    <form action="add_employee.php" method="post">
    <!-- EMP FIRSTNAME -->
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
    <!-- <label for="emp_file">Upload CV/Resume</label>
    <input type="text" id="emp_file" name="emp_file" required>
    <br><br> -->
    <input type="submit" value="Submit">
    </form>

    
    
</body>
</html>