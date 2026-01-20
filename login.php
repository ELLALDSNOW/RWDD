<?php

session_start();


$DEBUG = true;
if ($DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}


ob_start();

include 'conn.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($DEBUG) error_log('login.php: POST received');

    $login = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = 'Please enter both username and password.';
        if ($DEBUG) error_log('login.php: missing username or password');
    } else {
 
        $sql = "SELECT user_id, user_name, email_address, password_hash, user_type
                FROM user_account
                WHERE user_name = ? OR email_address = ?
                LIMIT 1";

        $stmt = mysqli_prepare($dbConn, $sql);
        if ($stmt === false) {

            error_log('login.php: prepare failed: ' . mysqli_error($dbConn));
            $error = 'Database error. Please try again later.';
        } else {
            mysqli_stmt_bind_param($stmt, 'ss', $login, $login);
            $exec = mysqli_stmt_execute($stmt);
            if ($exec === false) {
                error_log('login.php: execute failed: ' . mysqli_stmt_error($stmt));
                $error = 'Database error. Please try again later.';
                mysqli_stmt_close($stmt);
            } else {
                $result = mysqli_stmt_get_result($stmt);
                if ($result === false) {
                    error_log('login.php: get_result failed (mysqli built without mysqlnd?), trying bind_result fallback');

                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) === 1) {
                        mysqli_stmt_bind_result($stmt, $user_id, $user_name, $email_address, $password_hash, $user_type);
                        mysqli_stmt_fetch($stmt);
                        $user = [
                            'user_id' => $user_id,
                            'user_name' => $user_name,
                            'email_address' => $email_address,
                            'password_hash' => $password_hash,
                            'user_type' => $user_type,
                        ];
                    } else {
                        $user = null;
                    }
                } else {
                    $user = mysqli_fetch_assoc($result);
                }

                mysqli_stmt_close($stmt);

                if ($user) {
                    $storedPassword = $user['password_hash'] ?? '';
                    $valid = false;

  
                    if ($storedPassword !== '' && password_verify($password, $storedPassword)) {
                        $valid = true;
                    } elseif ($password === $storedPassword) {
      
                        $valid = true;
                    }

                    if ($valid) {
            
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['user_name'] = $user['user_name'];
                        $_SESSION['user_type'] = $user['user_type'];

              
                        $up = mysqli_prepare($dbConn, "UPDATE user_account SET last_login = NOW() WHERE user_id = ?");
                        if ($up) {
                            mysqli_stmt_bind_param($up, 'i', $user['user_id']);
                            mysqli_stmt_execute($up);
                            mysqli_stmt_close($up);
                        } else {
                            error_log('login.php: failed to prepare last_login update: ' . mysqli_error($dbConn));
                        }

                 
                        header('Location: personal.php');
                  
                        ob_end_flush();
                        exit;
                    } else {
                        $error = 'Invalid credentials.';
                        if ($DEBUG) error_log('login.php: invalid credentials for login=' . $login);
                    }
                } else {
                    $error = 'User not found.';
                    if ($DEBUG) error_log('login.php: user not found for login=' . $login);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Page</title>
  <link rel="stylesheet" href="login.css">
  <style>
    .form-error { color:#fff; background:#e74c3c; padding:10px; margin-bottom:12px; border-radius:6px; }
  </style>
</head>
<body>

<header class="signup-header">
  <div class="logo-section">
    <a href="index.html"><img src="images/Group 24.png" alt="Hive Logo" class="logo"></a>
    <span class="brand">HIVE</span>
  </div>
</header>

<h1>Login</h1>
<div class="login-container">
  <h2>Login</h2>
  <?php if (!empty($error)): ?>
    <div class="form-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form id="loginForm" method="POST" action="">
    <div class="form-group">
      <label for="username">Username or Email</label>
      <input type="text" id="username" name="username" placeholder="Enter username or email" required>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter password" required>
    </div>

    <button type="submit"><b>Login</b></button>
  </form>

  <div class="links">

    <a href="signup.php">Signup</a>
  </div>
</div>

<script>
document.getElementById("loginForm").addEventListener("submit", function(e) {
  const u = document.getElementById("username").value.trim();
  const p = document.getElementById("password").value.trim();
  if (!u || !p) {
    e.preventDefault();
    alert("Please fill in both username and password ❌");
  } else {

    console.log('login form submitting for', u);
  }
});
</script>

</body>
</html>