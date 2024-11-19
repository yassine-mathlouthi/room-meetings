<?php
class Booking {
    private $conn;
    private $table = "bookings";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createBooking($user_id, $room_id, $start_time, $end_time) {
        $query = "INSERT INTO " . $this->table . " (user_id, room_id, start_time, end_time) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiss", $user_id, $room_id, $start_time, $end_time);
        return $stmt->execute();
    }
}
?>
