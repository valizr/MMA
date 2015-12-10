<?php
$servername = "https://mobile-site.ro";
$username = "mobilesi_mma";
$password = "trencad1$";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//futai de baza de date
echo "Connected successfully";
?> 