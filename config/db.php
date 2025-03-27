<?php
$host = "localhost";
$dbname = "e_responde_db";
$username = "root"; // change if your DB username is different
$password = "";     // add your MySQL password if needed

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional: Set character encoding
    $conn->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
