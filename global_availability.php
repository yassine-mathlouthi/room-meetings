<?php
include 'includes/config.php';
include 'includes/functions.php';

if (!is_admin()) {
    redirect('dashboard.php');
}

// Pagination settings
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "SELECT r.*, c.name AS category_name 
          FROM rooms r 
          LEFT JOIN categories c ON r.category_id = c.id";

// Add search condition if search term exists
if (!empty($search)) {
    $query .= " WHERE r.name LIKE '%" . $conn->real_escape_string($search) . "%'";
}

// Get total number of rooms for pagination
$total_rooms = $conn->query($query)->num_rows;
$total_pages = ceil($total_rooms / $items_per_page);

// Add pagination limit
$offset = ($page - 1) * $items_per_page;
$query .= " ORDER BY r.name ASC LIMIT $offset, $items_per_page";

$rooms = $conn->query($query);
$rooms_array = [];
while ($room = $rooms->fetch_assoc()) {
    $rooms_array[] = $room;
}
?>

<?php include 'views/header.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Room Availability</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        /* Inherit your global styles */
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #2d3748;
            line-height: 1.5;
        }

        .page-header {
            background: white;
            padding: 1rem 0;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        /* Global availability specific styles */
        .availability-container {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .time-header {
            display: grid;
            grid-template-columns: 200px repeat(9, 1fr);
            margin-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0.5rem;
        }

        .time-slot {
            text-align: center;
            font-size: 0.875rem;
            color: #4a5568;
            font-weight: 500;
        }

        .room-row {
            display: grid;
            grid-template-columns: 200px repeat(9, 1fr);
            margin-bottom: 0.5rem;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f7fafc;
        }

        .room-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-right: 1rem;
        }

        .room-name {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.875rem;
        }

        .time-block {
            height: 30px;
            margin: 0 2px;
            border-radius: 4px;
            position: relative;
        }

        .available {
            background-color: #def7ec;
        }

        .booked {
            background-color: #fde8e8;
        }

        .partially-booked {
            background: linear-gradient(45deg, #def7ec 50%, #fde8e8 50%);
        }

        .category-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
        }



        .filters {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* Ensures vertical alignment */
        }

        .date-navigation {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .search-box {
            display: flex;
            align-items: center;
        }

        .nav-btn {
            background: #4361ee;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .nav-btn:hover {
            background-color: #3a53d0;
        }



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

        @media (max-width: 1200px) {

            .time-header,
            .room-row {
                grid-template-columns: 150px repeat(9, 1fr);
            }
        }

        @media (max-width: 768px) {
            .availability-container {
                overflow-x: auto;
            }

            .time-header,
            .room-row {
                min-width: 900px;
            }
        }
    </style>
</head>

<body>
    <div class="page-header">
        <div class="container-fluid">
            <h2 class="mb-0">Global Room Availability</h2>
        </div>
    </div>

    <div class="container-fluid">
        <div class="filters">
            <div class="date-navigation">
                <button class="nav-btn" id="prevDay">
                    <i class="fas fa-chevron-left mr-2"></i>Previous Day
                </button>
                <input type="date" id="selectedDate" class="form-control" style="width: auto;">
                <button class="nav-btn" id="nextDay">
                    Next Day<i class="fas fa-chevron-right ml-2"></i>
                </button>
            </div>
            <div class="search-box">
                <form action="" method="GET" class="flex-grow-1">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search rooms..."
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


        <div class="availability-container">
            <div class="time-header">
                <div class="time-slot">Room Name</div>
                <?php
                // Generate time slots from 6 AM to 11 PM in 2-hour intervals
                for ($hour = 6; $hour <= 23; $hour += 2) {
                    $nextHour = min($hour + 2, 23);
                    echo '<div class="time-slot">' .
                        sprintf("%02d:00", $hour) . ' - ' .
                        sprintf("%02d:00", $nextHour) .
                        '</div>';
                }
                ?>
            </div>

            <div id="availability-grid">
                <?php foreach ($rooms_array as $room): ?>
                    <div class="room-row" data-room-id="<?php echo $room['id']; ?>">
                        <div class="room-info">
                            <span class="category-indicator" style="background-color: #4361ee;"></span>
                            <span class="room-name"><?php echo htmlspecialchars($room['name']); ?></span>
                        </div>
                        <?php
                        // Placeholder slots - will be filled by JavaScript
                        for ($hour = 6; $hour <= 23; $hour += 2) {
                            echo '<div class="time-block available" data-start="' . $hour . '" data-end="' . min($hour + 2, 23) . '"></div>';
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Room navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" <?php echo ($page <= 1) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" <?php echo ($page >= $total_pages) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Set today's date as default
            $('#selectedDate').val(new Date().toISOString().split('T')[0]);

            // Function to check if a booking overlaps with a time block
            function isOverlapping(blockStart, blockEnd, bookingStart, bookingEnd) {
                return !(bookingEnd <= blockStart || bookingStart >= blockEnd);
            }

            // Function to update availability
            function updateAvailability() {
                const date = $('#selectedDate').val();

                $('.room-row').each(function() {
                    const roomId = $(this).data('room-id');
                    const row = $(this);

                    // AJAX call to get bookings for this room
                    $.get('availability.php', {
                        room_id: roomId,
                        date: date
                    }, function(bookings) {
                        // Reset all blocks to available
                        row.find('.time-block').removeClass('booked partially-booked').addClass('available');

                        // Process each time block
                        row.find('.time-block').each(function() {
                            const blockStart = parseInt($(this).data('start'));
                            const blockEnd = parseInt($(this).data('end'));
                            let isPartiallyBooked = false;
                            let isFullyBooked = false;

                            // Check against all bookings
                            bookings.forEach(function(booking) {
                                const startTime = new Date(`2000-01-01 ${booking.start}`);
                                const endTime = new Date(`2000-01-01 ${booking.end}`);
                                const bookingStart = startTime.getHours();
                                const bookingEnd = endTime.getHours();

                                if (isOverlapping(blockStart, blockEnd, bookingStart, bookingEnd)) {
                                    if (bookingStart <= blockStart && bookingEnd >= blockEnd) {
                                        isFullyBooked = true;
                                    } else {
                                        isPartiallyBooked = true;
                                    }
                                }
                            });

                            // Update block status
                            if (isFullyBooked) {
                                $(this).removeClass('available partially-booked').addClass('booked');
                            } else if (isPartiallyBooked) {
                                $(this).removeClass('available booked').addClass('partially-booked');
                            }

                            // Update tooltip
                            const status = isFullyBooked ? 'Fully Booked' : (isPartiallyBooked ? 'Partially Booked' : 'Available');
                            $(this).attr('title', `${blockStart}:00 - ${blockEnd}:00\n${status}`);
                        });
                    });
                });
            }

            // Date navigation
            $('#prevDay').click(function() {
                const date = new Date($('#selectedDate').val());
                date.setDate(date.getDate() - 1);
                $('#selectedDate').val(date.toISOString().split('T')[0]);
                updateAvailability();
            });

            $('#nextDay').click(function() {
                const date = new Date($('#selectedDate').val());
                date.setDate(date.getDate() + 1);
                $('#selectedDate').val(date.toISOString().split('T')[0]);
                updateAvailability();
            });

            $('#selectedDate').change(updateAvailability);

            // Initialize availability
            updateAvailability();

            // Initialize tooltips
            $('[title]').tooltip();
        });
    </script>
</body>

</html>