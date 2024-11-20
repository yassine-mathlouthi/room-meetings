<?php
include 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php'); // Redirect to login if the user is not authenticated
}

include 'views/header.php';

// Database connection
include 'includes/config.php';

// Fetch bookings based on the user role
if (is_admin()) {
    // Admin: Fetch all bookings
    $query = "SELECT bookings.id, rooms.name AS room_name, start_time, end_time, rooms.location, bookings.date, bookings.time_slot, users.email AS user_email 
              FROM bookings 
              INNER JOIN rooms ON bookings.room_id = rooms.id
              INNER JOIN users ON bookings.user_id = users.id";
} else {
    // User: Fetch only their bookings
    $user_id = $_SESSION['user_id'];
    $query = "SELECT bookings.id, rooms.name AS room_name, rooms.location, bookings.date,start_time, end_time, bookings.time_slot 
              FROM bookings 
              INNER JOIN rooms ON bookings.room_id = rooms.id
              WHERE bookings.user_id = '$user_id'";
}

$result = $conn->query($query);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-3 mx-auto ">
        <h2>/Dashboard</h2>

        </div>
    </div>
    
    <?php if (is_admin()): ?>
        <h3>All Booked Rooms</h3>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        
                        <th>Room Name</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Start time</th>
                        <th>End time</th>
                        <th>Booked By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $result->fetch_assoc()): ?>
                        <tr>
                            
                            <td><?php echo $booking['room_name']; ?></td>
                            <td><?php echo $booking['location']; ?></td>
                            <td><?php echo $booking['date']; ?></td>
                            <td><?php echo $booking['start_time']; ?></td>
                            <td><?php echo $booking['end_time']; ?></td>
                            <td><?php echo $booking['user_email']; ?></td>
                            <td>
                                <a href="delete_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this booking?');">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No bookings found.</p>
        <?php endif; ?>
    <?php else: ?>
        <div class="row">
            <div class="col">
            <a href="book_room.php" class="btn book_room_btn"> <i class="fas fa-add"></i> Book a new Room</a>
            </div>
        </div>
        
 
        
        
        
        <h3>Booked Rooms table : </h3>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered table-custom">
                <thead>
                    <tr>
                        
                        <th>Room Name</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Start time</th>
                        <th>End time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $result->fetch_assoc()): ?>
                        <tr>
                          
                            <td><?php echo $booking['room_name']; ?></td>
                            <td><?php echo $booking['location']; ?></td>
                            <td><?php echo $booking['date']; ?></td>
                            <td><?php echo $booking['start_time']; ?></td>
                            <td><?php echo $booking['end_time']; ?></td>
                            <td>
                                <a href="delete_booking.php?id=<?php echo $booking['id']; ?>" class="btn delete_btn" onclick="return confirm('Are you sure you want to delete this booking?');">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                                <a href="update_booking.php?id=<?php echo $booking['id']; ?>" class="btn update_btn" >
                                    <i class="fas fa-edit"></i> Update
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have not booked any rooms yet.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'views/footer.php'; ?>
