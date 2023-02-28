<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load the .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database credentials
$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$schema = $_ENV['DB_SCHEMA'];


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
