<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isOfficial()) {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: home.php');
    }
    exit();
}

$error = '';
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE (username = '$username' OR email = '$username') AND is_active = 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // For demo: simple password check
        // In production, use: password_verify($password, $user['password'])
        if (verifyPassword($password, $user['password'] || ($password === 'demo123' && $user['username'] === 'demo'))) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_type'] = $user['user_type'];
            
            if ($user['user_type'] === 'official') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: home.php');
            }
            exit();
        } else {
            $error = 'Invalid username or password!';
        }
    } else {
        $error = 'Invalid username or password!';
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $address = $conn->real_escape_string($_POST['address']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } else if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } else {
        // Check if username or email already exists
        $check_sql = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $error = 'Username or email already exists!';
        } else {
            // In production: $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $hashed_password = hashPassword($password);// For demo only
            
            // Insert new user
            $insert_sql = "INSERT INTO users (full_name, address, email, phone, username, password) 
                          VALUES ('$full_name', '$address', '$email', '$phone', '$username', '$hashed_password')";
            
            if ($conn->query($insert_sql)) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Dahat - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .auth-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .welcome-section {
            flex: 1;
            background: linear-gradient(135deg, rgba(13, 74, 158, 0.9) 0%, rgba(30, 107, 196, 0.9) 100%);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            color: #0d4a9e;
        }

        .logo-text h1 {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .logo-text p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .welcome-content h2 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .welcome-content p {
            margin-bottom: 30px;
            line-height: 1.6;
            opacity: 0.9;
        }

        .features {
            list-style: none;
            margin-top: 30px;
        }

        .features li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .features i {
            color: #ff7e30;
        }

        .form-section {
            flex: 1;
            padding: 50px;
            overflow-y: auto;
            max-height: 700px;
        }

        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: #0d4a9e;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #666;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d4a9e;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 74, 158, 0.1);
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 74, 158, 0.3);
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #ff7e30 0%, #ff9a52 100%);
        }

        .switch-form {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .switch-form a {
            color: #0d4a9e;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .switch-form a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                max-height: 90vh;
            }

            .welcome-section {
                padding: 30px;
                text-align: center;
            }

            .form-section {
                padding: 30px;
            }

            .logo {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .welcome-section, .form-section {
                padding: 20px;
            }

            .welcome-content h2 {
                font-size: 1.8rem;
            }

            .form-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="welcome-section">
            <div class="logo">
                <div class="logo-icon">BD</div>
                <div class="logo-text">
                    <h1>Barangay Dahat</h1>
                    <p>Progress Through Unity</p>
                </div>
            </div>
            
            <div class="welcome-content">
                <h2>Welcome to Our Community Portal</h2>
                <p>Access barangay services, stay updated with events, and connect with officials through our secure online platform.</p>
                
                <ul class="features">
                    <li><i class="fas fa-check-circle"></i> Request barangay clearance online</li>
                    <li><i class="fas fa-check-circle"></i> Report concerns securely</li>
                    <li><i class="fas fa-check-circle"></i> Stay updated with events</li>
                    <li><i class="fas fa-check-circle"></i> Real-time chat support</li>
                    <li><i class="fas fa-check-circle"></i> Track your requests</li>
                </ul>
            </div>
        </div>
        
        <div class="form-section">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <div class="form-container active" id="loginForm">
                <div class="form-header">
                    <h2>Resident Login</h2>
                    <p>Access your account to continue</p>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="login" value="1">
                    
                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <input type="text" id="username" name="username" class="form-control" required placeholder="Enter your username or email">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
                    </div>
                    
                    <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember" style="display: inline; margin-left: 5px;">Remember me</label>
                        </div>
                        <a href="#" style="color: #0d4a9e; text-decoration: none; font-weight: 500;">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="btn">Login</button>
                    
                    <div class="switch-form">
                        <p>Don't have an account? <a id="showRegister">Register here</a></p>
                        <p style="margin-top: 10px;">
                            <small>Official login? <a href="official_login.php">Click here</a></small>
                        </p>
                    </div>
                </form>
            </div>
            
            <!-- Registration Form -->
            <div class="form-container" id="registerForm">
                <div class="form-header">
                    <h2>Create Account</h2>
                    <p>Register as a resident to access services</p>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="register" value="1">
                    
                    <div class="form-group">
                        <label for="reg_full_name">Full Name</label>
                        <input type="text" id="reg_full_name" name="full_name" class="form-control" required placeholder="Juan Dela Cruz">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_address">Complete Address</label>
                        <input type="text" id="reg_address" name="address" class="form-control" required placeholder="Street, Zone, Barangay Dahat">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_email">Email Address</label>
                        <input type="email" id="reg_email" name="email" class="form-control" required placeholder="juandelacruz@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_phone">Phone Number</label>
                        <input type="tel" id="reg_phone" name="phone" class="form-control" required placeholder="09123456789">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_username">Username</label>
                        <input type="text" id="reg_username" name="username" class="form-control" required placeholder="Choose a username">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_password">Password</label>
                        <input type="password" id="reg_password" name="password" class="form-control" required placeholder="Create a strong password">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_confirm_password">Confirm Password</label>
                        <input type="password" id="reg_confirm_password" name="confirm_password" class="form-control" required placeholder="Confirm your password">
                    </div>
                    
                    <div class="form-group">
                        <input type="checkbox" id="agree_terms" name="agree_terms" required>
                        <label for="agree_terms" style="display: inline; margin-left: 5px;">
                            I agree to the <a href="#" style="color: #0d4a9e;">terms and conditions</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary">Register</button>
                    
                    <div class="switch-form">
                        <p>Already have an account? <a id="showLogin">Login here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Switch between login and register forms
        document.getElementById('showRegister').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('loginForm').classList.remove('active');
            document.getElementById('registerForm').classList.add('active');
        });
        
        document.getElementById('showLogin').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('registerForm').classList.remove('active');
            document.getElementById('loginForm').classList.add('active');
        });
        
        // Form validation
        const registerForm = document.querySelector('#registerForm form');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                const password = document.getElementById('reg_password').value;
                const confirmPassword = document.getElementById('reg_confirm_password').value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    return false;
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long!');
                    return false;
                }
                
                return true;
            });
        }
    </script>
</body>
</html>