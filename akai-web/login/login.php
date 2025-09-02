<?php
session_start();
require_once '../databasehelper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new DatabaseHelper();

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic input validation
    if (empty($username) || empty($password)) {
        die('Please fill both username and password.');
    }

    // Fetch user from DB
    $stmt = $db->pdo->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Password correct, set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $hash = password_hash($password, PASSWORD_DEFAULT);


        $_SESSION['token'] = '$2y$10$yumC4x7Y0SpdlUfsCAEeUOrtNqNOkL2qFSkBBJA9Fg4Phm2jaazSW';


        // Redirect or success message
        header('Location: ../dashboard');
        exit;
    } else {
        header("Location: ../login?msg=invalid username or password");
    }
} else {
    // Show a simple login form if accessed via GET
    echo '<form method="POST" action="">
            <input name="username" placeholder="Username" required><br>
            <input name="password" type="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
          </form>';
}
?>