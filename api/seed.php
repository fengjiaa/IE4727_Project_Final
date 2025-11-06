<?php
require __DIR__.'/bootstrap_mysql.php'; $pdo=db(); $today=date('Y-m-d');
$seed=[['Moana 2','16:00','Hall 1'],['Inside Out 2','18:30','Hall 2'],['Frozen II Sing-Along','20:45','VIP']];
foreach($seed as $row){[$title,$time,$hall]=$row; $id=$today.'-'.preg_replace('/[^a-z0-9]+/','',strtolower($title)).'-'.str_replace(':','',$time);
$pdo->prepare("INSERT IGNORE INTO showtimes (id,movie_title,date_key,time_str,hall) VALUES (?,?,?,?,?)")->execute([$id,$title,$today,$time,$hall]);
$cnt=$pdo->query("SELECT COUNT(*) FROM seats WHERE show_id='$id'")->fetchColumn();
if(!$cnt){ $pdo->beginTransaction(); for($r=0;$r<10;$r++){ for($c=1;$c<=16;$c++){ $code=chr(65+$r).$c;
$pdo->prepare("INSERT INTO seats (show_id,code,status,hold_until,booked_at) VALUES (?,?,?,?,?)")->execute([$id,$code,'available',0,0]); } } $pdo->commit(); } }
json_out(['ok'=>true,'date'=>$today]);
?>