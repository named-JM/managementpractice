<?php
include "../db_connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pos_id = $_POST['pos_id'];
    $ben_id = $_POST['ben_id'];

    // Prepare and execute the deletion query
    $stmt = $conn->prepare("DELETE FROM position_benefits WHERE pos_ref = ? AND ben_id = ?");
    $stmt->bind_param("ii", $pos_id, $ben_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to the position benefits page
    header("Location: position_benefits.php?pos_id=$pos_id");
    exit;
}

$conn->close();
?>
