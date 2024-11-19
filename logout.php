<?php
// Start the session
session_start();

// Destroy the session and redirect to the login page
session_unset();  // Unsets all session variables
session_destroy();  // Destroys the session

// Redirect the user to the login page
header('Location: login.php');
exit();
?>
