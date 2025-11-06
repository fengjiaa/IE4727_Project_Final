<?php
require __DIR__.'/config.php';
function db(){
  global $DB_HOST,$DB_USER,$DB_PASS,$DB_NAME;
  static $pdo=null; if($pdo) return $pdo;
  $pdo=new PDO("mysql:host=$DB_HOST;charset=utf8mb4",$DB_USER,$DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
  $pdo->exec("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
  $pdo->exec("USE `$DB_NAME`");
  $pdo->exec("CREATE TABLE IF NOT EXISTS showtimes (id VARCHAR(64) PRIMARY KEY, movie_title VARCHAR(120), date_key DATE, time_str VARCHAR(8), hall VARCHAR(20))");
  $pdo->exec("CREATE TABLE IF NOT EXISTS seats (id INT AUTO_INCREMENT PRIMARY KEY, show_id VARCHAR(64), code VARCHAR(8), status ENUM('available','held','booked') DEFAULT 'available', hold_until INT DEFAULT 0, booked_at INT DEFAULT 0, INDEX(show_id), INDEX(code))");
  $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (id INT AUTO_INCREMENT PRIMARY KEY, show_id VARCHAR(64), seats TEXT, name VARCHAR(120), email VARCHAR(120), phone VARCHAR(40), created_at INT)");
  $pdo->exec("CREATE TABLE IF NOT EXISTS submissions (id INT AUTO_INCREMENT PRIMARY KEY, type VARCHAR(20), name VARCHAR(120), email VARCHAR(120), phone VARCHAR(40), birthday DATE NULL, message TEXT NULL, created_at INT)");
  return $pdo;
}
function json_out($a){ header('Content-Type: application/json'); echo json_encode($a); exit; }
function expire_holds($pdo){ $now=time(); $pdo->exec("UPDATE seats SET status='available', hold_until=0 WHERE status='held' AND hold_until>0 AND hold_until < $now"); }
?>