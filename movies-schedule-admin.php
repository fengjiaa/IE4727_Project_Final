<?php
session_start();
require __DIR__ . '/api/bootstrap_mysql.php';
$pdo = db();

// Restrict access to admins only
if (!isset($_SESSION['admin'])) {
    header('Location: account.php');
    exit;
}

// Handle adding a new movie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_movie'])) {
    $movie_name = trim($_POST['movie_name']);
    $genre = trim($_POST['genre']);
    $duration_mins = intval($_POST['duration_mins']);
    $poster_url = trim($_POST['poster_url']);
    $description = trim($_POST['description']);

    $stmt = $pdo->prepare("INSERT INTO movies (movie_name, genre, duration_mins, poster_url, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$movie_name, $genre, $duration_mins, $poster_url, $description]);
    $movie_id = $pdo->lastInsertId();

    // Optionally add an initial showtime
    if (!empty($_POST['show_date']) && !empty($_POST['show_time']) && !empty($_POST['hall'])) {
        $stmt2 = $pdo->prepare("INSERT INTO showtimes (movie_id, hall, show_date, show_time) VALUES (?, ?, ?, ?)");
        $stmt2->execute([$movie_id, $_POST['hall'], $_POST['show_date'], $_POST['show_time']]);
    }

    header('Location: movies-schedule-admin.php');
    exit;
}

// Handle deleting a movie
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM movies WHERE movie_id=?")->execute([$id]);
    header('Location: movies-schedule-admin.php');
    exit;
}

// Fetch movies
$movies = $pdo->query("SELECT * FROM movies ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MiraMoo — Admin Now Showing</title>
<link rel="icon" href="assets/img/logo.png">
<link rel="stylesheet" href="stylesheet.css">
<style>
/* MiraMoo Admin Dashboard Styling */
body {
  background: #ffffff; /* white background */
}
.container.section {
  max-width: 1100px;
}
.admin-header {
  text-align: center;
  margin-bottom: 30px;
}
.admin-header h1 {
  color: #000000; /* black heading */
  font-size: 2.2rem;
  font-weight: 700;
}
.btn {
  background: #8a68c4;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 18px;
  cursor: pointer;
  transition: all 0.3s ease;
}
.btn:hover {
  background: #7353a2;
}
.btn-danger {
  background: #c44a68;
}
.btn-danger:hover {
  background: #a63a56;
}
.table td a.btn {
  margin-right: 8px; /* space between buttons */
}

.table td a.btn:last-child {
  margin-top: 10px; /* remove margin from the last button */
}
.card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 3px 8px rgba(43,36,64,0.1);
  padding: 20px;
  margin-bottom: 20px;
}
form.add-movie {
  display: grid;
  gap: 15px;
  grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
  align-items: end;
}
form.add-movie input, select, textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-family: inherit;
}
.table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 25px;
}
.table th, .table td {
  border-bottom: 1px solid #eee;
  text-align: left;
  padding: 10px;
}
.table th {
  background: #f3ebff;
  color: #2b2440;
}
.poster-thumb {
  width: 80px;
  border-radius: 6px;
  object-fit: cover;
}
.logout-btn {
  display: inline-block;
  margin-top: 10px;
  color: #8a68c4;
  font-weight: 600;
}
</style>
</head>
<body>
<header class="site_hdr">
  <div class="container nav">
    <nav class="site_links left">
      <div class="site_brand">MiraMoo</div>
      <a href="index.html">Home</a>
      <a href="movies-schedule.html">Now Showing</a>
    </nav>
    <a href="index.html" class="site_centerlogo"><img src="assets/img/logo.png" alt=""></a>
    <nav class="site_links right">
      <a href="logout.php" class="logout-btn">Log Out</a>
    </nav>
    <button id="nav_toggle" class="nav_toggle" aria-label="Menu"><i></i></button>
  </div>
</header>
<script src="nav.js"></script>

