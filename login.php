<?php
include 'includes/config.php';
include 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $query = "SELECT * FROM `users` WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        redirect('dashboard.php');
    } else {
        $errorMessage = "Invalid email or password. Please try again.";
    }
}

include 'views/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Meeting Room Booking System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            background-color: #f5f7fb;
            font-family: 'Inter', sans-serif;
            color: #1a2b3c;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .page-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-card {
            width: 100%;
            max-width: 580px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            padding: 2rem 2rem 1.5rem;
            text-align: center;
        }

        .card-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .error-alert {
            margin: 1rem 1.5rem;
            padding: 1rem;
            border-radius: 12px;
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
        }

        .form-content {
            padding: 1.5rem 2.5rem 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
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
            border-radius: 10px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        .input-group>.form-control:not(:first-child){
            z-index: 1;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
            pointer-events: none;
            z-index: 2;
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.95rem;
            color: white;
            background: #3498db;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-login:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .card-footer {
            background: #f8fafd;
            border-top: 1px solid #e1e8f0;
            padding: 1.25rem;
            text-align: center;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
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

        .card-footer a:hover {
            color: #2980b9;
        }

        @media (max-width: 640px) {
            .page-content {
                padding: 1rem;
            }

            .login-card {
                margin: 0;
            }

            .form-content {
                padding: 1.25rem 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="page-content">
        <div class="login-card">
            <div class="card-header">
                <h3>Welcome Back!</h3>
            </div>
            <?php if (isset($errorMessage)): ?>
                <div class="error-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            <div class="form-content">
                <form method="post">
                    <div class="form-group">
                        <label class="form-label">Work Email</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" class="form-control" name="email" required
                                placeholder="Enter your email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" class="form-control" name="password" required
                                placeholder="Enter your password">
                        </div>
                    </div>
                    <button type="submit" class="btn-login">Sign In</button>
                </form>
            </div>
            <div class="card-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</body>

</html>