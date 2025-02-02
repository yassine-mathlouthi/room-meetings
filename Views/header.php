<style>
    header {
        font-family: "Playfair Display", serif;
        font-optical-sizing: auto;
        font-style: normal;
        font-size: 20px;
    }

    .navbar-nav .nav-link {
        position: relative;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .navbar-nav .nav-link:hover::after {
        content: "";
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #007bff;
        transition: width 0.3s ease;
    }

    .navbar-nav .nav-link::after {
        content: "";
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 0;
        height: 2px;
        background-color: #007bff;
        transition: width 0.3s ease;
    }
    .logo{
        font-size:40px;
    }
</style>

<header>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" 
      integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" 
      crossorigin="anonymous">
<link rel="stylesheet" href="css/style.css">

    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
            <a class="navbar-brand fw-bold" href="#">Meeting Room Booking</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">Dashboard</a>
                        </li>
                        <?php if (is_admin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="manage_rooms.php">Manage Rooms</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
      
    </nav>
</header>

<!-- Include Bootstrap JS (for responsive navbar) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-ENjdO4Dr2bkBIFxQpeo1ma6QvY09YxFd4Jo0RWK4NAblo8nsTIu9MXJtjBYfQ7hZ" 
        crossorigin="anonymous"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEJRAk7i8z2fK0gEo9z1yDJi45OXYFmjgsuYyAyIYhMZoBz3F2trlf5UAMpzR" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-VhTcY7Joo3lP0HfX7mMGV7sS4t+r8+5T1LO4MiJAZ7hP/2GbVmRWrkk7vwgMkhdz" 
        crossorigin="anonymous"></script>
