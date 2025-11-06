<?php
require __DIR__.'/bootstrap_mysql.php'; $pdo=db();
$d=json_decode(file_get_contents('php://input'),true) ?: [];
$show=$d['show']??''; $seats=$d['seats']??[];
if(!$show||!is_array($seats)||!count($seats)) json_out(['ok'=>false,'msg'=>'invalid params']); expire_holds($pdo);
$until=time()+8*60; $ok=true; $pdo->beginTransaction();
foreach($seats as $code){
  $stmt=$pdo->prepare("UPDATE seats SET status='held', hold_until=? WHERE show_id=? AND code=? AND status='available'");
  $stmt->execute([$until,$show,$code]); if(!$stmt->rowCount()) $ok=false;
}
$pdo->commit();
$rows=$pdo->query("SELECT code,status FROM seats WHERE show_id=".$pdo->quote($show))->fetchAll(PDO::FETCH_ASSOC); $out=[];
foreach($rows as $r){ $out[$r['code']]=$r['status']; }
json_out(['ok'=>$ok,'seats'=>$out]);
?>