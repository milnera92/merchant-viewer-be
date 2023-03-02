<?php

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database credentials
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$schema = getenv('DB_SCHEMA');

// Create connection
$conn = mysqli_connect($host, $username, $password, $dbname, $port);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set the active schema
mysqli_query($conn, "USE $schema");

// Query merchants
$merchants_query = "SELECT * FROM merchants";
$merchants_result = mysqli_query($conn, $merchants_query);

// Query terminals
$terminals_query = "SELECT * FROM terminals";
$terminals_result = mysqli_query($conn, $terminals_query);

// Query transactions
$transactions_query = "SELECT * FROM transactions";
$transactions_result = mysqli_query($conn, $transactions_query);

// Format data as JSON response
$data = [
    "merchants" => [],
    "terminals" => [],
    "transactions" => []
];

while ($row = mysqli_fetch_assoc($merchants_result)) {
    $data["merchants"][] = $row;
}

while ($row = mysqli_fetch_assoc($terminals_result)) {
    $data["terminals"][] = $row;
}

while ($row = mysqli_fetch_assoc($transactions_result)) {
    $data["transactions"][] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);

// Close connection
mysqli_close($conn);
?>
