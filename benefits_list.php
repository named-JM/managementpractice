<?php
include "db_connection.php";

$ben_id = $_GET['ben_id'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ben_list_range_s = $_POST['ben_list_range_s'] ?? '';
    $ben_list_range_e = $_POST['ben_list_range_e'] ?? '';
    $ben_employee_amount = $_POST['ben_employee_amount'] ?? '';
    $ben_employer_amount = $_POST['ben_employer_amount'] ?? '';
    $ben_list_status = $_POST['ben_list_status'] ?? '';

    if (!empty($ben_list_range_s) && !empty($ben_list_range_e) && !empty($ben_employee_amount) && !empty($ben_employer_amount) && !empty($ben_list_status)) {
        $stmt = $conn->prepare("INSERT INTO benefits_lists (ben_id, ben_list_range_s, ben_list_range_e, ben_employee_amount, ben_employer_amount, ben_list_status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idddds", $ben_id, $ben_list_range_s, $ben_list_range_e, $ben_employee_amount, $ben_employer_amount, $ben_list_status);

        if ($stmt->execute()) {
            echo "New benefits list entry added successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "All fields are required.";
    }
}

// Fetch benefits list for specific ben_id
$sql = "SELECT * FROM benefits_lists WHERE ben_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ben_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benefits List</title>
</head>
<body>
    <h1>Benefits List</h1>
    <form action="benefits_list.php?ben_id=<?php echo $ben_id; ?>" method="post">
        <label for="ben_list_range_s">Range Start:</label>
        <input type="number" id="ben_list_range_s" name="ben_list_range_s" step="0.01">
        <br><br>
        <label for="ben_list_range_e">Range End:</label>
        <input type="number" id="ben_list_range_e" name="ben_list_range_e" step="0.01">
        <br><br>
        <label for="ben_employee_amount">Employee Amount:</label>
        <input type="number" id="ben_employee_amount" name="ben_employee_amount" step="0.01">
        <br><br>
        <label for="ben_employer_amount">Employer Amount:</label>
        <input type="number" id="ben_employer_amount" name="ben_employer_amount" step="0.01">
        <br><br>
        <label for="ben_list_status">Status:</label>
        <select id="ben_list_status" name="ben_list_status">
            <option value="">Select Status</option>
            <option value="active">Active</option>
            <option value="on hold">On Hold</option>
            <option value="pending">Pending</option>
        </select>
        <br><br>
        <input type="submit" value="Add to List">
    </form>

    <h2>Benefits List Records</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Range Start</th>
                <th>Range End</th>
                <th>Employee Amount</th>
                <th>Employer Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["ben_list_id"] . "</td>";
                    echo "<td>" . $row["ben_list_range_s"] . "</td>";
                    echo "<td>" . $row["ben_list_range_e"] . "</td>";
                    echo "<td>" . $row["ben_employee_amount"] . "</td>";
                    echo "<td>" . $row["ben_employer_amount"] . "</td>";
                    echo "<td>" . $row["ben_list_status"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <br><br>
    <a href="benefits.php">Back to Benefits</a>
</body>
</html>

<?php
$conn->close();
?>