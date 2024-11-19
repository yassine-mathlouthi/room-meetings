<?php
class Room {
    private $conn;
    private $table = "rooms";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getRooms() {
        $query = "SELECT * FROM " . $this->table;
        return $this->conn->query($query);
    }
}
?>
