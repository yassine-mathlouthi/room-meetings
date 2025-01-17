<?php
include 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

include 'views/header.php';
include 'includes/config.php';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search parameters
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$search_condition = $search ? "AND (rooms.name LIKE '%$search%' OR rooms.location LIKE '%$search%' OR bookings.date LIKE '%$search%')" : '';

// Base query depending on user role
if (is_admin()) {
    $query = "SELECT bookings.id, rooms.id AS room_id, rooms.name AS room_name, start_time, end_time, rooms.location, 
              bookings.date, bookings.time_slot, users.email AS user_email 
              FROM bookings 
              INNER JOIN rooms ON bookings.room_id = rooms.id
              INNER JOIN users ON bookings.user_id = users.id
              WHERE 1=1 $search_condition";
} else {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT bookings.id, rooms.id AS room_id, rooms.name AS room_name, rooms.location, bookings.date,
              start_time, end_time, bookings.time_slot 
              FROM bookings 
              INNER JOIN rooms ON bookings.room_id = rooms.id
              WHERE bookings.user_id = '$user_id' $search_condition";
}

if (isset($_POST['update_booking'])) {
    $booking_id = $_POST['booking_id'];
    $room_id = $_POST['room_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Check if the room is already booked (excluding the current booking)
    $check_query = "SELECT * FROM bookings 
                    WHERE room_id = ? 
                    AND date = ? 
                    AND id != ? 
                    AND ((start_time >= ? AND start_time < ?) 
                    OR (end_time > ? AND end_time <= ?))";

    $stmt = $conn->prepare($check_query);
    $stmt->bind_param(
        "isissss",
        $room_id,
        $date,
        $booking_id,
        $start_time,
        $end_time,
        $start_time,
        $end_time
    );
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error_message'] = "This room is already booked for the selected time range.";
        header("Location: dashboard.php");
        exit();
    }

    // If no conflicts, proceed with update
    $update_query = "UPDATE bookings SET room_id = ?, date = ?, start_time = ?, end_time = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("isssi", $room_id, $date, $start_time, $end_time, $booking_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Booking updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating booking. Please try again.";
    }

    header("Location: dashboard.php");
    exit();
}
// Count total records for pagination
$total_records_query = "SELECT COUNT(*) as count FROM (" . $query . ") as subquery";
$total_records_result = $conn->query($total_records_query);
$total_records = $total_records_result->fetch_assoc()['count'];
$total_pages = ceil($total_records / $records_per_page);

// Add pagination to the main query
$query .= " ORDER BY bookings.date DESC LIMIT $offset, $records_per_page";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>

