<?php
include "../db_connection.php";
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['ben_list_id'])) {
    $ben_list_id = $_GET['ben_list_id'];

    // Fetch the existing data for the specific benefits list
    $stmt = $conn->prepare("SELECT * FROM benefits_lists WHERE ben_list_id = ?");
    $stmt->bind_param("i", $ben_list_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false]);
}

// Update the benefits list data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ben_list_id = $_POST['ben_list_id']; // Get the ID from the form
    $ben_list_range_s = $_POST['ben_list_range_s'];
    $ben_list_range_e = $_POST['ben_list_range_e'];
    $ben_employee_amount = $_POST['ben_employee_amount'];
    $ben_employer_amount = $_POST['ben_employer_amount'];

    $updateQuery = "UPDATE benefits_lists SET ben_list_range_s = ?, ben_list_range_e = ?, ben_employee_amount = ?, ben_employer_amount = ? WHERE ben_list_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("dddii", $ben_list_range_s, $ben_list_range_e, $ben_employee_amount, $ben_employer_amount, $ben_list_id);

    if ($updateStmt->execute()) {
        $_SESSION['message'] = "Benefits list updated successfully.";
        header("Location: benefits_list.php?ben_id=$ben_list_id");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update benefits list.";
    }

    $updateStmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<form action="edit_benefits_list.php?ben_list_id=<?php echo $_GET['ben_list_id']; ?>" id="benefitsForm" method="post" class="space-y-1 text-left">
        <input type="hidden" name="ben_list_id" value="<?php echo $_GET['ben_list_id']; ?>">
        <label for="ben_list_range_s" class="text-sm font-medium">Range Start:</label>
        <input type="number" id="ben_list_range_s" name="ben_list_range_s" step="0.01" class="w-full p-2 text-sm border rounded-md" value="<?php echo $data['ben_list_range_s']; ?>" required>
        <br><br>
        <label for="ben_list_range_e" class="text-sm font-medium">Range End:</label>
        <input type="number" id="ben_list_range_e" name="ben_list_range_e" step="0.01" class="w-full p-2 text-sm border rounded-md" value="<?php echo $data['ben_list_range_e']; ?>" required>
        <br><br>
        <label for="ben_employee_amount" class="text-sm font-medium">Employee Amount:</label>
        <input type="number" id="ben_employee_amount" name="ben_employee_amount" step="0.01" class="w-full p-2 text-sm border rounded-md" value="<?php echo $data['ben_employee_amount']; ?>" required>
        <br><br>
        <label for="ben_employer_amount" class="text-sm font-medium">Employer Amount:</label>
        <input type="number" id="ben_employer_amount" name="ben_employer_amount" step="0.01" class="w-full p-2 text-sm border rounded-md" value="<?php echo $data['ben_employer_amount']; ?>" required>
        <br><br>
        <button type="submit">Update</button>
    </form>
</body>
</html>