<?php
include "db_connection.php";

// Add new benefit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ben_name = $_POST['ben_name'] ?? '';
    $ben_status = $_POST['ben_status'] ?? '';

    if (!empty($ben_name) && !empty($ben_status)) {
        $stmt = $conn->prepare("INSERT INTO benefits (ben_name, ben_status) VALUES (?, ?)");
        $stmt->bind_param("ss", $ben_name, $ben_status);

        if ($stmt->execute()) {
            echo "New benefit added successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "All fields are required.";
    }
}

// Fetch benefits
$sql = "SELECT * FROM benefits";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benefits</title>
</head>
<body>
    <h1>Benefits</h1>
    <form action="benefits.php" method="post">
        <label for="ben_name">Benefit Name:</label>
        <input type="text" id="ben_name" name="ben_name">
        <br><br>
        <label for="ben_status">Status:</label>
        <select id="ben_status" name="ben_status">
            <option value="">Select Status</option>
            <option value="active">Active</option>
            <option value="on hold">On Hold</option>
            <option value="pending">Pending</option>
        </select>
        <br><br>
        <input type="submit" value="Add Benefit">
    </form>

    <h2>Benefits List</h2>
    <table border="1">
        <thead>
            <tr>
                <!-- <th>ID</th> -->
                <th>Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    // echo "<td>" . $row["ben_id"] . "</td>";
                    echo "<td>" . $row["ben_name"] . "</td>";
                    echo "<td>" . $row["ben_status"] . "</td>";
                    echo "<td><a href='benefits_list.php'>View List</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <a href="employment.php">back to employment</a>
</body>
</html>

<?php
$conn->close();
?>
