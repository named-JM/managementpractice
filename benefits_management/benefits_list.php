<?php
include "../db_connection.php";
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// GET THE BEN_ID FROM THE BENEFITS PAGE AFTER CLICKING THE VIEW
// FOR EXAMPLE CLICKING VIEW TO THE COLUMN OF SSS AND WILL GET THE BEN_ID OF IT 
// TO PROCEED TO ANOTHER PAGE TO MODIFY WITH THAT VALIE
$ben_id = $_GET['ben_id'] ?? '';
$ben_name = '';

// CHECKING FOR OVERLAPPING IN THE DATA TABLE RANGES!!!
// THIS IS TO CHECK AND ENSURES THR START OR END OF THE NEW RANGE WILL OVERLAP
// WITHIN THE EXISTING RANGE OR THE EXISITNG RANGE STARTS WITHIN NEW RANGE
function isRangeOverlap($conn, $ben_id, $start, $end, $current_id = null) {
    $query = "SELECT * FROM benefits_lists WHERE ben_id = ? AND ((ben_list_range_s <= ? AND ben_list_range_e >= ?) OR (ben_list_range_s >= ? AND ben_list_range_s <= ?))";
    
    // THIS IS WHERE TO CONDITION IN EDITING. IF CLICKING THE RANGE TO EDIT IT WILL PROVIDED AN CURRENT OF ITS ID
    // THEN IT WILL EXCLUDE THAT BENEFIT RANGE TO THE CHECKING 
    if ($current_id !== null) {
        $query .= " AND ben_list_id != ?";
    }
    
    $stmt = $conn->prepare($query);
    // THIS IS WHERE IF USER EDIT THE RANGES IT WILL EXLCUDE THE ITS CURRENT ID FROM THE COMPARING CHECKING
    if ($current_id !== null) {
        // THE FIRST $END AND $START CHINCHECK YUNG BAGONG RANGE IF IT OVERLAPS SA EXISTING RANGES PALABAS OR PALOOB 
        // EX:  EXISTING RANGE IS 10-20 AND U GONNA ADD 15-25 RANGE. 
        //  so ben_list_range_s (current exisiting range start) <= $end which is 10 <=25 TRUE
        //  and then ben list range e (current range end) <= $start which is 10<=15 TRUE
        // THE LAST $START AND $END CHINICHECK NYA IF UNG EXISITNG RANGE E NAGSTARTS WITHIN THE NEW RANGES
        // so ben_list_range_s (current exisiting range start) >= $start which is 10 >= 15  FALSE (tho nagfalse sya nacheck nya parin ung iba na overlaps pa din)
        // so ben list range s (current exisitng range start) <= $enf which 10<25 TRUE
        $stmt->bind_param("iddddi", $ben_id, $end, $start, $start, $end, $current_id);
    }
    // ELSE IT WILL PROCEED TO CHECK OVERALL
    else {
        $stmt->bind_param("idddd", $ben_id, $end, $start, $start, $end);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->num_rows > 0;
}


// THIS HERE HANDLES THE UPDATE REQUEST !
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['edit_id'] ?? '';
    $ben_list_range_s = $_POST['ben_list_range_s'] ?? '';
    $ben_list_range_e = $_POST['ben_list_range_e'] ?? '';
    $ben_employee_amount = $_POST['ben_employee_amount'] ?? '';
    $ben_employer_amount = $_POST['ben_employer_amount'] ?? '';

    // CHECKING IF THE USER FILLED OUT THE FIELDS
    if (!empty($id) && !empty($ben_list_range_s) && !empty($ben_list_range_e) && !empty($ben_employee_amount) && !empty($ben_employer_amount)) {
        // THIS IS ANOTHER CONDITION THAT IT SHOULD NOT PROCEED IF START IS GREATER THAN THE END
        if ($ben_list_range_s > $ben_list_range_e) {
            echo json_encode(['error' => 'The start range cannot be greater than the end range.']);
            exit;
        }
        // THIS IS CONDITION OF EDITING A CURRENT RANGE WHERE THE CHECKING OF OEVRLAP WITH ANY EXISITN GRANGES EXCLUSING THE CURRENT RANGE
        if (isRangeOverlap($conn, $ben_id, $ben_list_range_s, $ben_list_range_e, $id)) {
            echo json_encode(['overlap' => true]);
            exit;
        } else {
            $stmt = $conn->prepare("UPDATE benefits_lists SET ben_list_range_s = ?, ben_list_range_e = ?, ben_employee_amount = ?, ben_employer_amount = ? WHERE ben_list_id = ?");
            $stmt->bind_param("ddddi", $ben_list_range_s, $ben_list_range_e, $ben_employee_amount, $ben_employer_amount, $id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => $stmt->error]);
            }
            $stmt->close();
        }
    }
    exit;
}

