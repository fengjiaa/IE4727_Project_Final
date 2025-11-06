<?php
require __DIR__ . '/bootstrap_mysql.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';
$pdo = db();

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$payment = $data['payment'] ?? '';
$isMember = !empty($data['isMember']) ? 1 : 0;
$total = $data['total'] ?? 0;
$details = json_encode($data['details'], JSON_UNESCAPED_UNICODE);

try {
  // for each booking detail, we need to get the movie name, date, time, hall to find showtime_id
  foreach ($data['details'] as $detail) {
    $movieName = $detail['movie'];
    $date = $detail['date'];
    $time = $detail['time'];
    $hall = $detail['hall'];

    $stmt = $pdo->prepare("SELECT s.showtime_id
                          FROM showtimes s
                          JOIN movies m ON s.movie_id = m.movie_id
                          WHERE m.movie_name = ? AND s.show_date = ? AND s.show_time = ? AND s.hall = ?");
    $stmt->execute([$movieName, $date, $time, $hall]);
    $showtimeId = $stmt->fetchColumn();
    if (!$showtimeId) {
      throw new Exception("Showtime not found for $movieName on $date at $time in $hall");
    }
    
    // Insert booking record into DB, booking_id is auto-incremented
    $stmt = $pdo->prepare("INSERT INTO bookings (showtime_id, user_name, email, phone, verification_status, date_created)
                            VALUES (?, ?, ?, ?, 'PENDING', NOW())");
    $stmt->execute([$showtimeId, $name, $email, $phone]);
    // Get the last inserted booking_id
    $bookingId = $pdo->lastInsertId();
    if (!empty($detail['seats'])) {
      foreach ($detail['seats'] as $seat) {
        // Select seat_id based on showtime_id and seat_no
        $stmt = $pdo->prepare("SELECT seat_id FROM seats
                              WHERE showtime_id = ? AND seat_no = ?");
        $stmt->execute([$showtimeId, $seat]);
        $seatId = $stmt->fetchColumn();
        if (!$seatId) {
          throw new Exception("Seat $seat not found for showtime ID $showtimeId");
        }
        // Insert into booking_seats
        $stmt2 = $pdo->prepare("INSERT INTO booking_seats (booking_id, seat_id)
                            VALUES (?, ?)");
        $stmt2->execute([$bookingId, $seatId]);
        // Update seat to booked
        $stmt3 = $pdo->prepare("UPDATE seats
                                SET is_booked = 1
                                WHERE showtime_id = ? AND seat_no = ?");
        $stmt3->execute([$showtimeId, $seat]);
      }
    }
  }
  // build a formatted movie list for the email
  $movieListHTML = "";
  foreach ($data['details'] as $i => $detail) {
    $movieTitle = htmlspecialchars($detail['movie']);
    $showDate   = htmlspecialchars($detail['date']);
    $showTime   = htmlspecialchars($detail['time']);
    $hallName   = htmlspecialchars($detail['hall']);
    $pax        = htmlspecialchars($detail['pax']);
    $seats      = !empty($detail['seats']) ? implode(", ", $detail['seats']) : "N/A";

    $movieListHTML .= "
        <tr>
            <td style='padding:6px 10px;border-bottom:1px solid #eee;'>Movie " . ($i + 1) . "</td>
            <td style='padding:6px 10px;border-bottom:1px solid #eee;'>
                <strong>$movieTitle</strong><br>
                <small>Date: $showDate | Time: $showTime | Hall: $hallName | Pax: $pax | Seats: $seats</small>
            </td>
        </tr>";
  }
  // Compose confirmation email
  $subject = "Your MiraMoo Booking Confirmation";
  $html = "
      <html>
      <body style='font-family:Poppins,sans-serif; color:#2b2440;'>
          <h2>Thank You for Booking with MiraMoo!</h2>
          <p>Hi <strong>$name</strong>,</p>
          <p>We've received your booking successfully. Here are your details:</p>

          <table cellpadding='0' cellspacing='0' border='0' style='border-collapse:collapse;width:100%;max-width:600px;margin:10px 0;font-size:14px;'>
              <thead>
                  <tr style='background:#f8f5ff;color:#8a68c4;'>
                      <th style='text-align:left;padding:8px 10px;'>Item</th>
                      <th style='text-align:left;padding:8px 10px;'>Details</th>
                  </tr>
              </thead>
              <tbody>
                  $movieListHTML
              </tbody>
          </table>
          
          <p><strong>Details:</strong><br>
          Phone: $phone<br>
          Payment Method: $payment<br>
          Total: $$total</p>
          <p>We'll see you soon at MiraMoo Cinemas</p>
          <p style='color:#8a68c4;'>MiraMoo Team</p>
      </body>
      </html>
  ";
  $mail = new PHPMailer(true);
  $sent = false;
  try {
      $mail->isSMTP();
      $mail->Host       = 'smtp.gmail.com';
      $mail->Port       = 587;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->SMTPAuth   = true;
      $mail->Username   = 'fengjia0709@gmail.com';
      $mail->Password   = 'rjfo qzlo mecx ehfg';

      $mail->setFrom('no-reply@miramoo.com', 'MiraMoo Cinemas');
      $mail->addAddress($email);   // recipient
      $mail->Subject = $subject;
      $mail->isHtml(true);
      $mail->Body    = $html;

      $mail->send();
      $sent = true;
  } catch (Exception $inner) {
      $error = $mail->ErrorInfo;
  }
  $success = true;
} catch (Throwable $e) {
  $success = false;
  $error = $e->getMessage();
  file_put_contents(__DIR__ . '/../mail_log.txt', date('c') . " DB error: $error\n", FILE_APPEND);
}
header('Content-Type: application/json');
// Output JSON response
echo json_encode([
    'success' => $success,
    'email_sent to' => $email ?? null,
    'email_content' => $html ?? null,
    'message' => $sent ? 'Email sent successfully' : 'Booking saved but email failed',
    'error' => $error ?? null
]);
?>
