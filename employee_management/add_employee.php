<?php
include "../db_connection.php";
$successMessage = "";
$errorMessage = "";
$emailErrorMessage = "";
$phoneErrorMessage = "";

// Initialize error messages
$emailErrorMessage = "";
$phoneErrorMessage = "";

// Function to generate a random password
function generatePassword($length = 8) {
    $characters = '0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

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
        if ($dbEmail == $emp_email) {
            $emailErrorMessage = "Email already exists!";
        }
        if ($dbPhone == $emp_number) {
            $phoneErrorMessage = "Phone Number already exists!";
        }
    } else {
        // Generate `emp_company_num`
        $year = date('Y');
        $query = "SELECT MAX(emp_company_num) as max_num FROM employee_table WHERE emp_company_num LIKE 'EMP-$year-%'";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();

        if ($row['max_num']) {
            $last_num = (int)substr($row['max_num'], -9);
            $new_num = str_pad($last_num + 1, 9, '0', STR_PAD_LEFT);
        } else {
            $new_num = '000000001';
        }
        $emp_company_num = "EMP-$year-$new_num";

        // Generate password
        $emp_password = generatePassword();

        // Handle file upload
        $uploadsDir = "C:/xampp/htdocs/management/secure_uploads/";
        $fileName = basename($_FILES["emp_file"]["name"]);
        $targetFile = $uploadsDir . $fileName;
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        $allowedTypes = array("pdf", "doc", "docx");
        if (!in_array($fileType, $allowedTypes)) {
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            echo "<br>Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["emp_file"]["tmp_name"], $targetFile)) {
                $successMessage = "The file " . htmlspecialchars($fileName) . " has been uploaded.";

                // After checking uploaded file, inserting data to the database
                $stmt = $conn->prepare("INSERT INTO employee_table (emp_fname, emp_mname, emp_lname, emp_position, emp_company_num, emp_email, emp_number, emp_zip, employ_manager, employ_dept, emp_status, emp_password, emp_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', ?, ?)");
                $stmt->bind_param("sssissiissss", $emp_fname, $emp_mname, $emp_lname, $emp_position, $emp_company_num, $emp_email, $emp_number, $emp_zip, $employ_manager, $employ_dept, $emp_password, $fileName);
                if ($stmt->execute()) {
                    $successMessage .= " New record created successfully. Password: $emp_password";
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
$departments = $conn->query("SELECT dept_id, dept_name FROM department");
$managers = $conn->query("SELECT user_id, user_full_name FROM user_management");

$result = $conn->query("
SELECT employee_table.*, position.pos_name, user_management.user_full_name, department.dept_name FROM employee_table
JOIN position ON employee_table.emp_position = position.pos_id
JOIN user_management ON employee_table.employ_manager = user_management.user_id
JOIN department ON employee_table.employ_dept = department.dept_id");
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <!-- TAILWING CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- tialiwind css file -->
    <link href="./output.css" rel="stylesheet">
    <!-- datatable style cdn -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">
    <!-- font awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- SWEETALERT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- As an old-school alternative, you can initialize the plugin by referencing the necessary files: -->
    <script src="sweetalert2.all.min.js"></script>
    <!-- Or with the stylesheet separately if desired: -->
    <script src="sweetalert2.min.js"></script>
    <link rel="stylesheet" href="sweetalert2.min.css">
</head>
<style>
    .swal2-popup {
            width: 500px !important;
            padding: 20px !important;
            text-align: left;
        }
        .swal2-content {
            text-align: left !important;
        }
        .swal2-input {
            width: 100% !important;
            margin-bottom: 15px;
        }
        .swal2-select {
            width: 100% !important;
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
    span.ripple {
        position: absolute;
        border-radius: 50%;
        transform: scale(0);
        animation: ripple 600ms linear;
        background-color: rgba(255, 255, 255, 0.7);
        }
        @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
        }
</style>

<body class="p-20 m-2 bg-gray-100">
    <br>
    <button type="button" id="openFormBtn"
        class="relative px-5 py-3 overflow-hidden text-white bg-indigo-500 rounded shadow min-w-max hover:bg-opacity-90">
        Add Employee
    </button>

    <script>
        // AUTO CAPITAL IN THE FIRST LETTER
        function capitalizeFirstLetter(input) {
        return input.charAt(0).toUpperCase() + input.slice(1).toLowerCase();
    }
        function rippleEffect(event) {
                const btn = event.currentTarget;

                const circle = document.createElement("span");
                const diameter = Math.max(btn.clientWidth, btn.clientHeight);
                const radius = diameter / 2;

                circle.style.width = circle.style.height = `${diameter}px`;
                circle.style.left = `${event.clientX - (btn.offsetLeft + radius)}px`;
                circle.style.top = `${event.clientY - (btn.offsetTop + radius)}px`;
                circle.classList.add("ripple");

                const ripple = btn.getElementsByClassName("ripple")[0];

                if (ripple) {
                    ripple.remove();
                }
                btn.appendChild(circle);
            }
            const btn = document.getElementById("openFormBtn");
            btn.addEventListener("click", rippleEffect);
            
        // SWEETALERT ADDING FORM HERE!!!!!
        document.getElementById('openFormBtn').addEventListener('click', function() {
            Swal.fire({
            title: 'Employment Contract Form',
            html: `
            <!-- EMPLOYEE FORM MANAGEMENT -->
            <form id="employeeForm" class="space-y-4 text-left" action="add_employee.php" method="post" enctype="multipart/form-data"> <!--THE multipart FORM DATA TO  MAKE SURE INCLUDES THE FILE UPLOAD-->
            <!-- EMP FIRSTNAME -->
            <label for="emp_fname" class="block text-sm font-medium text-gray-700">First Name</label>
            <input type="text" id="emp_fname" name="emp_fname" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"required>
            

            <!-- EMP MIDDLE NAME -->

            <label for="emp_mname" class="block text-sm font-medium text-gray-700">Middle Name</label>
            <input type="text" id="emp_mname" name="emp_mname" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"required>
            <!-- EMP LAST NAME -->
            <label for="emp_lname" class="block text-sm font-medium text-gray-700">Last Name</label>
            <input type="text" id="emp_lname" name="emp_lname" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"required>
            

            <!-- EMP POSITION  TAWAGAIN SA POSITION TABLE AS A NUMBER NA LANG-->
            <label for="emp_position" class="block text-sm font-medium text-gray-700">Position</label>
            <select name="emp_position" id="emp_position" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"required>
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
            
            <!-- EMP EMAIL -->
            <label for="emp_email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="text" id="emp_email" name="emp_email" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"required>
            <span style="color:red;"><?php echo $emailErrorMessage; ?></span>
            
            
            <!-- EMP NUMBER -->
            <label for="emp_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
            <input type="text" id="emp_number" name="emp_number" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"required>
            <span style="color:red;"><?php echo $phoneErrorMessage; ?></span>
            

            <!-- EMP ZIP CODE PLACE -->
            <label for="emp_zip" class="block text-sm font-medium text-gray-700">Zip Code</label>
            <input type="text" id="emp_zip" name="emp_zip"class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>

            <!-- EMPLOY MANAGER -->
            <label for="employ_manager" class="block text-sm font-medium text-gray-700">Manager</label>
            <select name="employ_manager" id="employ_manager"  class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"required>
            // I WANT TO MAKE THIS INTO SELECTION OF DEPARTMENT NAME
                <option value="">Select Manager</option>
                <?php
                if($managers->num_rows >0){
                    while($manager = $managers->fetch_assoc()){
                        echo "<option value='" . $manager['user_id'] . "'>" . $manager['user_full_name'] . "</option>";
                    }
                }else {
                    echo "<option value=''>No Department Available</option>";
                }
                
                ?>
            </select>

            <!-- EMPLOY DEPARTMENT -->
            <label for="employ_dept" class="block text-sm font-medium text-gray-700">Department</label>
            <select name="employ_dept" id="employ_dept"  class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"required>
            // I WANT TO MAKE THIS INTO SELECTION OF DEPARTMENT NAME
                <option value="">Select Manager</option>
                <?php
                if($departments->num_rows >0){
                    while($department = $departments->fetch_assoc()){
                        echo "<option value='" . $department['dept_id'] . "'>" . $department['dept_name'] . "</option>";
                    }
                }else {
                    echo "<option value=''>No Department Available</option>";
                }
                
                ?>
            </select>

            <!-- EMP FILE/MOVE UPLOAD, WAG NA SA DATABASE -->
            <label for="emp_file" class="block text-sm font-medium text-gray-700">Upload CV/Resume</label>
            <input type="file" id="emp_file" name="emp_file" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"required>
            </form>
            `,
            showCancelButton: true,
                cancelButtonColor: "#d33",
                confirmButtonText: 'Submit',
                width: '400px',
                customClass: {
                    popup: 'swal-wide', // Additional custom class if needed
                },
                preConfirm: () => {

                    // CALL FUNCTION TO capitalizeFirstLetter WHEN SUBMITTING
                    let emp_fname = capitalizeFirstLetter(document.getElementById('emp_fname').value);
                let emp_mname = capitalizeFirstLetter(document.getElementById('emp_mname').value);
                let emp_lname = capitalizeFirstLetter(document.getElementById('emp_lname').value);
                let employ_manager = capitalizeFirstLetter(document.getElementById('employ_manager').value);
                let employ_dept = capitalizeFirstLetter(document.getElementById('employ_dept').value);

                // Set the capitalized values back to the form inputs
                document.getElementById('emp_fname').value = emp_fname;
                document.getElementById('emp_mname').value = emp_mname;
                document.getElementById('emp_lname').value = emp_lname;
                document.getElementById('employ_manager').value = employ_manager;
                document.getElementById('employ_dept').value = employ_dept;

                    // SUBMITTING AFTER VALIDATION!!!
                    document.getElementById('employeeForm').submit();
                }
            });
        });
    </script>


    <!--DISPLAY TABLE  -->
    <table border="1" id="employee_table" class="w-full bg-white rounded-lg shadow-lg display">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Last Name</th>
                <th>Position</th>
                <th>Email</th>
                <th>Number</th>
                <th>Zip Code</th>
                <th>Manager</th>
                <th>Department</th>
                <th>Status</th>
                <th>Action</th>
                
            </tr>
        </thead>

        <tbody>
            <?php
                while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['emp_fname'];?></td>
                    <td><?php echo $row['emp_mname'];?></td>
                    <td><?php echo $row['emp_lname'];?></td>
                    <td><?php echo $row['pos_name']; ?></td> <!-- Displaying pos_name instead of emp_position -->
                    <td><?php echo $row['emp_email'];?></td>
                    <td><?php echo $row['emp_number'];?></td>
                    <td><?php echo $row['emp_zip'];?></td>
                    <td><?php echo $row['user_full_name'];?></td> <!-- i want to display here instead of employ_manger it should be the user_full_name from the user_management table -->
                    <td><?php echo $row['dept_name'];?></td>
                    <td><?php echo htmlspecialchars($row["emp_status"]); ?></td>
                    <td>
                    <button type="button" class="updateBtn" data-id="<?php echo $row['emp_company_num']; ?>">
                    <i class="fas fa-edit"></i> Update
                    </button>
                    <?php if($row['emp_file']): ?>
                    <a href="../secure_uploads/<?php echo rawurlencode($row['emp_file']); ?>" 
                    download="<?php echo htmlspecialchars($row['emp_file']); ?>" 
                    class="text-blue-600 hover:text-blue-800">
                        Download
                    </a>
                    <?php else: ?>
                        <span class="text-gray-600">No file</span>
                    <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
        </tbody>
    </table>
    <br>
    <?php
    if ($successMessage) {
        echo "<p style='color:green;'>$successMessage</p>";
    }
    if ($errorMessage) {
        echo "<p style='color:red;'>$errorMessage</p>";
    }
    ?>
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS CDN -->
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
    <!-- DataTables Initialization Script -->
    <script>
        $(document).ready( function () {
            $('#employee_table').DataTable();



            $('.updateBtn').on('click', function() {
            var emp_company_num = $(this).data('id');
            var emp_status = $(this).data('status');

            // Fetch employee data using AJAX
            $.ajax({
                url: 'fetch_employee.php',
                type: 'POST',
                data: { emp_company_num: emp_company_num },
                dataType: 'json',
                success: function(data) {
                        // SWWERT ALERT FORM EDITING/UPDATING HERE!!!!
                        Swal.fire({
                            title: 'Update Employee',
                            html: `
                                <form id="updateEmployeeForm" class="space-y-4 text-left" action="update_employee.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="emp_company_num" value="${data.emp_company_num}">
                                <label for="emp_fname" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" id="emp_fname" name="emp_fname" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="${data.emp_fname}" required>
                                <label for="emp_mname" class="block text-sm font-medium text-gray-700">Middle Name</label>
                                <input type="text" id="emp_mname" name="emp_mname" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="${data.emp_mname}" required>
                                <label for="emp_lname" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" id="emp_lname" name="emp_lname" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="${data.emp_lname}" required>
                                <label for="emp_position" class="block text-sm font-medium text-gray-700">Position</label>
                                <select name="emp_position" id="emp_position" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <option value="${data.emp_position}">${data.pos_name}</option>
                                    <?php
                                        $positions->data_seek(0);
                                        while($position = $positions->fetch_assoc()) {
                                            echo "<option value='" . $position['pos_id'] . "'>" . $position['pos_name'] . "</option>";
                                        }
                                    ?>
                                </select>
                                <label for="emp_email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" id="emp_email" name="emp_email" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="${data.emp_email}" required>
                                <label for="emp_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="text" id="emp_number" name="emp_number" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="${data.emp_number}" required>
                                <label for="emp_zip" class="block text-sm font-medium text-gray-700">Zip Code</label>
                                <input type="text" id="emp_zip" name="emp_zip" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="${data.emp_zip}" required>
                                <label for="employ_manager" class="block text-sm font-medium text-gray-700">Manager</label>
                                <select name="emp_position" id="emp_position" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <option value="${data.employ_manager}">${data.employ_manager}</option>
                                    <?php
                                        $managers->data_seek(0);
                                        while($manager = $managers->fetch_assoc()) {
                                            echo "<option value='" . $manager['user_id'] . "'>" . $manager['user_full_name'] . "</option>";
                                        }
                                    ?>
                                </select>
                                <input type="text" id="employ_manager" name="employ_manager" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="${data.employ_manager}" required>
                                <label for="employ_dept" class="block text-sm font-medium text-gray-700">Department</label>
                                <input type="text" id="employ_dept" name="employ_dept" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="${data.employ_dept}" required>
                                
                                <select id="emp_status" name="emp_status" class="w-full py-2 mt-1 mb-5 text-left border rounded">
                            <option value="Active" ${emp_status === 'Active' ? 'selected' : ''}>Active</option>
                            <option value="AWOL" ${emp_status === 'AWOL' ? 'selected' : ''}>AWOL</option>
                            <option value="Terminated" ${emp_status === 'Terminated' ? 'selected' : ''}>Terminated </option>
                        </select>
                                
                                </form>
                            `,
                        showCancelButton: true,
                        cancelButtonColor: "#d33",
                        confirmButtonText: 'Update',
                        preConfirm: () => {
                            document.getElementById('updateEmployeeForm').submit();
                        }
                    });
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                }
            });
        });
        });
    </script>
</body>
</html>