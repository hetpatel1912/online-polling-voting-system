<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Kolkata');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly'=>true, 'secure'=>(getenv('RENDER') === 'true' || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')), 'samesite'=>'Lax']);
session_start();
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
header('Cache-Control: no-store');
function db(): PDO {
    static $db;
    if (!$db) {
        $path = __DIR__.'/config.php';
        $c = require (is_file($path) ? $path : __DIR__.'/config.example.php');
        $db = new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4", $c['username'], $c['password'], DB_PDO_OPTIONS + [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
        $db->exec("SET time_zone = '+05:30'");
    }
    return $db;
}
function query(string $sql, array $args=[]): PDOStatement { $s=db()->prepare($sql); $s->execute($args); return $s; }
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function post(string $key): string { return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : ''; }
function go(string $url='index.php'): void { header('Location: '.$url, true, 303); exit; }
function flash(string $message, string $kind='success'): void { $_SESSION['flash']=[$message,$kind]; }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function token(): string { return '<input type="hidden" name="csrf" value="'.e(csrf()).'">'; }
function user(): ?array {
    if (empty($_SESSION['uid'])) return null;
    $u=query('SELECT id,name,email,role,active FROM users WHERE id=?',[$_SESSION['uid']])->fetch();
    if (!$u || !$u['active']) { unset($_SESSION['uid']); return null; }
    return $u;
}
function require_user(): array { $u=user(); if (!$u) throw new RuntimeException('Please sign in to continue.'); return $u; }
function require_admin(): array { $u=require_user(); if ($u['role']!=='admin') throw new RuntimeException('Administrator access required.'); return $u; }
function status(array $p): string { if ($p['closed'] || strtotime($p['ends_at'])<=time()) return 'Closed'; return strtotime($p['starts_at'])>time() ? 'Upcoming' : 'Active'; }
function date_label(string $d): string { return date('d M Y, g:i a',strtotime($d)).' IST'; }
function read_poll(int $id, bool $lock=false): array {
    $p=query('SELECT * FROM polls WHERE id=?'.($lock?' FOR UPDATE':''),[$id])->fetch();
    if (!$p) throw new RuntimeException('Poll not found.');
    return $p;
}
function poll_input(): array {
    $title=post('title'); $description=post('description'); $category=post('category');
    if (strlen($title)<5 || strlen($title)>180 || strlen($description)>3000 || !in_array($category,['Campus','Academics','Events','Clubs','General'],true)) throw new RuntimeException('Enter a title of 5–180 characters, a valid category, and a description under 3,000 characters.');
    $dates=[];
    foreach (['starts_at','ends_at'] as $key) {
        $raw=post($key); $date=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$raw);
        if (!$date || $date->format('Y-m-d\TH:i')!==$raw) throw new RuntimeException('Enter valid start and end dates.');
        $dates[]=$date->format('Y-m-d H:i:s');
    }
    if ($dates[1]<=$dates[0] || strtotime($dates[1])<=time()) throw new RuntimeException('The deadline must be in the future and after the start time.');
    $options=array_values(array_filter(array_map('trim',explode("\n",post('options'))),fn($v)=>$v!==''));
    if (count($options)<2 || count($options)>8 || count(array_unique(array_map('strtolower',$options)))!==count($options)) throw new RuntimeException('Enter 2–8 different options, one per line.');
    foreach ($options as $o) if (strlen($o)>160) throw new RuntimeException('Each option must be under 160 characters.');
    return [$title,$description,$category,$dates[0],$dates[1],$options];
}
