<?php
header('Content-Type: application/json');
include 'config.php';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Fetch all showtimes (used for the global date filter)
$sql = "SELECT movie_id, show_date FROM showtimes WHERE show_date >= '2025-12-01'";
$result = $conn->query($sql);

$showtimes = [];
while ($row = $result->fetch_assoc()) {
    $showtimes[] = $row;
}

echo json_encode($showtimes);
$conn->close();
?>
