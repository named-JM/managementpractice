<?php
include "db_connection.php";

// Fetch existing records
$sql = "SELECT * FROM employment";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employment Records</title>
</head>
<body>
    <h1>Employment Records</h1>
    
    <a href="employment.php">Go back to form</a>
    <table border="1">
        <thead>
            <tr>
                <th>Contractual Name</th>
                <th>Compensation Type</th>
                <th>Terms</th>
                <th>Duration</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["contractual_name"] . "</td>";
                    echo "<td>" . $row["employ_compensation"] . "</td>";
                    echo "<td>" . $row["employ_terms"] . "</td>";
                    echo "<td>" . $row["employ_duration"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>


</body>
</html>

<?php
$conn->close();
?>
