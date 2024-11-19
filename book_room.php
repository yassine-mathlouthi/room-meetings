<?php
include 'includes/config.php';
include 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php'); // Redirect to login if the user is not authenticated
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $user_id = $_SESSION['user_id'];

    // Check if the room is already booked for the selected date and time range
    $check_query = "SELECT * FROM bookings WHERE room_id = '$room_id' AND date = '$date' AND ((start_time >= '$start_time' AND start_time < '$end_time') OR (end_time > '$start_time' AND end_time <= '$end_time'))";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows > 0) {
        $error_message = "This room is already booked for the selected date and time range.";
    } else {
        // Insert the booking into the database
        $insert_query = "INSERT INTO bookings (room_id, user_id, date, start_time, end_time) VALUES ('$room_id', '$user_id', '$date', '$start_time', '$end_time')";
        if ($conn->query($insert_query)) {
            $success_message = "Room booked successfully!";
        } else {
            $error_message = "Error booking the room. Please try again.";
        }
    }
}

// Fetch available rooms
$rooms_query = "SELECT * FROM rooms";
$rooms_result = $conn->query($rooms_query);

?>

<?php include 'views/header.php'; ?>

<div class="container">
    <h2>Book a Meeting Room</h2>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Display available rooms as cards -->
    <div class="row">
        <?php while ($room = $rooms_result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                <img src="<?php echo $room['image']; ?>" class="card-img-top" alt="Room Image">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $room['name']; ?></h5>
                        <p class="card-text"><?php echo $room['location']; ?></p>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#bookingModal" data-roomid="<?php echo $room['id']; ?>" data-roomname="<?php echo $room['name']; ?>">Book This Room</button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Modal to show booking form -->
    <div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Book a Room</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Booking Form -->
                    <form method="POST">
                        <input type="hidden" name="room_id" id="room_id">
                        <div class="mb-3">
                            <label for="date" class="form-label">Select Date:</label>
                            <input type="date" name="date" id="date" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time:</label>
                            <input type="time" name="start_time" id="start_time" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time:</label>
                            <input type="time" name="end_time" id="end_time" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Book Room</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/footer.php'; ?>

<!-- Include Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<script>
    // When the user clicks on a room to book
    $('#bookingModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var room_id = button.data('roomid'); // Extract room ID from data-* attributes
        var room_name = button.data('roomname'); // Extract room name from data-* attributes

        // Update the modal's content
        var modal = $(this);
        modal.find('.modal-title').text('Book ' + room_name);
        modal.find('#room_id').val(room_id);
    });
</script>
