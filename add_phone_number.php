<?php
// add_phone_number.php

$servername = "localhost";
$username = "root"; // Change to your DB username
$password = "";     // Change to your DB password
$dbname = "cafe_app"; // Change to your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add phone_number column to orders table
$alterOrdersTable = "ALTER TABLE orders ADD phone_number VARCHAR(15) NULL";

if ($conn->query($alterOrdersTable) === TRUE) {
    echo "Phone_number column added successfully to orders table.";
} else {
    echo "Error adding phone_number column: " . $conn->error;
}

$conn->close();
?>