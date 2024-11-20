<?php
include 'includes/config.php';
include 'includes/functions.php';
include 'views/header.php';
// Check if user is logged in and has the right permissions
if (!is_logged_in()) {
    redirect('login.php'); // Redirect to login if the user is not authenticated
}

if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];

    // Fetch the current booking details from the database
    $query = "SELECT * FROM bookings WHERE id = '$booking_id'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
    } else {
        echo "Booking not found.";
        exit;
    }
}

// Handle the form submission for updating the booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Update the booking in the database
    $update_query = "UPDATE bookings SET room_id = '$room_id', date = '$date', start_time = '$start_time', end_time = '$end_time' WHERE id = '$booking_id'";

    if ($conn->query($update_query)) {
        $success_message = "Booking updated successfully!";
    } else {
        $error_message = "Error updating booking. Please try again.";
    }
}

// Fetch available rooms for the dropdown list
$rooms_query = "SELECT * FROM rooms";
$rooms_result = $conn->query($rooms_query);
?>

<!-- Include Bootstrap CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" 
      integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" 
      crossorigin="anonymous">

<div class="container-fluid">
<div class="row">
        <div class="col-5 mx-auto">
        <h2>/Update booking of room : <?php $room = $rooms_result->fetch_assoc() ; echo $room['name'] ?> </h2>

        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Booking Update Form -->
    <div class="row">

    <form method="post" class="col-md-8 mx-auto card shadow card-body mt-4">
        <div class="mb-3 ">
            <label for="room_id" class="form-label">Select Room:</label>
            <select name="room_id" id="room_id" class="form-control" required>
                <?php while ($room = $rooms_result->fetch_assoc()): ?>
                    <option value="<?php echo $room['id']; ?>" <?php echo ($room['id'] == $booking['room_id']) ? 'selected' : ''; ?>>
                        <?php echo $room['name'] . " - " . $room['location']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">Select Date:</label>
            <input type="date" name="date" id="date" class="form-control" value="<?php echo $booking['date']; ?>" required>
        </div>

        <div class="mb-3">
            <label for="start_time" class="form-label">Start Time:</label>
            <input type="time" name="start_time" id="start_time" class="form-control" value="<?php echo $booking['start_time']; ?>" required>
        </div>

        <div class="mb-3">
            <label for="end_time" class="form-label">End Time:</label>
            <input type="time" name="end_time" id="end_time" class="form-control" value="<?php echo $booking['end_time']; ?>" required>
        </div>

        <button type="submit" class="btn update_btn" >
                                    <i class="fas fa-edit"></i> Update </button>
    </form>
    </div>
   
</div>

<!-- Include Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEJRAk7i8z2fK0gEo9z1yDJi45OXYFmjgsuYyAyIYhMZoBz3F2trlf5UAMpzR" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-VhTcY7Joo3lP0HfX7mMGV7sS4t+r8+5T1LO4MiJAZ7hP/2GbVmRWrkk7vwgMkhdz" 
        crossorigin="anonymous"></script>
