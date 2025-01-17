<?php
include 'includes/config.php';
include 'includes/functions.php';

if (!is_logged_in()) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

if (!isset($_GET['room_id']) || !isset($_GET['date'])) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

$room_id = $_GET['room_id'];
$date = $_GET['date'];

function get_room_availability($room_id, $date) {
    global $conn;

    $query = "SELECT start_time, end_time 
              FROM bookings 
              WHERE room_id = ? 
              AND date = ?
              ORDER BY start_time";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $room_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $bookings = array();
    while ($row = $result->fetch_assoc()) {
        $bookings[] = array(
            'start' => $row['start_time'],
            'end' => $row['end_time']
        );
    }

    return $bookings;
}

$availability = get_room_availability($room_id, $date);
header('Content-Type: application/json');
echo json_encode($availability);