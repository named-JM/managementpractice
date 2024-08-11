<?php
include "../db_connection.php";
$successMessage = "";
$errorMessage = "";
$emailErrorMessage = "";
$phoneErrorMessage = "";

// Initialize error messages
$emailErrorMessage = "";
$phoneErrorMessage = "";

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

    // Check if email or phone number already exists
    $checkQuery = $conn->prepare("SELECT emp_email, emp_number FROM employee_table WHERE emp_email = ? OR emp_number = ?");
    $checkQuery->bind_param("si", $emp_email, $emp_number);
    $checkQuery->execute();
    $checkQuery->store_result();
    $checkQuery->bind_result($dbEmail, $dbPhone);
    $checkQuery->fetch();
    

    if ($checkQuery->num_rows > 0) {
        // WHERE VALIDATING SEPARETE IF EMAIL ALREADY EXIST OR PHONE ALREADY EXIST TO KNOW WHAT USER IS WHAT THEIR WRONG
        if ($dbEmail == $emp_email){
            $emailErrorMessage = "Email already exist!";
        }
        if($dbPhone == $emp_number){
            $phoneErrorMessage = "Phone Number already exist!";
        }
    } else {
        // Generate `emp_company_num`
        $year = date('Y');
        $query = "SELECT MAX(emp_company_num) as max_num FROM employee_table WHERE emp_company_num LIKE 'EMP-$year-%'";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();

        if ($row['max_num']) {
            // Extract the last number part and increment it
            $last_num = (int)substr($row['max_num'], -9);
            $new_num = str_pad($last_num + 1, 9, '0', STR_PAD_LEFT);
        } else {
            // Start with 000000001 if no records exist for the year
            $new_num = '000000001';
        }
        $emp_company_num = "EMP-$year-$new_num";

        // Handle file upload
        $uploadsDir = "C:/xampp/secure_uploads/"; // Directory outside the web root
        $fileName = basename($_FILES["emp_file"]["name"]);
        $targetFile = $uploadsDir . $fileName;
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if file is a valid type (you can add more validation)
        $allowedTypes = array("pdf", "doc", "docx");
        if (!in_array($fileType, $allowedTypes)) {
            echo "<br>Sorry, only PDF, DOC, and DOCX files are allowed.";
            $uploadOk = 0;
        }

        // If UPLOADOK IS NOT OKAY (0) THEN MESSAGE NOT UPLOADED
        if ($uploadOk == 0) {
            echo "<br>Sorry, your file was not uploaded.";
        } else {
            // Try to move the file to the target directory
            if (move_uploaded_file($_FILES["emp_file"]["tmp_name"], $targetFile)) {
                $successMessage = "The file " . htmlspecialchars($fileName) . " has been uploaded.";

                // After checking uploaded file, inserting data to the database
                $stmt = $conn->prepare("INSERT INTO employee_table (emp_fname, emp_mname, emp_lname, emp_position, emp_company_num, emp_email, emp_number, emp_zip, employ_manager, employ_dept, emp_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssissiisss", $emp_fname, $emp_mname, $emp_lname, $emp_position, $emp_company_num, $emp_email, $emp_number, $emp_zip, $employ_manager, $employ_dept, $fileName);
                if ($stmt->execute()) {
                    $successMessage .= " New record created successfully.";
                } else {
                    $errorMessage = "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    $checkQuery->close();
}

// Get fetching the position data table in the database to display the drop-down
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
    <br>
    <a href="../employment.php">back with employment</a>
    <h1>Employee Management</h1>
    <!-- EMPLOYEE FORM MANAGEMENT -->
    <form action="add_employee.php" method="post" enctype="multipart/form-data"> <!--THE multipart FORM DATA TO  MAKE SURE INCLUDES THE FILE UPLOAD-->
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
    <select name="emp_position" id="emp_position" required
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
    <span style="color:red;"><?php echo $emailErrorMessage; ?></span>
    
    <br><br>
    <!-- EMP NUMBER -->
    <label for="emp_number">Phone Number</label>
    <input type="text" id="emp_number" name="emp_number" required>
    <span style="color:red;"><?php echo $phoneErrorMessage; ?></span>
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
    <br>
    <?php
    if ($successMessage) {
        echo "<p style='color:green;'>$successMessage</p>";
    }
    if ($errorMessage) {
        echo "<p style='color:red;'>$errorMessage</p>";
    }
    ?>
    
    
</body>
</html>