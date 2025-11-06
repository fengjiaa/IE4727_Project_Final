<?php
session_start();
require __DIR__ . '/api/bootstrap_mysql.php';
$pdo = db(); // Assuming db() returns a PDO connection

// Restrict access to admins only
if (!isset($_SESSION['admin']) || $_SESSION['admin']['account_type'] !== 'ADMIN') {
    header('Location: account.php');
    exit;
}

// Fetch all contact submissions, newest first
$stmt = $pdo->query("SELECT * FROM contact ORDER BY submitted_at DESC");
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MiraMoo — View Submissions</title>
<link rel="icon" href="assets/img/logo.png">
<link rel="stylesheet" href="stylesheet.css">
<style>
/* MiraMoo Admin Page Styling */
body {
    background: #ffffff;
    font-family: 'Arial', sans-serif;
}
.container.section {
    max-width: 1100px;
    margin: 0 auto;
    padding: 20px;
}
.admin-header {
    text-align: center;
    margin-bottom: 30px;
}
.admin-header h1 {
    color: #2b2440;
    font-size: 2rem;
    font-weight: 700;
}
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
.table th, .table td {
    border-bottom: 1px solid #eee;
    padding: 12px 10px;
    text-align: left;
}
.table th {
    background: #f3ebff;
    color: #2b2440;
}
.card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 3px 8px rgba(43,36,64,0.1);
    padding: 20px;
}
.logout-btn, .back-btn {
    display: inline-block;
    margin-top: 10px;
    color: #8a68c4;
    font-weight: 600;
    text-decoration: none;
}
.logout-btn:hover, .back-btn:hover {
    text-decoration: underline;
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
        <h1>Admin — Contact Form Submissions</h1>
        <p>Welcome, <strong><?= htmlspecialchars($_SESSION['admin']['full_name']) ?>!</strong> Here are all submissions from the Contact Us form.</p>
    
    </div>

    <div class="card">
        <?php if (empty($submissions)): ?>
            <p>No submissions yet.</p>
        <?php else: ?>
            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Submitted At</th>
                </tr>
                <?php foreach ($submissions as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['contact_id']) ?></td>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= nl2br(htmlspecialchars($s['message'])) ?></td>
                        <td><?= htmlspecialchars($s['submitted_at']) ?></td>
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
