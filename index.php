<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the active schema
$conn->query("USE $schema");

// Query merchants
$merchants_query = "SELECT * FROM merchants";
$merchants_result = $conn->query($merchants_query);

// Query terminals
$terminals_query = "SELECT * FROM terminals";
$terminals_result = $conn->query($terminals_query);

// Query transactions
$transactions_query = "SELECT * FROM transactions";
$transactions_result = $conn->query($transactions_query);

// Query terminal batch totals
$batch_totals_query = "SELECT * FROM terminal_batch_totals";
$batch_totals_result = $conn->query($batch_totals_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse JSON data from POST body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Update merchant data
    if (isset($data['merchant'])) {
        $merchant = $data['merchant'];
        $update_query = "UPDATE merchants SET name=?, address=? WHERE id=?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssi", $merchant['name'], $merchant['address'], $merchant['id']);
        if ($stmt->execute()) {
            // Query successful
        } else {
            // Query failed, log the error
            error_log("Error updating merchant: " . $stmt->error);
        }
        $stmt->close();
    }
    
    // Insert new terminal data
    if (isset($data['terminal'])) {
        $terminal = $data['terminal'];
        $insert_query = "INSERT INTO terminals (merchant_id, name) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("is", $terminal['merchant_id'], $terminal['name']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Insert new transaction data
    if (isset($data['transaction'])) {
        $transaction = $data['transaction'];
        $insert_query = "INSERT INTO transactions (terminal_id, amount) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("id", $transaction['terminal_id'], $transaction['amount']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Update batch total data
    if (isset($data['batch_total'])) {
        $batch_total = $data['batch_total'];
        $update_query = "UPDATE terminal_batch_totals SET total=? WHERE terminal_id=?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("di", $batch_total['total'], $batch_total['terminal_id']);
        $stmt->execute();
        $stmt->close();
    }
}

// Format data as JSON response
$data = [
    "merchants" => [],
    "terminals" => [],
    "transactions" => [],
    "batch_totals" => []
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

while ($row = mysqli_fetch_assoc($batch_totals_result)) {
    $data["batch_totals"][] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);

// Close connection
mysqli_close($conn);
?>
