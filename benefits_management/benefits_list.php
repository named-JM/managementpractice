<?php
include "../db_connection.php";

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

// Handle AJAX request for range overlap check
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check_overlap'])) {
    $ben_list_range_s = $_POST['ben_list_range_s'] ?? '';
    $ben_list_range_e = $_POST['ben_list_range_e'] ?? '';

    if (!empty($ben_list_range_s) && !empty($ben_list_range_e)) {
        $overlap = isRangeOverlap($conn, $ben_id, $ben_list_range_s, $ben_list_range_e);
        echo json_encode(['overlap' => $overlap]);
    } else {
        echo json_encode(['overlap' => false]);
    }
    exit;
}

// Existing form handling code
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['check_overlap'])) {
    $ben_list_range_s = $_POST['ben_list_range_s'] ?? '';
    $ben_list_range_e = $_POST['ben_list_range_e'] ?? '';
    $ben_employee_amount = $_POST['ben_employee_amount'] ?? '';
    $ben_employer_amount = $_POST['ben_employer_amount'] ?? '';

    if (!empty($ben_list_range_s) && !empty($ben_list_range_e) && !empty($ben_employee_amount) && !empty($ben_employer_amount)) {
        if (isRangeOverlap($conn, $ben_id, $ben_list_range_s, $ben_list_range_e)) {
            // echo "Error: The range overlaps with an existing range.";
        } else {
            // Check if ben_id exists in the table
            $stmt_check = $conn->prepare("SELECT ben_id FROM benefits WHERE ben_id = ?");
            $stmt_check->bind_param("i", $ben_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            // Insert new benefits list entry
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
        // echo "All fields are required.";
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
    <!-- TAILWING CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- tialiwind css file -->
    <link href="./output.css" rel="stylesheet">
    <!-- datatable style cdn -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">
    <!-- font awesome icons -->
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
    
    $('#openFormBtn').on('click', function () {
        Swal.fire({
            title: 'Benefits Form',
            html: `
                <form id="benefitsForm" method="post" class="space-y-1 text-left">
                    <label for="ben_list_range_s" class="text-sm font-medium">Range Start:</label>
                    <input type="number" id="ben_list_range_s" name="ben_list_range_s" step="0.01" class="w-full p-2 text-sm border rounded-md" required>
                    <br><br>
                    <label for="ben_list_range_e" class="text-sm font-medium">Range End:</label>
                    <input type="number" id="ben_list_range_e" name="ben_list_range_e" step="0.01" class="w-full p-2 text-sm border rounded-md" required>
                    <br><br>
                    <label for="ben_employee_amount" class="text-sm font-medium">Employee Amount:</label>
                    <input type="number" id="ben_employee_amount" name="ben_employee_amount" step="0.01" class="w-full p-2 text-sm border rounded-md" required>
                    <br><br>
                    <label for="ben_employer_amount" class="text-sm font-medium">Employer Amount:</label>
                    <input type="number" id="ben_employer_amount" name="ben_employer_amount" step="0.01" class="w-full p-2 text-sm border rounded-md" required>
                    <br><br>
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
                const benListRangeS = document.getElementById('ben_list_range_s').value;
                const benListRangeE = document.getElementById('ben_list_range_e').value;
                const benEmployeeAmount = document.getElementById('ben_employee_amount').value;
                const benEmployerAmount = document.getElementById('ben_employer_amount').value;

                if (!benListRangeS || !benListRangeE || !benEmployeeAmount || !benEmployerAmount) {
                    Swal.showValidationMessage('All fields are required.');
                    return false; // Prevent the form from submitting
                }

                // Check for overlapping ranges
                return $.ajax({
                    type: 'POST',
                    url: 'benefits_list.php?ben_id=<?php echo $ben_id; ?>',
                    data: {
                        check_overlap: true,
                        ben_list_range_s: benListRangeS,
                        ben_list_range_e: benListRangeE
                    },
                    dataType: 'json'
                }).then(response => {
                    if (response.overlap) {
                        Swal.showValidationMessage('The range overlaps with an existing range.');
                        return false; // Prevent the form from submitting
                    }
                    // Proceed with form submission
                    $('#benefitsForm').append('<input type="hidden" name="submitted" value="true">'); // Add a hidden field to indicate form submission
                    $('#benefitsForm').submit();
                }).catch(() => {
                    Swal.showValidationMessage('An error occurred while checking for overlapping ranges.');
                    return false; // Prevent the form from submitting
                });
            }
        });
    });
});
</script>

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
</head>
<body class="p-20 m-2 bg-gray-100">
    <br><br>

    <button type="button" id="openFormBtn"
        class="relative px-5 py-3 overflow-hidden text-white bg-indigo-500 rounded shadow min-w-max hover:bg-opacity-90">
        Add Benefits Range
    </button>
    

    

    <h2>Benefits List for ben_id: <?php echo $ben_id; ?></h2>
    <table border="1" id="benefits_table" class="w-full bg-white rounded-lg shadow-lg display">
        <thead>
        <tr>
            <th>Range Start</th>
            <th>Range End</th>
            <th>Employee Amount</th>
            <th>Employer Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
    
            <td><?php echo $row['ben_list_range_s']; ?></td>
            <td><?php echo $row['ben_list_range_e']; ?></td>
            <td><?php echo $row['ben_employee_amount']; ?></td>
            <td><?php echo $row['ben_employer_amount']; ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
