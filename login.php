<?php
/*
 * 🔒 Spond Manager - Created by Belli Dev
 * © 2025 Belli Dev. All rights reserved.
 * You are not allowed to copy, modify, redistribute, or sell this software
 * without explicit written permission from the author.
 * Violators will be prosecuted under applicable laws.
 */



session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $spond_username = $_POST['spond_username'] ?? '';
    $spond_password = $_POST['spond_password'] ?? '';
    
    if ($username && $password) {
        $user = authenticateUser($pdo, $username, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['spond_username'] = $spond_username;
            $_SESSION['spond_password'] = $spond_password;
            
            // Save Spond credentials securely
            saveUserSpondCredentials($pdo, $user['id'], $spond_username, $spond_password);
            
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid credentials';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spond Manager - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo h1 {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            width: 100%;
        }
        .watermark {
            position: fixed;
            bottom: 20px;
            right: 20px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            z-index: 1000;
        }
        .spond-info {
            background: rgba(102, 126, 234, 0.1);
            border-left: 4px solid #667eea;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h1><i class="fas fa-users-cog"></i> Spond Manager</h1>
                <p class="text-muted">Attendance Management System</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="spond-info">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Enter your application credentials and Spond login details for synchronization.
                    </small>
                </div>

                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                </div>

                <hr class="my-4">
                <h6 class="text-center text-muted mb-3">Spond Integration</h6>

                <div class="form-floating">
                    <input type="text" class="form-control" id="spond_username" name="spond_username" placeholder="Spond Username">
                    <label for="spond_username"><i class="fas fa-envelope"></i> Spond Username/Email</label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="spond_password" name="spond_password" placeholder="Spond Password">
                    <label for="spond_password"><i class="fas fa-key"></i> Spond Password</label>
                </div>

                <button type="submit" class="btn btn-primary btn-login mt-3">
                    <i class="fas fa-sign-in-alt"></i> Login & Sync
                </button>
            </form>

            <div class="text-center mt-3">
                <small class="text-muted">
                    Don't have an account? <a href="register.php">Register here</a>
                </small>
            </div>
        </div>
    </div>

    <!-- Watermark -->
    <div class="watermark">
        Created By <strong>Belli Dev</strong>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>