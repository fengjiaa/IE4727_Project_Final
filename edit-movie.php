<?php
session_start();
require __DIR__ . '/api/bootstrap_mysql.php';
$pdo = db();

// Ensure admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: account.php');
    exit;
}

$movie_id = intval($_GET['id'] ?? 0);

// Fetch the movie
$stmt = $pdo->prepare("SELECT * FROM movies WHERE movie_id=? LIMIT 1");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
    echo "Movie not found!";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_name = trim($_POST['movie_name']);
    $genre = trim($_POST['genre']);
    $duration_mins = intval($_POST['duration_mins']);
    $poster_url = trim($_POST['poster_url']);
    $description = trim($_POST['description']);

    $stmt = $pdo->prepare("UPDATE movies SET movie_name=?, genre=?, duration_mins=?, poster_url=?, description=? WHERE movie_id=?");
    $stmt->execute([$movie_name, $genre, $duration_mins, $poster_url, $description, $movie_id]);

    header('Location: movies-schedule-admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Movie — MiraMoo Admin</title>
<link rel="icon" href="assets/img/logo.png">
<link rel="stylesheet" href="stylesheet.css">
<style>
  body { background: #ffffff; font-family: sans-serif; }
  .card { max-width: 500px; margin: 40px auto; padding: 20px; border-radius: 10px; box-shadow: 0 3px 8px rgba(43,36,64,0.1); }
  label { display: block; margin-bottom: 10px; }
  input, textarea { width: 100%; padding: 8px; margin-top: 4px; border: 1px solid #ccc; border-radius: 6px; }
  .btn { background: #8a68c4; color: #fff; padding: 10px 18px; border-radius: 8px; border: none; cursor: pointer; }
  .btn:hover { background: #7353a2; }
</style>
</head>
<body>

<!-- Navbar -->
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

<!-- Main Content -->
<main class="container section">
  <div class="admin-header"> <center>
    <h1>Edit Movie</h1>
    <p>Welcome, <strong><?= htmlspecialchars($_SESSION['admin']['full_name']) ?>!</strong> Update movie details below.</p> </center>
  </div>

  <div class="card">
    <h2>Edit Movie</h2>
    <form method="post">
      <label>Movie Name
        <input type="text" name="movie_name" value="<?= htmlspecialchars($movie['movie_name']) ?>" required>
      </label>
      <label>Genre
        <input type="text" name="genre" value="<?= htmlspecialchars($movie['genre']) ?>" required>
      </label>
      <label>Duration (mins)
        <input type="number" name="duration_mins" value="<?= htmlspecialchars($movie['duration_mins']) ?>" required>
      </label>
      <label>Poster URL
        <input type="text" name="poster_url" value="<?= htmlspecialchars($movie['poster_url']) ?>">
      </label>
      <label>Description
        <textarea name="description"><?= htmlspecialchars($movie['description']) ?></textarea>
      </label>
      <button class="btn">Update Movie</button>
    </form>
    <p><a href="movies-schedule-admin.php">← Back to Admin Dashboard</a></p>
  </div>
</main>

<!-- Footer -->
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
