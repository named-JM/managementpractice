<?php
include "../db_connection.php";

// Update benefit status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $ben_id = $_POST['ben_id'];
    $ben_status = $_POST['ben_status'];

    $stmt = $conn->prepare("UPDATE benefits SET ben_status = ? WHERE ben_id = ?");
    $stmt->bind_param("si", $ben_status, $ben_id);

    if ($stmt->execute()) {
        echo "Benefit status updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
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
    <style>
        .popup {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            border: 1px solid #ccc;
            padding: 20px;
            background: #fff;
            z-index: 1000;
        }
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
</head>
<body>
    <h1>Benefits</h1>
    <form action="benefits.php" method="post">
        <label for="ben_name">Benefit Name:</label>
        <input type="text" id="ben_name" name="ben_name">
        <br><br>
        <input type="submit" value="Add Benefit">
    </form>

    <h2>Benefits List</h2>
    <table border="1">
        <thead>
            <tr>
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
                    echo "<td>" . $row["ben_name"] . "</td>";
                    echo "<td>" . $row["ben_status"] . "</td>";
                    echo "<td>
                    <button onclick='openPopup(" . $row["ben_id"] . ")'>Edit Status</button>
                    <a href='benefits_list.php?ben_id=" . $row["ben_id"] . "'>View List</a>
                    </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <a href="../employment.php">back to employment</a>

    <!-- Popup Overlay -->
    <div id="popup-overlay" class="popup-overlay"></div>

    <!-- Popup Form -->
    <div id="popup" class="popup">
        <form id="updateForm" action="benefits.php" method="post">
            <input type="hidden" id="ben_id" name="ben_id">
            <label for="ben_status">Status:</label>
            <select id="ben_status" name="ben_status">
                <option value="active">Active</option>
                <option value="on hold">On Hold</option>
                <option value="pending">Pending</option>
            </select>
            <br><br>
            <input type="submit" name="update_status" value="Update Status">
            <button type="button" onclick="closePopup()">Cancel</button>
        </form>
    </div>

    <script>
        function openPopup(benId) {
            document.getElementById('ben_id').value = benId;
            document.getElementById('popup-overlay').style.display = 'block';
            document.getElementById('popup').style.display = 'block';
        }

        function closePopup() {
            document.getElementById('popup-overlay').style.display = 'none';
            document.getElementById('popup').style.display = 'none';
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
