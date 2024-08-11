<?php
include "../db_connection.php";

// ADD NEW BENEFIT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_benefit'])) {
    $ben_name = $_POST['ben_name'];

    $stmt = $conn->prepare("INSERT INTO benefits (ben_name, ben_status) VALUES (?, 'active')");
    $stmt->bind_param("s", $ben_name);

    if ($stmt->execute()) {
        header("Location: benefits.php"); // Redirect to prevent resubmission
        exit();
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
    <!-- TAILWIND CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Tailwind CSS file -->
    <link href="./output.css" rel="stylesheet">
    <!-- DataTable style CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS CDN -->
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
    <!-- DataTables Initialization Script -->
    <script>
        $(document).ready(function () {
            $('#benefits_table').DataTable();

            // Update status on change
            $('select[name="ben_status"]').on('change', function () {
                var ben_id = $(this).data('ben-id');
                var ben_status = $(this).val();

                $.ajax({
                    url: 'update_status.php',
                    type: 'POST',
                    data: { ben_id: ben_id, ben_status: ben_status },
                    success: function (response) {
                        alert("Benefit status updated successfully.");
                    },
                    error: function () {
                        alert("Error updating status.");
                    }
                });
            });
        });
    </script>
</head>
<body class="bg-gray-100 p-20 m-2">
    <br><br>
    <!-- BENEFITS FORM -->
    <h1>Benefits</h1>
    <form action="benefits.php" method="post">
        <label for="ben_name">Benefit Name:</label>
        <input type="text" id="ben_name" name="ben_name" class="border rounded px-2 py-1">
        <br><br>
        <input type="submit" name="add_benefit" value="Add Benefit" class="bg-blue-500 text-white px-4 py-2 rounded">
    </form>

    <h2>Benefits List</h2>
    <table border="1" id="benefits_table" class="display w-full bg-white rounded-lg shadow-lg">
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
                    echo "<td>
                            <select name='ben_status' data-ben-id='" . $row["ben_id"] . "' class='border rounded px-2 py-1'>
                                <option value='active'" . ($row['ben_status'] == 'active' ? ' selected' : '') . ">Active</option>
                                <option value='on hold'" . ($row['ben_status'] == 'on hold' ? ' selected' : '') . ">On Hold</option>
                                <option value='pending'" . ($row['ben_status'] == 'pending' ? ' selected' : '') . ">Pending</option>
                            </select>
                          </td>";
                    echo "<td>
                            <a href='benefits_list.php?ben_id=" . $row["ben_id"] . "' class='text-blue-500'>View List</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
<?php
$conn->close();
?>
