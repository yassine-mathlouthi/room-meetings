<?php
include 'includes/config.php';
include 'includes/functions.php';

if (!is_admin()) {
    redirect('dashboard.php');
}

// Handle Add/Edit/Delete Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        // Common Data
        $name = $_POST['name'];
        $location = $_POST['location'];
        $capacity = intval($_POST['capacity']);
        $category_id = intval($_POST['category_id']);
        $image_path = $_POST['current_image'] ?? null;

        // Handle Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = 'uploads/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_type = $_FILES['image']['type'];

            if (in_array($file_type, $allowed_types)) {
                $new_file_name = uniqid('room_', true) . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    $image_path = $upload_dir . $new_file_name;
                } else {
                    $_SESSION['error_message'] = "Error uploading the image.";
                }
            } else {
                $_SESSION['error_message'] = "Invalid image type.";
            }
        }

        if ($action === 'add') {
            // Add Room Query
            $stmt = $conn->prepare("INSERT INTO rooms (name, location, capacity, category_id, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiss", $name, $location, $capacity, $category_id, $image_path);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Room added successfully!";
            } else {
                $_SESSION['error_message'] = "Error adding the room.";
            }
        } elseif ($action === 'edit') {
            $id = intval($_POST['id']);

            // Edit Room Query
            $stmt = $conn->prepare("UPDATE rooms SET name=?, location=?, capacity=?, category_id=?, image=? WHERE id=?");
            $stmt->bind_param("ssissi", $name, $location, $capacity, $category_id, $image_path, $id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Room updated successfully!";
            } else {
                $_SESSION['error_message'] = "Error updating the room.";
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Delete Room Query
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Room deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting the room.";
    }

    header('Location: manage_rooms.php');
    exit;
}

// Fetch Rooms and Categories
$items_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total number of rooms
$total_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms")->fetch_assoc()['count'];
$total_pages = ceil($total_rooms / $items_per_page);

// Fetch Rooms with pagination
$rooms = $conn->query("SELECT r.*, c.name AS category_name 
                      FROM rooms r 
                      LEFT JOIN categories c ON r.category_id = c.id 
                      LIMIT $offset, $items_per_page");
$categories_result = $conn->query("SELECT * FROM categories");
$categories = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories[] = $category;
}
?>

