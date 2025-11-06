<?php
require __DIR__.'/bootstrap_mysql.php'; $pdo=db();
$d=json_decode(file_get_contents('php://input'),true) ?: [];
$show=$d['show']??''; $seats=$d['seats']??[]; $email=$d['email']??''; $name=$d['name']??''; $phone=$d['phone']??'';
if(!$show) json_out(['ok'=>false,'msg'=>'show required']); expire_holds($pdo);
if(!is_array($seats)||!count($seats)){
  $pdo->prepare("UPDATE seats SET status='booked', booked_at=?, hold_until=0 WHERE show_id=? AND status='held'")->execute([time(),$show]);
}else{
  $pdo->beginTransaction();
  foreach($seats as $c){
    $pdo->prepare("UPDATE seats SET status='booked', booked_at=?, hold_until=0 WHERE show_id=? AND code=? AND status='held'")->execute([time(),$show,$c]);
  }
  $pdo->commit();
}
$pdo->prepare("INSERT INTO bookings (show_id,seats,name,email,phone,created_at) VALUES (?,?,?,?,?,?)")->execute([$show,implode(',',$seats),$name,$email,$phone,time()]);
if($email){
  $headers="MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: MiraMoo <noreply@miramoo.local>\r\n";
  $subject="MiraMoo Booking Confirmation";
  $body="<h2>MiraMoo Booking Confirmation</h2>
  <p>Thank you for your booking.</p>
  <table cellpadding='8' cellspacing='0' border='1' style='border-collapse:collapse'>
    <tr><th align='left'>Show</th><td>{$show}</td></tr>
    <tr><th align='left'>Seats</th><td>".htmlspecialchars(implode(',', $seats))."</td></tr>
    <tr><th align='left'>Name</th><td>".htmlspecialchars($name)."</td></tr>
    <tr><th align='left'>Email</th><td>".htmlspecialchars($email)."</td></tr>
    <tr><th align='left'>Phone</th><td>".htmlspecialchars($phone)."</td></tr>
  </table><p>See you soon! — MiraMoo</p>";
  @mail($email,$subject,$body,$headers);
}
$rows=$pdo->query("SELECT code,status FROM seats WHERE show_id=".$pdo->quote($show))->fetchAll(PDO::FETCH_ASSOC);
$out=[]; foreach($rows as $r){ $out[$r['code']]=$r['status']; }
json_out(['ok'=>true,'seats'=>$out,'emailed'=>(bool)$email]);
?>