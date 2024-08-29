<?php
include "../db_connection.php";

// i think this is the fetching for edit employee form
if (isset($_POST['emp_company_num'])) {
    $emp_company_num = $_POST['emp_company_num'];

    $stmt = $conn->prepare("
    SELECT e.*, p.pos_name, um.user_full_name as manager_name
        FROM employee_table e
        LEFT JOIN position p ON e.emp_position = p.pos_id
        LEFT JOIN user_management um ON e.employ_manager = um.user_id
        WHERE e.emp_company_num = ?
    ");
    $stmt->bind_param("s", $emp_company_num);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();

    echo json_encode($employee);
    $stmt->close();
}

$conn->close();
?>