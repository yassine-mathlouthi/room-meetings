<?php
include 'includes/functions.php';

if (!is_logged_in() || !isset($_GET['id'])) {
    redirect('login.php');
}

include 'includes/config.php';

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if the user is trying to delete their own booking or if the user is an admin
$query = "SELECT * FROM bookings WHERE id = '$booking_id' AND (user_id = '$user_id' OR EXISTS (SELECT 1 FROM users WHERE role = 'admin' AND id = '$user_id'))";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    // Proceed with deletion
    $delete_query = "DELETE FROM bookings WHERE id = '$booking_id'";
    if ($conn->query($delete_query)) {
        redirect('dashboard.php'); // Redirect to the dashboard after deletion
    } else {
        echo "Error deleting the booking. Please try again.";
    }
} else {
    echo "You do not have permission to delete this booking.";
}
?>
