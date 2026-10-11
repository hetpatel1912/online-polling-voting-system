<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
$c=require __DIR__.'/../app/config.example.php';
$db=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['username'],$c['password'],DB_PDO_OPTIONS + [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
if ($db->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn()>0) { echo "Existing administrator preserved.\n"; exit; }
$email=strtolower(trim(getenv('ADMIN_EMAIL')?:''));
$password=getenv('ADMIN_PASSWORD')?:'';
if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190 || strlen($password)<12 || strlen($password)>72) {
 fwrite(STDERR,"First deployment requires ADMIN_EMAIL and an ADMIN_PASSWORD of 12–72 characters in Render environment settings.\n"); exit(1);
}
$s=$db->prepare("INSERT INTO users (name,email,password_hash,role) VALUES ('Administrator',?,?,'admin')");
$s->execute([$email,password_hash($password,PASSWORD_DEFAULT)]);
echo "Initial administrator created. Remove ADMIN_PASSWORD from the Render environment after successful setup.\n";
