<?php
include 'includes/config.php';
include 'includes/functions.php';

if (!is_admin()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $name = $_POST['name'];
    $location = $_POST['location'];
    $capacity = $_POST['capacity'];

    // Handle the image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Define the upload directory and allowed file types
        $upload_dir = 'uploads/'; // Directory to store images
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif']; // Allowed file types

        // Get the file's properties
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $file_type = $_FILES['image']['type'];

        // Check if the file is an allowed image type
        if (in_array($file_type, $allowed_types)) {
            // Generate a unique file name to avoid overwriting
            $new_file_name = uniqid('room_', true) . '.' . pathinfo($file_name, PATHINFO_EXTENSION);

            // Move the uploaded file to the upload directory
            if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                // If the upload is successful, store the image path in the database
                $image_path = $upload_dir . $new_file_name;
            } else {
                $error_message = "Error uploading the image. Please try again.";
            }
        } else {
            $error_message = "Invalid image type. Only JPEG, PNG, and GIF files are allowed.";
        }
    } else {
        $image_path = null; // No image uploaded
    }

    // Insert the room details into the database
    if (empty($error_message)) {
        $query = "INSERT INTO rooms (name, location, capacity, image) VALUES ('$name', '$location', $capacity, '$image_path')";
        if ($conn->query($query)) {
            $success_message = "Room added successfully!";
        } else {
            $error_message = "Error adding the room. Please try again.";
        }
    }
}

$result = $conn->query("SELECT * FROM rooms");

?>

<?php include 'views/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Add a New Meeting Room</h2>

    <!-- Display any error or success message -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php elseif (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Add Room Form -->
    <form method="post" class="mb-5" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Room Name:</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="location">Location:</label>
            <input type="text" name="location" id="location" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="capacity">Capacity:</label>
            <input type="number" name="capacity" id="capacity" class="form-control" required>
        </div>

        <!-- Image Upload Field -->
        <div class="form-group">
            <label for="image">Room Image:</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
        </div>
        
        <button type="submit" class="btn btn-primary">Add Room</button>
    </form>

    <!-- Room List -->
    <h3>Existing Rooms</h3>
    <?php if ($result->num_rows > 0): ?>
        <ul class="list-group">
            <?php while ($room = $result->fetch_assoc()): ?>
                <li class="list-group-item">
                    <?php echo $room['name'] . " - " . $room['location'] . " (" . $room['capacity'] . " people)"; ?>
                    <?php if ($room['image']): ?>
                        <img src="<?php echo $room['image']; ?>" alt="Room Image" style="width: 100px; height: auto;">
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No rooms available yet.</p>
    <?php endif; ?>
</div>

<!-- Include Bootstrap JS (for responsive navbar, etc.) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEJRAk7i8z2fK0gEo9z1yDJi45OXYFmjgsuYyAyIYhMZoBz3F2trlf5UAMpzR" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-VhTcY7Joo3lP0HfX7mMGV7sS4t+r8+5T1LO4MiJAZ7hP/2GbVmRWrkk7vwgMkhdz" 
        crossorigin="anonymous"></script>

