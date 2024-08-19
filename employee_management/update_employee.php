<?php
include "../db_connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emp_company_num = $_POST['emp_company_num'];
    $emp_fname = $_POST['emp_fname'];
    $emp_mname = $_POST['emp_mname'];
    $emp_lname = $_POST['emp_lname'];
    $emp_position = $_POST['emp_position'];
    $emp_email = $_POST['emp_email'];
    $emp_number = $_POST['emp_number'];
    $emp_zip = $_POST['emp_zip'];
    $employ_manager = $_POST['employ_manager'];
    $employ_dept = $_POST['employ_dept'];
    $emp_status = $_POST['emp_status'];

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE employee_table SET emp_fname=?, emp_mname=?, emp_lname=?, emp_position=?, emp_email=?, emp_number=?, emp_zip=?, employ_manager=?, employ_dept=?, emp_status=? WHERE emp_company_num=?");
    $stmt->bind_param("sssississss", $emp_fname, $emp_mname, $emp_lname, $emp_position, $emp_email, $emp_number, $emp_zip, $employ_manager, $employ_dept, $emp_status, $emp_company_num);

    if ($stmt->execute()) {
        echo "Record updated successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the main page (or wherever you'd like)
    header("Location: add_employee.php");
    exit();
}
?>
