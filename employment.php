<?php
include "db_connection.php";

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $compensation = $_POST['compensation'] ?? '';
    $terms = $_POST['terms'] ?? '';
    $duration = $_POST['duration'] ?? '';


    if (!empty($compensation) && !empty($terms) && !empty($duration)) {
        $stmt = $conn->prepare("INSERT INTO employment (employ_compensation, employ_terms, employ_duration, employ_status) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $compensation, $terms, $duration);

        if ($stmt->execute()) {
            $successMessage = "New record created successfully.";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employment Contract Form</title>
</head>
<body>
    <h1>Employment Contract Form</h1>
    <form action="employment.php" method="post">
        <label for="compensation">Compensation Type:</label>
        <select id="compensation" name="compensation">
            <option value="">Select Compensation</option>
            <option value="contractual">Contractual</option>
            <option value="fixed rate">Fixed Rate</option>
            <option value="per day">Per Day</option>
            <option value="project-based">Project-Based</option>
            <option value="commission-based">Commission Based</option>
        </select>

        <br><br>

        <label for="terms">Terms:</label>
        <select id="terms" name="terms">
            <option value="">Select Term</option>
            <option value="time">Time</option>
            <option value="day">Day</option>
        </select>

        <br><br>

        <label id="durationLabel" for="duration">Duration:</label>
        <input type="number" id="duration" name="duration">

        <br><br>

        <input type="submit" value="Submit">
    </form>

    <br>

    <?php if ($successMessage): ?>
        <p><?php echo $successMessage; ?></p>
    <?php elseif ($errorMessage): ?>
        <p><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <br>
    <a href="display.php">View Records</a>
    <a href="benefits.php">Benefits</a>

</body>
</html>

<?php
$conn->close();
?>
