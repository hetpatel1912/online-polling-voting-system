<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$c=require (is_file(__DIR__.'/../app/config.php')?__DIR__.'/../app/config.php':__DIR__.'/../app/config.example.php');
try {
    $db=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['username'],$c['password'],DB_PDO_OPTIONS + [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $db->exec(file_get_contents(__DIR__.'/../database/schema.sql'));
    echo "Database tables are ready. Existing data has been preserved.\n";
    echo "Next: php scripts/create-admin.php\nThen: php scripts/seed.php (optional example polls)\nOpen forwarded port 8000 to use CampusVote.\n";
} catch (Throwable $ex) {
    fwrite(STDERR,"Installation failed. Check MySQL connection settings and that the database exists.\n".$ex->getMessage()."\n"); exit(1);
}
