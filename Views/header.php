<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Room Booking</title>

    <!-- CSS Dependencies -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh"
        crossorigin="anonymous">

    <style>
        header {
            font-family: "Playfair Display", serif;
            font-optical-sizing: auto;
            font-style: normal;
        }

        .navbar {
            padding: 1rem 2rem;
            background-color: #ffffff;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-right: 3rem;
            /* Add fixed margin to prevent shifting */
        }

        .navbar-nav .nav-link {
            position: relative;
            font-weight: normal;
            color: #4a5568;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link.active {
            font-weight: 600;
            color: #007bff;
        }

        .navbar-nav .nav-link:hover {
            color: #007bff;
        }

        .navbar-nav .nav-link::after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #007bff;
            transition: width 0.3s ease;
        }

        /* Only show underline on hover, removed .active::after */
        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        .navbar-nav .nav-link.text-danger {
            color: #dc3545 !important;
        }

        .navbar-nav .nav-link.text-danger:hover::after {
            background-color: #dc3545;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }

        .navbar-toggler:focus {
            outline: none;
            box-shadow: none;
        }

        .shadow-sm {
            box-shadow: 0 2px 4px rgba(0, 0, 0, .075) !important;
        }
    </style>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
            <a class="navbar-brand" href="#">SpaceBase</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"
                                href="dashboard.php">Dashboard</a>
                        </li>
                        <?php if (is_admin()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_rooms.php' ? 'active' : ''; ?>"
                                    href="manage_rooms.php">Manage Rooms</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'global-availability.php' ? 'active' : ''; ?>"
                                    href="global_availability.php">Global Availability</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>"
                                href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>"
                                href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha384-KyZXEJRAk7i8z2fK0gEo9z1yDJi45OXYFmjgsuYyAyIYhMZoBz3F2trlf5UAMpzR"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeo1ma6QvY09YxFd4Jo0RWK4NAblo8nsTIu9MXJtjBYfQ7hZ"
        crossorigin="anonymous"></script>
</body>

</html>