<main class="container section">
  <div class="admin-header">
    <h1>Admin — Manage Now Showing</h1>
    <p>Welcome, <strong><?= htmlspecialchars($_SESSION['admin']['full_name']) ?>!</strong> Add or update movies currently showing at MiraMoo.</p>
  </div>

  <!-- Add New Movie -->
  <div class="card">
    <h2>Add New Movie</h2>
    <form method="post" class="add-movie">
      <input type="hidden" name="add_movie" value="1">
      <label>Movie Name <input type="text" name="movie_name" required></label>
      <label>Genre <input type="text" name="genre" required></label>
      <label>Duration (mins) <input type="number" name="duration_mins" required></label>
      <label>Poster URL <input type="text" name="poster_url" placeholder="assets/img/posters/movie.jpg"></label>
      <label>Description <textarea name="description" rows="2" placeholder="Short summary..."></textarea></label>
      <label>Show Date <input type="date" name="show_date"></label>
      <label>Show Time <input type="time" name="show_time"></label>
      <label>Hall <input type="text" name="hall" placeholder="Hall 1"></label>
      <button class="btn">Add Movie</button>
    </form>
  </div>

  <!-- Movie List -->
  <div class="card">
    <h2>Current Movies</h2>
    <?php if (empty($movies)): ?>
      <p>No movies found. Add one above!</p>
    <?php else: ?>
    <table class="table">
      <tr>
        <th>Poster</th>
        <th>Movie Name</th>
        <th>Genre</th>
        <th>Duration</th>
        <th>Description</th>
        <th>Actions</th>
      </tr>
      <?php foreach ($movies as $m): ?>
      <tr>
        <td><img src="<?= htmlspecialchars($m['poster_url']) ?>" alt="" class="poster-thumb"></td>
        <td><?= htmlspecialchars($m['movie_name']) ?></td>
        <td><?= htmlspecialchars($m['genre']) ?></td>
        <td><?= htmlspecialchars($m['duration_mins']) ?> min</td>
        <td><?= htmlspecialchars($m['description']) ?></td>
        <td>
          <a href="edit-movie.php?id=<?= $m['movie_id'] ?>" class="btn">Edit</a>
          <a href="movies-schedule-admin.php?delete=<?= $m['movie_id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this movie?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
  </div>
</main>

<div> <center>
    <!-- Back Button -->
    <a href="account.php" class="back-btn" style="margin-bottom: 20px;">← Back to Admin Dashboard</a></center>
</div>

<footer class="site_ftr">
    <div class="container cols">
        <div class="footer_brand">
            <img src="assets/img/logo.png" alt="MiraMoo Logo" class="footer_logo">
            <div class="brand">MiraMoo</div>
            <p class="tagline">“Look. Laugh. Love — again.”</p>
            <p>A pastel-hued world where animated stories bring people together.</p>
        </div>
        <div>
            <h4>Quick Links</h4>
            <ul class="footer_links">
                <li><a href="index.html">Home</a></li>
                <li><a href="movies-schedule.html">Now Showing</a></li>
                <li><a href="booking.html">Book Tickets</a></li>
                <li><a href="membership.html">Membership</a></li>
                <li><a href="about.html">Our Story</a></li>
                <li><a href="contact.html">Contact Us</a></li>
                <li><a href="account.php">Admin Login</a></li>
            </ul>
        </div>
        <div>
            <h4>Visit Us</h4>
            <address>
                📍 MiraMoo Theatre<br>
                123 Dream Street, Pastel Park<br>
                Singapore, 222222<br><br>
                📞 (000) 123-4567<br>
                📧 <a href="mailto:hello@miramoo.com">hello@miramoo.com</a><br><br>
                🕒 <strong>Hours:</strong><br>
                Mon–Fri: 12 PM – 9 PM<br>
                Sat–Sun: 10 AM – 10 PM
            </address>
        </div>
        <div>
            <h4>Connect With Us</h4>
            <ul class="social_links">
                <li>Instagram — <a href="https://instagram.com/miramoo.cinema" target="_blank">@miramoo.cinema</a></li>
                <li>Facebook — <a href="https://facebook.com/miramoo" target="_blank">/miramoo</a></li>
                <li>TikTok — <a href="https://tiktok.com/@miramoo.magic" target="_blank">@miramoo.magic</a></li>
            </ul>
            <p class="community_note">
                Join the MiraMoo community — where families, friends, and dreamers gather to relive the magic of animation together.
            </p>
        </div>
    </div>

    <div class="footer_bottom">
        <p>© 2025 MiraMoo Cinemas — All rights reserved. |
            <a href="/privacy-policy">Privacy Policy</a> |
            <a href="/terms">Terms of Use</a>
        </p>
    </div>
</footer>
</body>
</html>