<head>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

    <style>
        /* Global Styles */
        body {
            background-color: #f8f9fa;
            font-family: 'DM Sans', sans-serif;
            color: #2d3748;
        }

        .container {
            max-width: 1400px;
        }

        /* Dashboard Header */
        h2 {
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0;
        }

        /* Search Bar Styling */
        .input-group {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        .input-group .form-control {
            border-right: none;
            padding: 0.75rem 1.25rem;
            height: auto;
        }

        .input-group .form-control:focus {
            box-shadow: none;
            border-color: #e2e8f0;
        }

        .input-group-append .btn-primary {
            background-color: #4299e1;
            border-color: #4299e1;
            padding: 0.75rem 1.25rem;
        }

        /* Stats Cards */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            color: #718096;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }

        .card-text {
            color: #2d3748;
            font-weight: 700;
            margin-bottom: 0;
        }

        /* Table Styling */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #f7fafc;
            border-top: none;
            border-bottom: 2px solid #e2e8f0;
            color: #4a5568;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem;
        }

        .table td {
            vertical-align: middle;
            padding: 1rem;
            border-color: #edf2f7;
        }

        /* Status Badges */
        .badge {
            padding: 0.5em 1em;
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-primary {
            background-color: #ebf8ff;
            color: #2b6cb0;
        }

        .badge-success {
            background-color: #f0fff4;
            color: #2f855a;
        }

        .badge-secondary {
            background-color: #f7fafc;
            color: #4a5568;
        }

        /* Action Buttons */
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
        }

        .btn-success {
            background-color: #48bb78;
            border-color: #48bb78;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }

        .btn-success:hover {
            background-color: #38a169;
            border-color: #38a169;
        }

        .btn-danger {
            background-color: #f56565;
            border-color: #f56565;
        }

        .btn-danger:hover {
            background-color: #e53e3e;
            border-color: #e53e3e;
        }

        .btn-primary {
            background-color: #4299e1;
            border-color: #4299e1;
        }

        .btn-primary:hover {
            background-color: #3182ce;
            border-color: #3182ce;
        }

        /* Pagination */
        .pagination {
            margin-top: 2rem;
            margin-bottom: 0;
        }

        .page-link {
            color: #4a5568;
            padding: 0.75rem 1rem;
            border-color: #e2e8f0;
        }

        .page-item.active .page-link {
            background-color: #4299e1;
            border-color: #4299e1;
        }

        /* Empty State */
        .text-muted {
            color: #718096 !important;
        }

        .lead {
            font-size: 1.125rem;
            color: #4a5568;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .card-body {
                padding: 1rem;
            }

            .table td,
            .table th {
                padding: 0.75rem;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2>Dashboard</h2>
            </div>
            <div class="col-md-6">
                <form method="GET" action="" class="form-inline justify-content-end">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control"
                            placeholder="Search bookings..."
                            value="<?php echo htmlspecialchars($search); ?>">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php
        $total_bookings = $total_records;
        $today = date('Y-m-d');
        $upcoming_bookings = 0;
        $current_bookings = 0;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['date'] > $today) {
                    $upcoming_bookings++;
                } elseif ($row['date'] == $today) {
                    $current_bookings++;
                }
            }
            $result->data_seek(0);
        }
        ?>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Bookings</h5>
                        <h2 class="card-text"><?php echo $total_bookings; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Today's Bookings</h5>
                        <h2 class="card-text"><?php echo $current_bookings; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Upcoming Bookings</h5>
                        <h2 class="card-text"><?php echo $upcoming_bookings; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!is_admin()): ?>
            <div class="text-right mb-4">
                <a href="book_room.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Book a new Room
                </a>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Room Name</th>
                                    <th>Location</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <?php if (is_admin()): ?>
                                        <th>Booked By</th>
                                    <?php endif; ?>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($booking = $result->fetch_assoc()):
                                    $booking_date = strtotime($booking['date']);
                                    $current_date = strtotime(date('Y-m-d'));

                                    if ($booking_date > $current_date) {
                                        $status = 'upcoming';
                                        $status_text = 'Upcoming';
                                        $badge_class = 'badge-primary';
                                    } elseif ($booking_date == $current_date) {
                                        $status = 'ongoing';
                                        $status_text = 'Today';
                                        $badge_class = 'badge-success';
                                    } else {
                                        $status = 'past';
                                        $status_text = 'Past';
                                        $badge_class = 'badge-secondary';
                                    }
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($booking['room_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($booking['location']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($booking['date'])); ?></td>
                                        <td><?php echo date('g:i A', strtotime($booking['start_time'])) . ' - ' .
                                                date('g:i A', strtotime($booking['end_time'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <?php if (is_admin()): ?>
                                            <td><?php echo htmlspecialchars($booking['user_email']); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <?php if ($status !== 'past'): ?>
                                                <a href="delete_booking.php?id=<?php echo $booking['id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure you want to delete this booking?');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                                <?php if (!is_admin()): ?>
                                                    <a href="#"
                                                        onclick="openEditModal(
                                                            '<?php echo $booking['id']; ?>', 
                                                            '<?php echo $booking['room_id']; ?>',
                                                            '<?php echo $booking['date']; ?>', 
                                                            '<?php echo $booking['start_time']; ?>', 
                                                            '<?php echo $booking['end_time']; ?>'
                                                        );"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">No actions available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">First</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page - 1) . ($search ? '&search=' . urlencode($search) : ''); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);

                                for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i . ($search ? '&search=' . urlencode($search) : ''); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page + 1) . ($search ? '&search=' . urlencode($search) : ''); ?>">Next</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $total_pages . ($search ? '&search=' . urlencode($search) : ''); ?>">Last</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                        <p class="lead"><?php echo is_admin() ? 'No bookings found.' : 'You have not booked any rooms yet.'; ?></p>
                        <?php if (!is_admin()): ?>
                            <a href="book_room.php" class="btn btn-success">
                                <i class="fas fa-plus"></i> Book your first room
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="modal fade" id="editBookingModal" tabindex="-1" role="dialog" aria-labelledby="editBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBookingModalLabel">Edit Booking</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editBookingForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="update_booking" value="1">
                        <input type="hidden" name="booking_id" id="edit_booking_id">

                        <div class="form-group">
                            <label for="edit_room_id">Select Room:</label>
                            <select name="room_id" id="edit_room_id" class="form-control" required>
                                <?php
                                $rooms_query = "SELECT * FROM rooms";
                                $rooms_result = $conn->query($rooms_query);
                                while ($room = $rooms_result->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $room['id']; ?>">
                                        <?php echo htmlspecialchars($room['name'] . " - " . $room['location']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_date">Date:</label>
                            <input type="date" name="date" id="edit_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_start_time">Start Time:</label>
                            <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_end_time">End Time:</label>
                            <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" crossorigin="anonymous"></script>

    <script>
        // Move the function declaration outside document.ready
        function openEditModal(bookingId, roomId, date, startTime, endTime) {
            $('#edit_booking_id').val(bookingId);
            $('#edit_room_id').val(roomId);
            $('#edit_date').val(date);
            $('#edit_start_time').val(startTime);
            $('#edit_end_time').val(endTime);
            $('#editBookingModal').modal('show');
        }

        $(document).ready(function() {
            // Success/error messages
            <?php if (isset($_SESSION['success_message'])): ?>
                toastr.success('<?php echo $_SESSION['success_message']; ?>');
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                toastr.error('<?php echo $_SESSION['error_message']; ?>');
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        });

        // Add time validation functions
        function validateTime(time) {
            if (!time) return false;
            const [hours] = time.split(':').map(Number);
            return hours >= 6 && hours < 23;
        }

        function validateDuration(startTime, endTime) {
            if (!startTime || !endTime) return false;
            const start = new Date(`2000-01-01 ${startTime}`);
            const end = new Date(`2000-01-01 ${endTime}`);
            const diffHours = (end - start) / (1000 * 60 * 60);
            return diffHours >= 2 && diffHours <= 8;
        }

        // Add form validation
        $('#editBookingForm').on('submit', function(e) {
            e.preventDefault();

            const startTime = $('#edit_start_time').val();
            const endTime = $('#edit_end_time').val();
            const selectedDate = $('#edit_date').val();
            let isValid = true;
            let errorMessage = '';

            // Validate date
            const today = new Date().toISOString().split('T')[0];
            if (selectedDate < today) {
                isValid = false;
                errorMessage = 'Please select a future date';
                toastr.error(errorMessage);
            }

            // Validate time range
            if (!validateTime(startTime) || !validateTime(endTime)) {
                isValid = false;
                errorMessage = 'Booking hours are between 6:00 AM and 11:00 PM only';
                toastr.error(errorMessage);
            }

            // Validate duration
            if (!validateDuration(startTime, endTime)) {
                isValid = false;
                errorMessage = 'Booking must be minimum 2 hours (maximum 8 hours)';
                toastr.error(errorMessage);
            }

            // Check if end time is after start time
            if (startTime >= endTime) {
                isValid = false;
                errorMessage = 'End time must be after start time';
                toastr.error(errorMessage);
            }

            if (isValid) {
                this.submit();
            }
        });

        // Add real-time validation for time inputs
        $('#edit_start_time, #edit_end_time').on('change', function() {
            const startTime = $('#edit_start_time').val();
            const endTime = $('#edit_end_time').val();

            if (startTime && endTime) {
                if (!validateTime(startTime) || !validateTime(endTime)) {
                    toastr.warning('Booking hours are between 6:00 AM and 11:00 PM only');
                } else if (!validateDuration(startTime, endTime)) {
                    toastr.warning('Booking must be minimum 2 hours (maximum 8 hours)');
                } else if (startTime >= endTime) {
                    toastr.warning('End time must be after start time');
                }
            }
        });
    </script>

</body>

</html>

<?php include 'views/footer.php'; ?>