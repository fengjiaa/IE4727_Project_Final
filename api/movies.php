<?php
// movies.php
header('Content-Type: application/json');
include 'config.php';

// Create a database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Check if connection is successful
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Query the database for all movies with their showtimes
$sql = "
    SELECT 
        m.movie_id, m.movie_name, m.description, m.genre, m.duration_mins, m.poster_url,
        s.showtime_id, s.hall, s.show_date, s.show_time
    FROM movies m
    LEFT JOIN showtimes s ON m.movie_id = s.movie_id
    ORDER BY m.movie_id, s.show_date, s.show_time
";
$result = $conn->query($sql);

// Check for query failure
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed']);
    exit;
}

// Initialize an empty array to store movies with showtimes
$movies = [];
while ($row = $result->fetch_assoc()) {
    $movie_id = $row['movie_id'];

    // If this movie is not yet in the array, add it
    if (!isset($movies[$movie_id])) {
        $movies[$movie_id] = [
            'movie_id' => $row['movie_id'],
            'movie_name' => $row['movie_name'],
            'description' => $row['description'],
            'genre' => $row['genre'],
            'duration_mins' => $row['duration_mins'],
            'poster_url' => $row['poster_url'],
            'showtimes' => []  // Initialize showtimes array
        ];
    }

    // Add the showtime to the movie's showtimes array
    if ($row['showtime_id']) {
        $movies[$movie_id]['showtimes'][] = [
            'showtime_id' => $row['showtime_id'],
            'hall' => $row['hall'],
            'show_date' => $row['show_date'],
            'show_time' => $row['show_time']
        ];
    }
}

// Return the movies with their showtimes as JSON
echo json_encode(array_values($movies)); // We use array_values to re-index the array

// Close the database connection
$conn->close();
?>
