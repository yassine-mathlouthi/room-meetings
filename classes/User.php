<?php
class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = ? AND password = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $email, md5($password));
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function register($name, $email, $password) {
        $query = "INSERT INTO " . $this->table . " (name, email, password) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $name, $email, md5($password));
        return $stmt->execute();
    }
}
?>
