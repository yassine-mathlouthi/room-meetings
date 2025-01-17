<?php
include 'includes/config.php';
include 'includes/functions.php';
include 'views/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', 'user')";
    if ($conn->query($query)) {
        redirect('login.php');
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Meeting Room Booking System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            background-color: #f5f7fb;
            font-family: 'Inter', sans-serif;
            color: #1a2b3c;
        }

        /* Add this to handle the navbar */
        body {
            display: flex;
            flex-direction: column;
        }

        .split-layout {
            display: flex;
            flex: 1;
            /* This makes it take remaining space */
            min-height: 0;
            /* This prevents overflow */
        }

        .features-section {
            flex: 0 0 30%;
            background: #2c3e50;
            color: white;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .welcome-title {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: white;
        }

        .features-list {
            display: grid;
            gap: 1.5rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            transition: transform 0.2s ease;
        }

        .feature-item:hover {
            transform: translateX(5px);
        }

        .feature-icon {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .feature-content {
            flex: 1;
        }

        .feature-title {
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .feature-description {
            font-size: 0.8rem;
            opacity: 0.8;
            margin: 0;
        }

        .form-section {
            flex: 1;
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
        }

        .registration-card {
            width: 100%;
            max-width: 480px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e1e8f0;
        }

        .card-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .form-content {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            display: block;
        }

        .input-group {
            position: relative;
            width: 100%;
        }

        .form-control {
            width: 100%;
            border: 1px solid #e1e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);

        }

        .input-group>.form-control:not(:first-child) {
            z-index: 1;

        }

        .input-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
            font-size: 0.875rem;
            z-index: 2;
        }

        .btn-register {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
            font-size: 0.875rem;
            transition: background-color 0.2s ease;
        }

        .btn-register:hover {
            background: #2980b9;
            color: white;

        }

        .btn-register:focus,
        .btn-register:active {
            outline: none;
            /* Remove default focus outline, if desired */
            background: #1c6ca1;
            /* Slightly darker for active state */
            color: white;
        }

        .card-footer {
            background: #f8fafd;
            border-top: 1px solid #e1e8f0;
            padding: 1rem;
            text-align: center;
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
        }

        .card-footer p {
            margin: 0;
            color: #64748b;
            font-size: 0.875rem;
        }

        .card-footer a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.75rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        @media (max-width: 768px) {
            .split-layout {
                flex-direction: column;
            }

            .features-section {
                padding: 1.5rem;
            }

            .form-section {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="split-layout">
        <div class="features-section">
            <h2 class="welcome-title">Welcome to SpaceBase</h2>
            <div class="features-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="feature-title">Instant Booking</h4>
                        <p class="feature-description">Reserve rooms in seconds with our streamlined system</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-sync"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="feature-title">Real-time Availability</h4>
                        <p class="feature-description">See live updates on room availability</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="feature-title">Smart Analytics</h4>
                        <p class="feature-description">Optimize your space utilization with insights</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="registration-card">
                <div class="card-header">
                    <h3>Create Account</h3>
                </div>
                <div class="form-content">
                    <form method="post" id="registerForm">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Work Email</label>
                            <div class="input-group">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" class="form-control" name="password" id="password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-register">Create Account</button>
                    </form>
                </div>
                <div class="card-footer">
                    <p>Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#registerForm').on('submit', function(e) {
                const password = $('#password').val();
                const email = $('input[name="email"]').val();
                const name = $('input[name="name"]').val();

                $('.error-message').remove();

                if (password.length < 8) {
                    e.preventDefault();
                    $('#password').parent().after('<div class="error-message"><i class="fas fa-exclamation-circle"></i>Password must be at least 8 characters</div>');
                }

                if (name.length < 2) {
                    e.preventDefault();
                    $('input[name="name"]').parent().after('<div class="error-message"><i class="fas fa-exclamation-circle"></i>Please enter your full name</div>');
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    $('input[name="email"]').parent().after('<div class="error-message"><i class="fas fa-exclamation-circle"></i>Please enter a valid email address</div>');
                }
            });
        });
    </script>
</body>

</html>