<?php

exit();
require_once 'databasehelper.php';

$db = new DatabaseHelper();

$username = 'treppi';
$password = 'treppi';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
$stmt->execute([$username, $hash]);

echo "User created.";

?>