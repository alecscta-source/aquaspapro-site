<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
function reply(int $status, bool $ok, string $message): never { http_response_code($status); echo json_encode(['ok'=>$ok,'message'=>$message], JSON_UNESCAPED_UNICODE); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') reply(405,false,'Metodă nepermisă.');
if (!empty($_POST['website'] ?? '')) reply(200,true,'Cererea a fost trimisă.');
$started=(int)($_POST['started_at'] ?? 0); if ($started < 1 || (int)(microtime(true)*1000)-$started < 2500) reply(429,false,'Te rugăm să verifici datele și să încerci din nou.');
$allowed=['Audit tehnic','Automatizări','Soluții digitale','Solicitare generală'];
$service=trim((string)($_POST['serviciu']??'')); if(!in_array($service,$allowed,true)) $service='Solicitare generală';
$name=trim((string)($_POST['nume']??'')); $phone=trim((string)($_POST['telefon']??'')); $email=filter_var(trim((string)($_POST['email']??'')),FILTER_VALIDATE_EMAIL); $message=trim((string)($_POST['mesaj']??'')); $page=trim((string)($_POST['pagina']??''));
if(empty($_POST['consimtamant']) || mb_strlen($name)<2 || mb_strlen($name)>100 || !preg_match('/^[0-9+().\-\s]{7,30}$/',$phone) || !$email || mb_strlen($message)<5 || mb_strlen($message)>2000) reply(422,false,'Completează corect toate câmpurile obligatorii.');
$ip=$_SERVER['REMOTE_ADDR']??'unknown'; $rateFile=sys_get_temp_dir().'/aquaspapro_'.hash('sha256',$ip).'.rate'; $now=time(); $last=is_file($rateFile)?(int)file_get_contents($rateFile):0; if($now-$last<30) reply(429,false,'Ai trimis recent o cerere. Așteaptă 30 de secunde.'); @file_put_contents($rateFile,(string)$now,LOCK_EX);
$safeName=str_replace(["\r","\n"],' ',$name); $safePage=preg_replace('/[^a-zA-Z0-9_\/.\-]/','',$page);
$body="Solicitare nouă AquaSpa Pro\n\nServiciu: $service\nPagina: $safePage\nNume: $safeName\nTelefon: $phone\nEmail: $email\n\nMesaj:\n$message\n\nConsimțământ GDPR: Da\n";
$subject='=?UTF-8?B?'.base64_encode('[AquaSpa Pro] '.$service).'?=';
$headers=['MIME-Version: 1.0','Content-Type: text/plain; charset=UTF-8','From: AquaSpa Pro <office@aquaspapro.ro>','Reply-To: '.$safeName.' <'.$email.'>','X-Mailer: PHP/'.PHP_VERSION];
if(!mail('office@aquaspapro.ro',$subject,$body,implode("\r\n",$headers))) reply(500,false,'Cererea nu a putut fi trimisă acum. Te rugăm să suni la 0778.756.192.');
reply(200,true,'Cererea a fost trimisă. Te contactăm în cel mai scurt timp.');
