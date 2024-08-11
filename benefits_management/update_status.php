<?php
// update_status.php
include "../db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ben_id']) && isset($_POST['ben_status'])) {
    $ben_id = $_POST['ben_id'];
    $ben_status = $_POST['ben_status'];

    $stmt = $conn->prepare("UPDATE benefits SET ben_status = ? WHERE ben_id = ?");
    $stmt->bind_param("si", $ben_status, $ben_id);

    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();

?>
