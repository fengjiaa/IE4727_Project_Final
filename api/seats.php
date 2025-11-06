<?php
require __DIR__ . '/bootstrap_mysql.php';
header('Content-Type: application/json');

try {
  $pdo = db();

  // expected GET parameters
  $movieName = $_GET['movie'] ?? '';
  $date = $_GET['date'] ?? '';
  $time = $_GET['time'] ?? '';
  $hall = $_GET['hall'] ?? '';

  if (!$movieName || !$date || !$time || !$hall) {
    throw new Exception("Missing parameters: movie, date, time, hall are required.");
  }

  // Find showtime_id first
  $stmt = $pdo->prepare("
    SELECT s.showtime_id 
    FROM showtimes s
    JOIN movies m ON s.movie_id = m.movie_id
    WHERE m.movie_name = ? AND s.show_date = ? AND s.show_time = ? AND s.hall = ?
    LIMIT 1
  ");
  $stmt->execute([$movieName, $date, $time, $hall]);
  $showtimeId = $stmt->fetchColumn();

  if (!$showtimeId) {
    throw new Exception("Showtime not found for $movieName ($date $time, Hall $hall)");
  }

  // Get all seats for this showtime
  $stmt = $pdo->prepare("
    SELECT seat_no, is_booked
    FROM seats
    WHERE showtime_id = ?
    ORDER BY seat_no ASC
  ");
  $stmt->execute([$showtimeId]);
  $seats = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'success' => true,
    'showtime_id' => $showtimeId,
    'seats' => $seats
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'error' => $e->getMessage()
  ]);
}
?>