<?php include 'views/header.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&family=Lora:wght@0,400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">


    <style>
        /* Global Styles */
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #2d3748;
            line-height: 1.5;
        }

        .container-fluid {
            padding: 1.5rem;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Page Header */
        .page-header {
            background: white;
            padding: 1rem 0;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        .page-header h2 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: #1a202c;
            font-size: 1.5rem;
            margin: 0;
        }

        /* Room Cards - Fixed Layout */
        .room-card {
            background: white;
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .room-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Fixed Image Container */
        .room-image-container {
            position: relative;
            width: 100%;
            padding-top: 66.67%;
            /* 3:2 aspect ratio */
            border-radius: 0.75rem 0.75rem 0 0;
            overflow: hidden;
            background-color: #e2e8f0;
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

        .info-icon {
            color: #4361ee;
            width: 1.25rem;
            margin-right: 0.5rem;
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

        .btn-book {
            background-color: #4361ee;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .btn-book:hover {
            background-color: #3a53d0;
            color: white;
        }

        /* Card Content */
        .card-body {
            padding: 1.25rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 1rem;
            color: #2d3748;
        }

        /* Category Badge */
        .category-badge {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: rgba(255, 255, 255, 0.9);
            color: #4361ee;
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 1rem;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Room Stats */
        .room-stats {
            background: #f8fafc;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-top: auto;
            font-size: 0.875rem;
        }

        .stat-icon {
            color: #4361ee;
            width: 1.25rem;
            margin-right: 0.5rem;
        }

        /* Buttons */
        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #4361ee;
            border: none;
        }

        .btn-primary:hover {
            background-color: #3a53d0;
        }

        .btn-warning {
            background-color: #ff9f43;
            border: none;
            color: white;
        }

        .btn-danger {
            background-color: #ea5455;
            border: none;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 0.75rem;
            border: none;
        }

        .modal-header {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            height: auto;
        }

        .form-control:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        /* Alerts */
        .alert {
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: none;
        }

        /* Pagination */
        .pagination {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        .pagination .page-link {
            padding: 0.5rem 0.75rem;
            margin: 0 0.125rem;
            border-radius: 0.375rem;
            border: none;
            color: #4361ee;
        }

        .pagination .page-item.active .page-link {
            background-color: #4361ee;
        }

        /* Image Preview in Modal */
        .img-preview {
            max-height: 150px;
            width: auto;
            border-radius: 0.375rem;
            margin-top: 0.5rem;
        }

        /* Grid Layout */
        .row {
            margin-right: -0.75rem;
            margin-left: -0.75rem;
        }

        .col-md-4,
        .col-lg-3 {
            padding-right: 0.75rem;
            padding-left: 0.75rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem;
            }

            .room-card {
                margin-bottom: 1rem;
            }

            .btn {
                padding: 0.5rem 0.75rem;
            }

            .modal-dialog {
                margin: 0.5rem;
            }
        }
    </style>
</head>

<body class="bg-light">

    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Meeting Room Management</h2>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addRoomModal">
                    <i class="fas fa-plus mr-2"></i>Add New Room
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php while ($room = $rooms->fetch_assoc()): ?>
                <div class="col-md-4 col-lg-4 mb-4">
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

                            <!-- Add availability button -->
                            <button class="availability-icon" data-toggle="modal"
                                data-target="#availabilityModal"
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
                            <div class="mt-3 d-flex justify-content-between">
                                <button class="btn btn-book edit-room-btn" data-toggle="modal"
                                    data-target="#editRoomModal"
                                    data-id="<?php echo $room['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($room['name']); ?>"
                                    data-location="<?php echo htmlspecialchars($room['location']); ?>"
                                    data-capacity="<?php echo $room['capacity']; ?>"
                                    data-category="<?php echo $room['category_id']; ?>"
                                    data-description="<?php echo htmlspecialchars($room['description']); ?>"
                                    data-image="<?php echo htmlspecialchars($room['image']); ?>">
                                    <i class="fas fa-edit mr-2"></i>Edit
                                </button>
                                <a href="manage_rooms.php?delete=<?php echo $room['id']; ?>"
                                    class="btn btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this room?')">
                                    <i class="fas fa-trash-alt mr-2"></i>Delete
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>



        <!-- Add pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Room navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" <?php echo ($page <= 1) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" <?php echo ($page >= $total_pages) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    </div>

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

    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" role="dialog" aria-labelledby="addRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle mr-2"></i>Add New Room
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label><i class="fas fa-door-open mr-2"></i>Room Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt mr-2"></i>Location</label>
                            <input type="text" name="location" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-users mr-2"></i>Capacity</label>
                            <input type="number" name="capacity" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-info-circle mr-2"></i>Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-tag mr-2"></i>Category</label>
                            <select name="category_id" class="form-control" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-image mr-2"></i>Room Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Save Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1" role="dialog" aria-labelledby="editRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit mr-2"></i>Edit Room
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" id="edit-room-id" name="id">
                        <div class="form-group">
                            <label><i class="fas fa-door-open mr-2"></i>Room Name</label>
                            <input type="text" name="name" id="edit-name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt mr-2"></i>Location</label>
                            <input type="text" name="location" id="edit-location" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-users mr-2"></i>Capacity</label>
                            <input type="number" name="capacity" id="edit-capacity" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-info-circle mr-2"></i>Description</label>
                            <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-tag mr-2"></i>Category</label>
                            <select name="category_id" id="edit-category" class="form-control" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-image mr-2"></i>New Image (optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <input type="hidden" name="current_image" id="edit-room-current-image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>


    <script>
        $(document).ready(function() {

            console.log('jQuery version:', jQuery.fn.jquery);

            // Debug check
            console.log('jQuery loaded:', typeof jQuery !== 'undefined');
            console.log('Bootstrap modal loaded:', typeof jQuery.fn.modal !== 'undefined');

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Populate edit modal with room data
            $('.edit-room-btn').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const location = $(this).data('location');
                const capacity = $(this).data('capacity');
                const category = $(this).data('category');
                const description = $(this).data('description');
                const image = $(this).data('image');

                $('#edit-room-id').val(id);
                $('#edit-name').val(name);
                $('#edit-location').val(location);
                $('#edit-capacity').val(capacity);
                $('#edit-category').val(category);
                $('#edit-description').val(description);
                $('#edit-room-current-image').val(image);
            });

            // Handle availability modal
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
        // Preview image before upload
        $('input[type="file"]').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Remove any existing preview
                    $(this).siblings('.img-preview').remove();

                    const preview = $('<img>')
                        .addClass('img-preview mt-2 img-thumbnail')
                        .attr('src', e.target.result)
                        .attr('style', 'max-height: 200px');
                    $(this).parent().append(preview);
                }.bind(this);
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>