<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('log_errors', 1);
ini_set('error_log', 'error.log'); // This will create error.log in your script's directory

function exception_handler($e)
{
    error_log($e->getMessage());
    error_log($e->getTraceAsString());
    echo "An error occurred: " . $e->getMessage();
}
set_exception_handler('exception_handler');

include 'includes/config.php';
include 'includes/functions.php';
require_once 'includes/EmailService.php';



if (!is_logged_in()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $user_id = $_SESSION['user_id'];

    // Check if the room is already booked
    // Check if the room is already booked
    $check_query = "SELECT * FROM bookings 
WHERE room_id = ? 
AND date = ? 
AND (
    (start_time <= ? AND end_time > ?) OR  -- New booking starts during existing booking
    (start_time < ? AND end_time >= ?) OR  -- New booking ends during existing booking
    (start_time >= ? AND end_time <= ?)    -- Existing booking completely contains new booking
)";

    $stmt = $conn->prepare($check_query);
    $stmt->bind_param(
        "isssssss",
        $room_id,
        $date,
        $start_time,
        $start_time,  // For first condition
        $end_time,
        $end_time,      // For second condition
        $start_time,
        $end_time     // For third condition
    );

    try {
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "This room is already booked for the selected time range.";
        } else {
            $stmt = $conn->prepare("INSERT INTO bookings (room_id, user_id, date, start_time, end_time) 
               VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $room_id, $user_id, $date, $start_time, $end_time);

            if ($stmt->execute()) {
                try {
                    // Get room details for the email
                    $room_query = "SELECT r.*, c.name AS category_name 
                                   FROM rooms r 
                                   LEFT JOIN categories c ON r.category_id = c.id 
                                   WHERE r.id = ?";
                    $room_stmt = $conn->prepare($room_query);
                    $room_stmt->bind_param("i", $room_id);
                    $room_stmt->execute();
                    $room = $room_stmt->get_result()->fetch_assoc();

                    // Get user details
                    $user_query = "SELECT name, email FROM users WHERE id = ?";
                    $user_stmt = $conn->prepare($user_query);
                    $user_stmt->bind_param("i", $user_id);
                    $user_stmt->execute();
                    $user = $user_stmt->get_result()->fetch_assoc();

                    // Prepare email data
                    $bookingData = [
                        "first_name" => $user['name'],
                        "roomName" => $room['name'],
                        "bookingDate" => date('F j, Y', strtotime($date)),
                        "startTime" => date('g:i A', strtotime($start_time)),
                        "endTime" => date('g:i A', strtotime($end_time)),
                        "location" => $room['location'],
                        "Sender_Email" => "chbinoumed06@gmail.com"
                    ];

                    // Send confirmation email
                    $emailService = new EmailService();
                    $emailResult = $emailService->sendBookingConfirmation(
                        $user['email'],
                        $user['name'],
                        $bookingData
                    );

                    error_log("Email result: " . print_r($emailResult, true));

                    if ($emailResult['success']) {
                        $success_message = "Room booked successfully! A confirmation email has been sent to your email address.";
                        error_log("Booking email failed: " . $emailResult['message']);
                        if (isset($emailResult['status'])) {
                            error_log("SendGrid status code: " . $emailResult['status']);
                        }
                    } else {
                        $success_message = "Room booked successfully! However, there was an issue sending the confirmation email: " . $emailResult['message'];
                    }
                } catch (Exception $e) {
                    // Booking was successful but email failed
                    $success_message = "Room booked successfully! However, there was an issue sending the confirmation email.";
                    error_log("Email sending error: " . $e->getMessage()); // Log the error for debugging
                }
            } else {
                $error_message = "Error booking the room.";
            }
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error code
            $error_message = "This time slot has just been booked by someone else. Please try a different time.";
        } else {
            $error_message = "An error occurred while processing your booking. Please try again.";
        }
    }
}

// Fetch available rooms with categories
$rooms_query = "SELECT r.*, c.name AS category_name 
                FROM rooms r 
                LEFT JOIN categories c ON r.category_id = c.id 
                ORDER BY r.name";
$rooms_result = $conn->query($rooms_query);
?>

