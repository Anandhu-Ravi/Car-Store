<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'car_parts_store';

$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
}

$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "<div class='alert alert-success'>Database created successfully</div><br>";
} else {
    echo "<div class='alert alert-danger'>Error creating database: " . $conn->error . "</div><br>";
}

$conn->select_db($dbname);

// create table
$table_sql = "CREATE TABLE IF NOT EXISTS car_parts (
    CarPartID INT(11) AUTO_INCREMENT PRIMARY KEY,
    CarPartName VARCHAR(255) NOT NULL,
    CarPartDescription TEXT NOT NULL,
    QuantityAvailable INT(11) NOT NULL,
    Price DECIMAL(10, 2) NOT NULL,
    ProductAddedBy VARCHAR(100) NOT NULL DEFAULT 'Anandhu Ravi',
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($table_sql) === TRUE) {
    echo "<div class='alert alert-success'>Table 'car_parts' created successfully</div><br>";
} else {
    echo "<div class='alert alert-danger'>Error creating table: " . $conn->error . "</div><br>";
}
?>
