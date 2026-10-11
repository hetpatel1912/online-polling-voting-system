<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
function ask(string $prompt): string { echo $prompt; return trim((string)fgets(STDIN)); }
$name=ask('Admin name: '); $email=strtolower(ask('Admin email: '));
if(strlen($name)<2 || strlen($name)>100 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190) { fwrite(STDERR,"Enter a valid name and email.\n"); exit(1); }
$hidden=PHP_OS_FAMILY!=='Windows' && function_exists('shell_exec') && function_exists('stream_isatty') && stream_isatty(STDIN);
if ($hidden) { shell_exec('stty -echo'); register_shutdown_function(fn()=>shell_exec('stty echo')); }
try { $password=ask('Admin password (8–72 characters): '); }
finally { if ($hidden) { shell_exec('stty echo'); echo "\n"; } }
if(strlen($password)<8 || strlen($password)>72) { fwrite(STDERR,"Password must be 8–72 characters.\n"); exit(1); }
$c=require (is_file(__DIR__.'/../app/config.php')?__DIR__.'/../app/config.php':__DIR__.'/../app/config.example.php');
try {
 $db=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['username'],$c['password'],DB_PDO_OPTIONS + [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 $s=$db->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,'admin')");
 $s->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
 echo "Administrator created. Sign in on port 8000 with your chosen credentials.\n";
} catch (PDOException $ex) { fwrite(STDERR,"Could not create administrator. Check the database and use an email that is not already registered.\n"); exit(1); }
