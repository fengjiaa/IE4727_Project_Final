<?php
// get_showtimes.php
header('Content-Type: application/json');
include 'config.php';

// Validate movie_id
if (!isset($_GET['movie_id']) || empty($_GET['movie_id'])) {
    echo json_encode(['error' => 'Movie ID is required']);
    exit;
}

$movie_id = (int) $_GET['movie_id'];

// Connect to Database
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Debug Info – Which database and what data PHP sees
$debug = [];
$db_result = $conn->query("SELECT DATABASE() AS db_name");
if ($db_result && $row = $db_result->fetch_assoc()) {
    $debug['connected_db'] = $row['db_name'];
}

$date_check = $conn->query("SELECT MIN(show_date) AS min_date, MAX(show_date) AS max_date, COUNT(*) AS total FROM showtimes");
if ($date_check && $row = $date_check->fetch_assoc()) {
    $debug['table_summary'] = [
        'total_rows' => $row['total'],
        'min_date' => $row['min_date'],
        'max_date' => $row['max_date']
    ];
}

// Uncomment to debug connection info in browser
// echo json_encode($debug, JSON_PRETTY_PRINT); exit;

// Query: Get showtimes for a specific movie
$sql = "
    SELECT show_date,
           DATE_FORMAT(show_time, '%H:%i') AS show_time,
           hall
    FROM showtimes
    WHERE movie_id = ?
      AND show_date >= '2025-12-01'
    ORDER BY show_date, show_time
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

// Build JSON Response
if ($result->num_rows > 0) {
    $showtimes = [];
    while ($row = $result->fetch_assoc()) {
        $showtimes[] = [
            'show_date' => $row['show_date'],
            'show_time' => $row['show_time'],
            'hall'      => $row['hall']
        ];
    }
    echo json_encode($showtimes, JSON_PRETTY_PRINT);
} else {
    echo json_encode(['message' => 'No showtimes found for this movie']);
}

// Cleanup
$stmt->close();
$conn->close();
?>