// Handle AJAX request for range overlap check
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check_overlap'])) {
    $ben_list_range_s = $_POST['ben_list_range_s'] ?? '';
    $ben_list_range_e = $_POST['ben_list_range_e'] ?? '';
    $edit_id = $_POST['edit_id'] ?? null;

    if (!empty($ben_list_range_s) && !empty($ben_list_range_e)) {
        $overlap = isRangeOverlap($conn, $ben_id, $ben_list_range_s, $ben_list_range_e, $edit_id);
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
        if ($ben_list_range_s > $ben_list_range_e) {
            echo json_encode(['error' => 'The start range cannot be greater than the end range.']);
            exit;
        }
        
        
        if (isRangeOverlap($conn, $ben_id, $ben_list_range_s, $ben_list_range_e)) {
            // echo "Error: The range overlaps with an existing range.";
        } else {
            //CHECKING IF BEN ID EXIST IN THE TABLE
            $stmt_check = $conn->prepare("SELECT ben_id FROM benefits WHERE ben_id = ?");
            $stmt_check->bind_param("i", $ben_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            // INSERTING NEW BENEFIT LIST!!!!!!!!!!BUT DISPLAY IF THE BEN ID DOES NOT EXIST NO MORE
            if ($result_check->num_rows == 0) {
                echo "Error: ben_id does not exist in benefits table.";
            } else {
                // INSERTING/ADDING BENEFITS LIST
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



// Fetch the benefit name using the ben_id
if (!empty($ben_id)) {
    $stmt_name = $conn->prepare("SELECT ben_name FROM benefits WHERE ben_id = ?");
    $stmt_name->bind_param("i", $ben_id);
    $stmt_name->execute();
    $result_name = $stmt_name->get_result();
    
    if ($result_name->num_rows > 0) {
        $row_name = $result_name->fetch_assoc();
        $ben_name = $row_name['ben_name'];
    }
    $stmt_name->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benefits List</title>
    <!-- TAILWIND CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Tailwind CSS file -->
    <link href="./output.css" rel="stylesheet">
    <!-- DataTable style CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">
    <!-- Font Awesome icons -->
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

            // OLD BUTTON EFFECT
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
            
            // THIS IS WHERE ADDING RANGES SWEETALERT FORM 
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
                        </form>`,
                    focusConfirm: false,
                    preConfirm: () => {
                        let benListRangeS = parseFloat(document.getElementById('ben_list_range_s').value);
                        let benListRangeE = parseFloat(document.getElementById('ben_list_range_e').value);

                        if (benListRangeS > benListRangeE) {
                            Swal.showValidationMessage('Error: The start range cannot be greater than the end range.');
                            return false;
                        }
                        return $.ajax({
                            type: 'POST',
                            url: 'benefits_list.php?ben_id=<?php echo $ben_id; ?>',
                            data: {
                                check_overlap: true,
                                ben_list_range_s: benListRangeS,
                                ben_list_range_e: benListRangeE,
                                edit_id: null
                            },
                            dataType: 'json'
                        })
                        .then((response) => {
                            if (response.overlap) {
                                Swal.showValidationMessage('Error: The range overlaps with an existing range.');
                                return false;
                            }
                            return [benListRangeS, benListRangeE];
                        })
                        .catch(error => {
                            Swal.showValidationMessage('Error: Could not validate the range.');
                            return false;
                        });
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#benefitsForm').submit();
                    }
                });
            });

            // THIS IS WHERE EDIT SWEETALERT FORM!!!
            $('.editBtn').on('click', function () {
                const row = $(this).closest('tr');
                const id = row.find('.ben-list-id').text();
                const benListRangeS = parseFloat(row.find('.ben-list-range-s').text());
                const benListRangeE = parseFloat(row.find('.ben-list-range-e').text());
                const benEmployeeAmount = parseFloat(row.find('.ben-employee-amount').text());
                const benEmployerAmount = parseFloat(row.find('.ben-employer-amount').text());

                Swal.fire({
                    title: 'Edit Benefits Entry',
                    html: `
                        <form id="editBenefitsForm" method="post" class="space-y-1 text-left">
                            <input type="hidden" id="edit_id" name="edit_id" value="${id}">
                            <label for="edit_ben_list_range_s" class="text-sm font-medium">Range Start:</label>
                            <input type="number" id="edit_ben_list_range_s" name="ben_list_range_s" step="0.01" class="w-full p-2 text-sm border rounded-md" value="${benListRangeS}" required>
                            <br><br>
                            <label for="edit_ben_list_range_e" class="text-sm font-medium">Range End:</label>
                            <input type="number" id="edit_ben_list_range_e" name="ben_list_range_e" step="0.01" class="w-full p-2 text-sm border rounded-md" value="${benListRangeE}" required>
                            <br><br>
                            <label for="edit_ben_employee_amount" class="text-sm font-medium">Employee Amount:</label>
                            <input type="number" id="edit_ben_employee_amount" name="ben_employee_amount" step="0.01" class="w-full p-2 text-sm border rounded-md" value="${benEmployeeAmount}" required>
                            <br><br>
                            <label for="edit_ben_employer_amount" class="text-sm font-medium">Employer Amount:</label>
                            <input type="number" id="edit_ben_employer_amount" name="ben_employer_amount" step="0.01" class="w-full p-2 text-sm border rounded-md" value="${benEmployerAmount}" required>
                        </form>`,
                    focusConfirm: false,
                    preConfirm: () => {
                        let editBenListRangeS = parseFloat(document.getElementById('edit_ben_list_range_s').value);
                        let editBenListRangeE = parseFloat(document.getElementById('edit_ben_list_range_e').value);

                        if (editBenListRangeS > editBenListRangeE) {
                            Swal.showValidationMessage('Error: The start range cannot be greater than the end range.');
                            return false;
                        }
                        return $.ajax({
                            type: 'POST',
                            url: 'benefits_list.php?ben_id=<?php echo $ben_id; ?>',
                            data: {
                                check_overlap: true,
                                edit_id: id,
                                ben_list_range_s: editBenListRangeS,
                                ben_list_range_e: editBenListRangeE
                            },
                            dataType: 'json'
                        })
                        .then((response) => {
                            if (response.overlap) {
                                Swal.showValidationMessage('Error: The range overlaps with an existing range.');
                                return false;
                            }
                            return true;
                        })
                        .catch(error => {
                            Swal.showValidationMessage('Error: Could not validate the range.');
                            return false;
                        });
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Save',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit the edit form using AJAX
                        let editData = {
                            edit_id: id,
                            ben_list_range_s: parseFloat(document.getElementById('edit_ben_list_range_s').value),
                            ben_list_range_e: parseFloat(document.getElementById('edit_ben_list_range_e').value),
                            ben_employee_amount: parseFloat(document.getElementById('edit_ben_employee_amount').value),
                            ben_employer_amount: parseFloat(document.getElementById('edit_ben_employer_amount').value),
                            update: true
                        };

                        $.ajax({
                            type: 'POST',
                            url: 'benefits_list.php?ben_id=<?php echo $ben_id; ?>',
                            data: editData,
                            dataType: 'json',
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Saved',
                                        text: 'The benefits entry has been updated.',
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.error || 'There was an error updating the entry.',
                                    });
                                }
                            },
                            error: function (xhr, status, error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while updating the entry.',
                                });
                            }
                        });
                    }
                });
            });

        });
    </script>

</head>
<body class="text-gray-800 bg-gray-100">

<div class="container p-4 mx-auto mt-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold">Benefits List for Benefit ID: <?php echo htmlspecialchars($ben_name); ?></h2>
        <button id="openFormBtn" class="px-4 py-2 text-white bg-blue-500 rounded-lg shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">Add Benefits Range</button>
    </div>

    <!-- HERE I THE BENEFIT RANGE TABLE DISPLAY! -->
    <table id="benefits_table" class="min-w-full bg-white border border-gray-200 rounded-md">
        <thead>
            <tr>
                <th class="px-4 py-2 border-b">ID</th>
                <th class="px-4 py-2 border-b">Range Start</th>
                <th class="px-4 py-2 border-b">Range End</th>
                <th class="px-4 py-2 border-b">Employee Amount</th>
                <th class="px-4 py-2 border-b">Employer Amount</th>
                <th class="px-4 py-2 border-b">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="px-4 py-2 border-b ben-list-id"><?php echo $row['ben_list_id']; ?></td>
                    <td class="px-4 py-2 border-b ben-list-range-s"><?php echo $row['ben_list_range_s']; ?></td>
                    <td class="px-4 py-2 border-b ben-list-range-e"><?php echo $row['ben_list_range_e']; ?></td>
                    <td class="px-4 py-2 border-b ben-employee-amount"><?php echo $row['ben_employee_amount']; ?></td>
                    <td class="px-4 py-2 border-b ben-employer-amount"><?php echo $row['ben_employer_amount']; ?></td>
                    <td class="px-4 py-2 border-b">
                        <button class="px-2 py-1 text-white bg-yellow-500 rounded editBtn hover:bg-yellow-600">Edit</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
