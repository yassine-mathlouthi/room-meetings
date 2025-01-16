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
?>

<?php include 'views/header.php'; ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap');
     body {
        background: url('https://wallpaperswide.com/download/white_abstract_background-wallpaper-1920x1080.jpg') no-repeat center center fixed;
        background-size: cover;
        font-family: "Playfair Display", serif;
        font-optical-sizing: auto;
        font-weight: <weight>;
        font-style: normal;
        color: #fff;
    }
    
    .card1 {
        background: #ffffff;
        border-radius: 15px;
        box-shadow: rgba(0, 0, 0, 0.25) 0px 54px 55px, rgba(0, 0, 0, 0.12) 0px -12px 30px, rgba(0, 0, 0, 0.12) 0px 4px 6px, rgba(0, 0, 0, 0.17) 0px 12px 13px, rgba(0, 0, 0, 0.09) 0px -3px 5px;
        overflow: hidden;
    }   
    .card-header1 {
        background-color: #93bdcc;
        color: white;
        padding: 10px;
        font-size: 1.5rem;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        font-family: "Playfair Display", serif;
        font-optical-sizing: auto;
        font-weight: <weight>;
        font-style: normal;
    }
    .form-label {
        color: #333;
        font-weight: 200;
    }
    .form-control {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        font-family: "Playfair Display", serif;
        font-optical-sizing: auto;
        font-weight: <weight>;
        font-style: normal;
    }
    .btn-primary {
        background: linear-gradient(90deg, #93bdcc, #2575fc);
        border: none;
        padding: 10px;
        font-size: 1rem;
        font-weight: bold;
        border-radius: 8px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        transition: background 0.3s;
        font-family: "Playfair Display", serif;
        font-optical-sizing: auto;
        font-weight: <weight>;
        font-style: normal;
    }
    .btn-primary:hover {
        background: linear-gradient(90deg, #2575fc, #6a11cb);
    }
    .card-footer {
    color: #333; /* Set a darker color for footer text */
}
    .card-footer a {
        color: #2575fc;
        font-weight: bold;
        text-decoration: none;
        transition: color 0.3s;
        font-family: "Playfair Display", serif;
        font-optical-sizing: auto;
        font-weight: <weight>;
        font-style: normal;
    }
    .card-footer a:hover {
        color: #6a11cb;
    }
    .font{
        font-family: "Playfair Display", serif;
        font-optical-sizing: auto;
        font-weight: <weight>;
        font-style: normal;
    }
</style>

<body>


    <div class="container mt-5">
        
        <div class="row justify-content-center">
            <div class="col-md-10">
           
                <div class="card1">
                    <div class="card-header1 text-center">
                        <h3>Login</h3>
                    </div>
                    <?php if (isset($errorMessage)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <?php echo $errorMessage; ?>
                     </div>
                     <?php endif; ?>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="email" class=" font form-label">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="font form-label">Password:</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
