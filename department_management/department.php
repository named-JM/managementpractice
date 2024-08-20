<?php

include "../db_connection.php";


if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dept_name = $_POST['dept_name'] ?? '';
    $dept_code= $_POST['dept_code'] ?? '';
    $dept_head = $_POST['dept_head'] ?? '';

    if (!empty($dept_name) && !empty($dept_code) && !empty($dept_head)) {
        $stmt = $conn->prepare("INSERT INTO department (dept_name, dept_code, dept_head) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $dept_name, $dept_code, $dept_head);

        if ($stmt->execute()) {
            $successMessage = "New record created successfully.";
            // Redirect after successful submission to avoid resubmission on refresh
            header("Location: department.php?success=1");
            exit();
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "All fields are required.";
    }
}

// FETCHING DEPARTMENT TO DOSPPALY TABLE AND TO DISPLA THE USER FULL NAME
$result = $conn->query("SELECT department.dept_name, department.dept_code, user_management.user_full_name 
                        FROM department
                        JOIN user_management ON department.dept_head = user_management.user_id");
// FETCHING THE USER ID ROLE FOR DISPLAYING USER ROLES IN DEPARTMENET SELECTION
$roles = $conn->query("SELECT user_id, user_full_name FROM user_management");

$deptHeadRolesOptions = "";
if ($roles->num_rows > 0) {
    while ($role= $roles->fetch_assoc()) {
        $deptHeadRolesOptions .= "<option value='" . $role['user_id'] . "'>" . $role['user_full_name'] . "</option>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management</title>
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
   
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS CDN -->
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
    <!-- DataTables Initialization Script -->
    <script>
    $(document).ready(function () {
        $('#department_table').DataTable();

        // MODAL FORM SWEETALERT!!!!
        $('#openFormBtn').on('click', function () {
            Swal.fire({
                title: 'Department Form',
                html: `
                <form id="departmentForm" action="department.php" method="POST" class="space-y-4 text-left">
                    <!-- Department name -->
                    <label for="dept_name" class="block text-sm font-medium text-gray-700">Department Name</label>
                    <input type="text" id="dept_name" name="dept_name" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>

                    <!-- Department code, ex: IT0589 -->
                    <label for="dept_code" class="block text-sm font-medium text-gray-700">Department Code</label>
                    <input type="text" id="dept_code" name="dept_code" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>

                    <!-- Department head selection -->
                    <label for="dept_head" class="block text-sm font-medium text-gray-700">Department Head</label>
                    <select name="dept_head" id="dept_head" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">Select Department Head</option>
                        <?php echo $deptHeadRolesOptions; ?>
                    </select>
                    <!-- Hidden submit button -->
                    <button type="submit" style="display: none" id="submitFormBtn"></button>
                </form>
                `,
                showCancelButton: true,
                cancelButtonColor: "#d33",
                confirmButtonText: 'Submit',
                width: '500px',
                customClass: {
                    popup: 'swal-wide',
                },
                preConfirm: () => {
                    // Trigger the hidden submit button click
                    $('#submitFormBtn').click();
                }
            });
        });
    });
</script>


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
        
</style>
<body class="p-20 m-2 bg-gray-100">
    <button type="button" id="openFormBtn"
        class="relative px-5 py-3 overflow-hidden text-white bg-indigo-500 rounded shadow min-w-max hover:bg-opacity-90">
        Add Department
    </button>


    <table border="1" id="department_table" class="w-full bg-white rounded-lg shadow-lg display">
        <thead>
            <tr>
                <td>Department Name</td>
                <td>Department Code</td>
                <td>Department Head</td>
            </tr>
        </thead>
    
    <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['dept_name'];?></td>
            <td><?php echo $row['dept_code'];?></td>
            <td><?php echo $row['user_full_name'];?></td>
        </tr>

        <?php endwhile; ?>
    </tbody>
    </table>

</body>
</html>