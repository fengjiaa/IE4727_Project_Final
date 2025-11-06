<?php 
session_start();
$is_post = ($_SERVER['REQUEST_METHOD'] === 'POST');
require __DIR__ . '/api/bootstrap_mysql.php';
$pdo = db();

if ($is_post) {
    $email = trim($_POST['email'] ?? '');
    $pw = $_POST['pw'] ?? '';

    // Fetch account from database
    $stmt = $pdo->prepare("SELECT account_id, full_name, email, password_hash, account_type FROM accounts WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account && password_verify($pw, $account['password_hash'])) {
        // Allow only ADMIN accounts
        if ($account['account_type'] === 'ADMIN') {
            $_SESSION['admin'] = $account;
        } else {
            $error = 'Access denied — only admin accounts can log in.';
        }
    } else {
        $error = 'Invalid email or password.';
    }
}

$admin = $_SESSION['admin'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login — MiraMoo</title>
  <link rel="icon" href="assets/img/logo.png">
  <link rel="stylesheet" href="stylesheet.css">

  <style>
    /* MiraMoo Admin Styling */
    body {
      background-color: #f9f6ff;
    }

    main.container.section {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 40px 15px;
    }

    .card {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 14px rgba(43, 36, 64, 0.1);
      padding: 30px 40px;
      max-width: 520px;
      width: 100%;
      text-align: center;
      border: 1px solid #ece4ff;
      transition: all 0.25s ease;
    }

    .card:hover {
      box-shadow: 0 6px 20px rgba(43, 36, 64, 0.15);
    }

    h1 {
      font-size: 1.8rem;
      color: #2b2440;
      margin-bottom: 15px;
    }

    .welcome-card h2 {
      background: linear-gradient(90deg, #8a68c4, #b497ff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      font-size: 2rem;
      margin-bottom: 10px;
    }

    .welcome-icon {
      font-size: 3rem;
      color: #8a68c4;
      margin-bottom: 10px;
    }

    label {
      text-align: left;
      font-weight: 500;
      color: #2b2440;
    }

    input[type=email],
    input[type=password] {
      width: 100%;
      padding: 10px;
      margin-top: 4px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }

    input:focus {
      border-color: #8a68c4;
      outline: none;
    }

    .btn {
      background: #8a68c4;
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 12px 18px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      margin-top: 15px;
    }

    .btn:hover {
      background: #7353a2;
      box-shadow: 0 3px 8px rgba(115, 83, 162, 0.3);
    }

    .btn-ghost {
      background: transparent;
      color: #8a68c4;
      border: 2px solid #8a68c4;
      border-radius: 10px;
      padding: 12px 18px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      display: inline-block;
      text-decoration: none;
      text-align: center;
      margin-top: 15px;
    }

    .btn-ghost:hover {
      background: #8a68c4;
      color: #fff;
      box-shadow: 0 3px 8px rgba(115, 83, 162, 0.3);
    }

    small {
      display: block;
      margin-top: 10px;
      color: #666;
    }

    .error {
      color: #b00;
      margin-bottom: 10px;
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
      <a href="booking.html">Book Tickets</a>
    </nav>
    <a href="index.html" class="site_centerlogo">
      <img src="assets/img/logo.png" alt="">
    </a>
    <nav class="site_links right">
      <a href="membership.html">Membership</a>
      <a href="about.html">Our Story</a>
      <a href="contact.html">Contact Us</a>
    </nav>
    <button id="nav_toggle" class="nav_toggle" aria-label="Menu"><i></i></button>
  </div>
</header>
<script src="nav.js"></script>

<main class="container section">

<?php if ($admin): ?>
  <!-- Admin Welcome Section -->
  <div class="card welcome-card">
    <div class="welcome-icon">🎬</div>
    <h2>Welcome, <?= htmlspecialchars($admin['full_name']) ?>!</h2>
    <p>You are logged in as <strong><?= htmlspecialchars($admin['email']) ?></strong>.</p>
    <p>Access the admin dashboard to manage your Now Showing schedule, update listings, and keep the MiraMoo magic alive ✨</p>

    <a href="movies-schedule-admin.php" class="btn">Go to Admin Now Showing Page</a>
    <a href="view-submissions.php" class="btn">Go to View Contact Us Submissions</a>
    <a href="logout.php" class="btn-ghost">Log Out</a>
  </div>

<?php else: ?>
  <!-- Login Form -->
  <div class="card">
    <h1>Admin Login</h1>
    <?php if (!empty($error)): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="grid" style="gap: 15px;">
      <label>Email
        <input type="email" name="email" required>
      </label>
      <label>Password
        <input type="password" name="pw" required>
      </label>
      <button class="btn">Log In</button>
      <small>Only admin accounts can access this area.</small>
    </form>
  </div>
<?php endif; ?>
</main>

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
