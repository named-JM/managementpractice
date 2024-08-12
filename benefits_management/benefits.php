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
    <!-- FONT SAWESOME ICONS CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- TAILWIND CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Tailwind CSS file -->
    <link href="./output.css" rel="stylesheet">
    <!-- DataTable style CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- SWEETALERT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
<style>
    .swal2-popup {
            width: 500px !important;
            padding: 20px !important;
            text-align: left;
        }
        .swal2-content {
            text-align: left !important;
        }
        .swal2-input {
            width: 100% !important;
            margin-bottom: 15px;
        }
        .swal2-select {
            width: 100% !important;
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        
    span.ripple {
        position: absolute;
        border-radius: 50%;
        transform: scale(0);
        animation: ripple 600ms linear;
        background-color: rgba(255, 255, 255, 0.7);
        }
        @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
        }
</style>
<body class="p-20 m-2 bg-gray-100">
    <br><br>
    <button type="button" id="openFormBtn" class="relative px-5 py-3 overflow-hidden text-white bg-indigo-500 rounded shadow min-w-max hover:bg-opacity-90">
        Add Benefits
    </button>
    <script>
        // BUTTON CLICK EFFECT
        function rippleEffect(event) {
                const btn = event.currentTarget;

                const circle = document.createElement("span");
                const diameter = Math.max(btn.clientWidth, btn.clientHeight);
                const radius = diameter / 2;

                circle.style.width = circle.style.height = `${diameter}px`;
                circle.style.left = `${event.clientX - (btn.offsetLeft + radius)}px`;
                circle.style.top = `${event.clientY - (btn.offsetTop + radius)}px`;
                circle.classList.add("ripple");

                const ripple = btn.getElementsByClassName("ripple")[0];

                if (ripple) {
                    ripple.remove();
                }
                btn.appendChild(circle);
            }
            const btn = document.getElementById("openFormBtn");
            btn.addEventListener("click", rippleEffect);
            
        // SWEETALERT FORM SCRIPT
        document.getElementById('openFormBtn').addEventListener('click', function() {
            Swal.fire({
            title: 'Benefits Form',
            html: `
            <!-- BENEFITS FORM -->
                <form id="benefitsForm" action="benefits.php" method="post">
                    <label for="ben_name" class="text-left">Benefit Name: <span class="text-red-500">*</span></label>
                    <input type="text" id="ben_name" name="ben_name" class="w-full py-1 pl-0 text-left border rounded" required>

                    <br><br>
                    <input type="hidden" name="add_benefit" value="1">
                </form>

            `,
            showCancelButton: true,
                cancelButtonColor: "#d33",
                confirmButtonText: 'Submit',
                width: '400px',
                customClass: {
                    popup: 'swal-wide', // Additional custom class if needed
                },
                preConfirm: () => {
                    // Trigger form submission
                    document.getElementById('benefitsForm').submit();
                }
            });
        });
    </script>
    

    <table border="1" id="benefits_table" class="w-full bg-white rounded-lg shadow-lg display">
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
                    echo "<td>" . htmlspecialchars($row["ben_name"]) . "</td>";
                    echo "<td>

                            <select name='ben_status' data-ben-id='" . $row["ben_id"] . "' class='px-2 py-1 border rounded'>
                            <option value='" . htmlspecialchars($row['ben_status']) . " ' selected>" . ucfirst($row['ben_status']) . "</option>//i want the value here of the current updated status of the ben name
                            <option value='active'" . ($row['ben_status'] == 'active' ? ' selected' : '') . ">Active</option>
                                <option value='on hold'" . ($row['ben_status'] == 'on hold' ? ' selected' : '') . ">On Hold</option>
                                <option value='pending'" . ($row['ben_status'] == 'pending' ? ' selected' : '') . ">Pending</option>
                            </select>
                        
                        </td>";
                    echo "<td>
                        <a href='benefits_list.php?ben_id=" . $row["ben_id"] . "' class='inline-flex items-center px-4 py-1 text-white bg-blue-500 rounded hover:bg-blue-700'><i class='mr-3 fa-solid fa-file-pen'></i>View</a>
                        
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
