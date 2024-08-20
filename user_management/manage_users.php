<?php
include "../db_connection.php";

$successMessage = $errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_full_name = $_POST['user_full_name'] ?? '';
    $user_email = $_POST['user_email'] ?? '';
    $user_contacts = $_POST['user_contacts'] ?? '';
    $user_role = $_POST['user_role'] ?? '';

    if (!empty($user_full_name) && !empty($user_email) && !empty($user_contacts) && !empty($user_role)) {
        $stmt = $conn->prepare("INSERT INTO user_management (user_full_name, user_email, user_contacts, user_role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $user_full_name, $user_email, $user_contacts, $user_role);

        if ($stmt->execute()) {
            $successMessage = "New record created successfully.";
            // Redirect after successful submission to avoid resubmission on refresh
            header("Location: manage_users.php?success=1");
            exit();
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "All fields are required.";
    }
}

// Fetch to display table
$result = $conn->query("SELECT * FROM user_management");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>USER MANAGEMENT</h1>

    <form action="manage_users.php" method="POST">
        <!-- full name -->
        <label for="user_full_name">User Full Name</label>
        <input type="text" id="user_full_name" name="user_full_name">
        <!-- user email -->
        <label for="user_email">User Email</label>
        <input type="text" id="user_email" name="user_email">
        <!-- user contacts -->
        <label for="user_contacts">User Contacts</label>
        <input type="text" id="user_contacts" name="user_contacts">
        <!-- user role dropdornwn-->
        <label for="user_role">User Roles</label>
        <select name="user_role" id="user_role">
            <option disabled>Select Roles</option>
            <option value="HR">HR</option>
            <option value="Admin">Admin</option>
            <option value="Supervisor">Supervisor</option>
            <option value="Manager">Manager</option>
            <option value="Staff">Staff</option>
        </select>
        <button type="submit">Submit</button>
    </form>


    <table border="1" id="users_table">
        <thead>
            <tr>
                <td>Full name</td>
                <td>Email</td>
                <td>Contact Number</td>
                <td>Role</td>
            </tr>
        </thead>
    
    <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['user_full_name'];?></td>
            <td><?php echo $row['user_email'];?></td>
            <td><?php echo $row['user_contacts'];?></td>
            <td><?php echo $row['user_role'];?></td>
        </tr>

        <?php endwhile; ?>
    </tbody>
    </table>

</body>
</html>