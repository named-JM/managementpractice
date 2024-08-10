<?php
include "../db_connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieving data from the form
    $emp_fname = $_POST['emp_fname'];
    $emp_mname = $_POST['emp_mname'];
    $emp_lname = $_POST['emp_lname'];
    $emp_position = $_POST['emp_position'];
    $emp_email = $_POST['emp_email'];
    $emp_number = $_POST['emp_number'];
    $emp_zip = $_POST['emp_zip'];
    $employ_manager = $_POST['employ_manager'];
    $employ_dept = $_POST['employ_dept'];


    // Handle file upload
    $uploadsDir = "C:/xampp/secure_uploads/"; // Directory outside the web root
    $fileName = basename($_FILES["emp_file"]["name"]);
    $targetFile = $uploadsDir . $fileName;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if file is a valid type (you can add more validation)
    $allowedTypes = array("pdf", "doc", "docx");
    if (!in_array($fileType, $allowedTypes)) {
        $errorMessage = "Sorry, only PDF, DOC, and DOCX files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $errorMessage = "Sorry, your file was not uploaded.";
    } else {
        // Try to move the file to the target directory
        if (move_uploaded_file($_FILES["emp_file"]["tmp_name"], $targetFile)) {
            $successMessage = "The file " . htmlspecialchars($fileName) . " has been uploaded.";

            // Insert the form data along with the file path into the database
            $stmt = $conn->prepare("INSERT INTO employee_table (emp_fname, emp_mname, emp_lname, emp_position, emp_email, emp_number, emp_zip, employ_manager, employ_dept, emp_file) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssississs", $emp_fname, $emp_mname, $emp_lname, $emp_position, $emp_email, $emp_number, $emp_zip, $employ_manager, $employ_dept, $fileName);
            if ($stmt->execute()) {
                $successMessage .= " New record created successfully.";
            } else {
                $errorMessage = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errorMessage = "Sorry, there was an error uploading your file.";
        }
    }
}

// Fetch positions for the dropdown
$positions = $conn->query("SELECT pos_id, pos_name FROM position");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
</head>
<body>
    <a href="../employment.php">back with employment</a>
    <h1>Employee Management</h1>
    <!-- EMPLOYEE FORM MANAGEMENT -->
    <form action="add_employee.php" method="post" enctype="multipart/form-data">
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
    <select name="emp_position" id="emp_position" required</select>>
        <option value="">Select Position</option>
        <?php
        if($positions->num_rows >0){
            while($position = $positions->fetch_assoc()){
                echo "<option value='" . $position['pos_id'] . "'>" . $position['pos_name'] . "</option>";
            }
        }else {
            echo "<option value=''>No Positions Available</option>";
        }
        ?>
    </select>
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

    <!-- EMPLOY MANAGER -->
    <label for="employ_manager">Manager</label>
    <input type="text" id="employ_manager" name="employ_manager" required>
    <br><br>

    <!-- EMPLOY DEPARTMENT -->
    <label for="employ_dept">Department</label>
    <input type="text" id="employ_dept" name="employ_dept" required>
    <br><br>

    <!-- EMP FILE/MOVE UPLOAD, WAG NA SA DATABASE -->
    <label for="emp_file">Upload CV/Resume</label>
    <input type="file" id="emp_file" name="emp_file" required>
    <br><br>
    <input type="submit" value="Submit">
    </form>

    
    
</body>
</html>