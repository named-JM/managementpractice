<?php
include "db_connection.php";

$ben_id = $_GET['ben_id'] ?? '';

// Function to check for overlapping ranges
function isRangeOverlap($conn, $ben_id, $start, $end) {
    $stmt = $conn->prepare("SELECT * FROM benefits_lists WHERE ben_id = ? AND ((ben_list_range_s <= ? AND ben_list_range_e >= ?) OR (ben_list_range_s <= ? AND ben_list_range_e >= ?))");
    $stmt->bind_param("idddd", $ben_id, $end, $end, $start, $start);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->num_rows > 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ben_list_range_s = $_POST['ben_list_range_s'] ?? '';
    $ben_list_range_e = $_POST['ben_list_range_e'] ?? '';
    $ben_employee_amount = $_POST['ben_employee_amount'] ?? '';
    $ben_employer_amount = $_POST['ben_employer_amount'] ?? '';

    // Debugging information
    echo "<br>ben_id: $ben_id<br>";
    echo "ben_list_range_s: $ben_list_range_s<br>";
    echo "ben_list_range_e: $ben_list_range_e<br>";
    echo "ben_employee_amount: $ben_employee_amount<br>";
    echo "ben_employer_amount: $ben_employer_amount<br>";

    if (!empty($ben_list_range_s) && !empty($ben_list_range_e) && !empty($ben_employee_amount) && !empty($ben_employer_amount)) {
        if (isRangeOverlap($conn, $ben_id, $ben_list_range_s, $ben_list_range_e)) {
            echo "Error: The range overlaps with an existing range.";
        } else {
            // Debugging statement to check if ben_id exists in benefits table
            $stmt_check = $conn->prepare("SELECT ben_id FROM benefits WHERE ben_id = ?");
            $stmt_check->bind_param("i", $ben_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows == 0) {
                echo "Error: ben_id does not exist in benefits table.";
            } else {
                $stmt = $conn->prepare("INSERT INTO benefits_lists (ben_id, ben_list_range_s, ben_list_range_e, ben_employee_amount, ben_employer_amount) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("idddd", $ben_id, $ben_list_range_s, $ben_list_range_e, $ben_employee_amount, $ben_employer_amount);

                if ($stmt->execute()) {
                    echo "New benefits list entry added successfully.";
                } else {
                    echo "Error: " . $stmt->error;
                }
                $stmt->close();
            }
            $stmt_check->close();
        }
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
        <input type="submit" value="Submit">
    </form>

    <h2>Benefits List for ben_id: <?php echo $ben_id; ?></h2>
    <table border="1">
        <tr>
            <th>Range Start</th>
            <th>Range End</th>
            <th>Employee Amount</th>
            <th>Employer Amount</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
    
            <td><?php echo $row['ben_list_range_s']; ?></td>
            <td><?php echo $row['ben_list_range_e']; ?></td>
            <td><?php echo $row['ben_employee_amount']; ?></td>
            <td><?php echo $row['ben_employer_amount']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