<?php include 'views/header.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            color: #2d3748;
        }

        .page-header {
            background: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        .page-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: #1a202c;
            margin: 0;
        }

        .booking-notice {
            background: #ebf4ff;
            border-left: 4px solid #4361ee;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }

        .room-card {
            background: white;
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            overflow: hidden;
        }

        .room-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .room-image-container {
            position: relative;
            width: 100%;
            padding-top: 66.67%;
            overflow: hidden;
        }

        .room-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .room-card:hover .room-image {
            transform: scale(1.05);
        }

        .category-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.9);
            color: #4361ee;
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 1rem;
            font-weight: 500;
        }

        .room-details {
            padding: 1.25rem;
        }

        .room-name {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 1rem;
            color: #2d3748;
        }

        .room-info {
            background: #f8fafc;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-icon {
            color: #4361ee;
            width: 1.25rem;
            margin-right: 0.5rem;
        }

        .btn-book {
            background-color: #4361ee;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            width: 100%;
            transition: background-color 0.2s ease;
        }

        .btn-book:hover {
            background-color: #3a53d0;
            color: white;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 0.75rem;
            border: none;
        }

        .modal-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.75rem;
        }

        .form-control:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .time-restrictions {
            background: #fff8f1;
            border-left: 4px solid #ff9f43;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .availability-icon {
            position: absolute;
            top: 10px;
            left: 10px;
            background: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .availability-icon:hover {
            transform: scale(1.1);
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.8rem;
            color: #666;
        }

        .timeline-slots {
            height: 30px;
            background: #f0f0f0;
            border-radius: 15px;
            position: relative;
            overflow: hidden;
        }

        .booked-slot {
            position: absolute;
            height: 100%;
            background: #dc3545;
            opacity: 0.7;
        }
    </style>
</head>

<body>
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">Book a Meeting Room</h1>
        </div>
    </div>

    <div class="container">
        <div class="booking-notice">
            <i class="fas fa-info-circle mr-2"></i>
            Welcome to our room booking system. Please select a room and your preferred time slot.
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php while ($room = $rooms_result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="room-card">
                        <div class="room-image-container">
                            <?php if ($room['image']): ?>
                                <img class="room-image" src="<?php echo htmlspecialchars($room['image']); ?>"
                                    alt="<?php echo htmlspecialchars($room['name']); ?>">
                            <?php else: ?>
                                <img class="room-image" src="/api/placeholder/400/300" alt="Room placeholder">
                            <?php endif; ?>
                            <span class="category-badge">
                                <i class="fas fa-tag mr-1"></i>
                                <?php echo htmlspecialchars($room['category_name']); ?>
                            </span>

                            <button class="availability-icon" data-toggle="modal" data-target="#availabilityModal"
                                data-roomid="<?php echo $room['id']; ?>"
                                data-roomname="<?php echo htmlspecialchars($room['name']); ?>">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                        </div>
                        <div class="room-details">
                            <h3 class="room-name"><?php echo htmlspecialchars($room['name']); ?></h3>
                            <div class="room-info">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt info-icon"></i>
                                    <span><?php echo htmlspecialchars($room['location']); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-users info-icon"></i>
                                    <span>Capacity: <?php echo htmlspecialchars($room['capacity']); ?> people</span>
                                </div>
                                <?php if (!empty($room['description'])): ?>
                                    <div class="info-item">
                                        <i class="fas fa-info-circle info-icon"></i>
                                        <span><?php echo htmlspecialchars($room['description']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-book" data-toggle="modal" data-target="#bookingModal"
                                data-roomid="<?php echo $room['id']; ?>"
                                data-roomname="<?php echo htmlspecialchars($room['name']); ?>">
                                <i class="fas fa-calendar-plus mr-2"></i>Book Now
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        <span id="modalRoomName">Book Room</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="time-restrictions">
                        <i class="fas fa-clock mr-2"></i>
                        Available booking hours: 6:00 AM - 11:00 PM<br>
                        <i class="fas fa-info-circle mr-2"></i>
                        Minimum booking duration: 2 hours (maximum 8 hours)
                    </div>

                    <form method="POST" id="bookingForm">
                        <input type="hidden" name="room_id" id="room_id">

                        <div class="form-group">
                            <label><i class="fas fa-calendar-day mr-2"></i>Date</label>
                            <input type="date" name="date" id="booking-date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-clock mr-2"></i>Start Time</label>
                            <input type="time" name="start_time" id="start_time" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-clock mr-2"></i>End Time</label>
                            <input type="time" name="end_time" id="end_time" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-book">
                            <i class="fas fa-check mr-2"></i>Confirm Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Availability Modal -->
    <div class="modal fade" id="availabilityModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span id="availabilityRoomName">Room Availability</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Date</label>
                        <input type="date" id="availability-date" class="form-control">
                    </div>
                    <div id="availability-timeline" class="mt-3">
                        <div class="timeline-header">
                            <div>6AM</div>
                            <div>9AM</div>
                            <div>12PM</div>
                            <div>3PM</div>
                            <div>6PM</div>
                            <div>9PM</div>
                            <div>11PM</div>
                        </div>
                        <div class="timeline-slots">
                            <!-- Slots will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js"></script>

    <?php include 'views/footer.php'; ?>



    <script>
        $(document).ready(function() {
            // Initialize variables
            const bookingForm = document.getElementById('bookingForm');
            const dateInput = document.getElementById('booking-date');
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');

            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Time validation function
            function validateTime(time) {
                if (!time) return false;
                const [hours] = time.split(':').map(Number);
                return hours >= 6 && hours < 23;
            }

            // Duration validation function
            function validateDuration(startTime, endTime) {
                if (!startTime || !endTime) return false;

                const start = new Date(`2000-01-01 ${startTime}`);
                const end = new Date(`2000-01-01 ${endTime}`);
                const diffHours = (end - start) / (1000 * 60 * 60);

                // Check minimum 2 hours
                if (diffHours < 2) {
                    return false;
                }

                // Maximum 8 hours check
                return diffHours <= 8;
            }

            // Update modal with room details
            $('#bookingModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const roomId = button.data('roomid');
                const roomName = button.data('roomname');

                $('#modalRoomName').text('Book ' + roomName);
                $(' #room_id').val(roomId);

                // Reset form and set default date
                bookingForm.reset();
                dateInput.value = today;

                // Clear any previous error messages
                $('.booking-error').remove();
            });

            // Real-time validation for start time
            startTimeInput.addEventListener('change', function() {
                if (!validateTime(this.value)) {
                    showError(this, 'Booking hours are between 6:00 AM and 11:00 PM only');
                } else {
                    removeError(this);
                }
            });

            // Real-time validation for end time
            endTimeInput.addEventListener('change', function() {
                if (!validateTime(this.value)) {
                    showError(this, 'Booking hours are between 6:00 AM and 11:00 PM only');
                } else if (!validateDuration(startTimeInput.value, this.value)) {
                    showError(this, 'Booking must be minimum 2 hours (maximum 8 hours)');
                } else {
                    removeError(this);
                }
            });

            // Helper function to show error message
            function showError(element, message) {
                removeError(element);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'booking-error text-danger mt-1';
                errorDiv.style.fontSize = '0.875rem';
                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>${message}`;
                element.parentNode.appendChild(errorDiv);
            }

            // Helper function to remove error message
            function removeError(element) {
                const existingError = element.parentNode.querySelector('.booking-error');
                if (existingError) {
                    existingError.remove();
                }
            }

            // Form submission validation
            bookingForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const startTime = startTimeInput.value;
                const endTime = endTimeInput.value;
                const selectedDate = dateInput.value;
                let isValid = true;
                let errorMessage = '';

                // Clear previous errors
                $('.booking-error').remove();

                // Validate date
                if (selectedDate < today) {
                    isValid = false;
                    errorMessage = 'Please select a future date';
                    showError(dateInput, errorMessage);
                }

                // Validate time range
                if (!validateTime(startTime) || !validateTime(endTime)) {
                    isValid = false;
                    errorMessage = 'Booking hours are between 6:00 AM and 11:00 PM only';
                    showError(startTimeInput, errorMessage);
                }

                // Validate duration
                if (!validateDuration(startTime, endTime)) {
                    isValid = false;
                    errorMessage = 'Booking must be minimum 2 hours (maximum 8 hours)';
                    showError(endTimeInput, errorMessage);
                }

                // Check if end time is after start time
                if (startTime >= endTime) {
                    isValid = false;
                    errorMessage = 'End time must be after start time';
                    showError(endTimeInput, errorMessage);
                }

                if (isValid) {
                    // If all validations pass, submit the form
                    this.submit();
                } else {
                    // Show error in modal
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show mb-3';
                    alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle mr-2"></i>${errorMessage}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            `;
                    this.insertBefore(alertDiv, this.firstChild);
                }
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            let currentRoomId = null;

            $('#availabilityModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const roomId = button.data('roomid');
                const roomName = button.data('roomname');
                currentRoomId = roomId;

                $('#availabilityRoomName').text(roomName + ' - Availability');
                $('#availability-date').val(new Date().toISOString().split('T')[0]);

                updateAvailability();
            });

            $('#availability-date').on('change', updateAvailability);

            function updateAvailability() {
                const date = $('#availability-date').val();

                $.get('availability.php', {
                    room_id: currentRoomId,
                    date: date
                }, function(bookings) {
                    const timeline = $('.timeline-slots');
                    timeline.empty();

                    bookings.forEach(function(booking) {
                        const startTime = new Date(`2000-01-01 ${booking.start}`);
                        const endTime = new Date(`2000-01-01 ${booking.end}`);

                        const startHour = startTime.getHours() + startTime.getMinutes() / 60;
                        const endHour = endTime.getHours() + endTime.getMinutes() / 60;

                        const startPercent = ((startHour - 6) / 17) * 100;
                        const width = ((endHour - startHour) / 17) * 100;

                        const slot = $('<div>')
                            .addClass('booked-slot')
                            .css({
                                left: startPercent + '%',
                                width: width + '%'
                            })
                            .attr('title', `${booking.start} - ${booking.end}`);

                        timeline.append(slot);
                    });
                });
            }
        });
    </script>
</body>

</html>