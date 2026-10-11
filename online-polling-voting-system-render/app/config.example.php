<?php
// Optional TLS with verification for an externally hosted MySQL database.
$ca = getenv('DB_SSL_CA') ?: '';
if ($ca !== '' && !is_readable($ca)) {
    throw new RuntimeException('DB_SSL_CA must point to a readable CA certificate.');
}
if (!defined('DB_PDO_OPTIONS')) {
    define('DB_PDO_OPTIONS', $ca !== '' ? [
        PDO::MYSQL_ATTR_SSL_CA => $ca,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
    ] : []);
}

return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'campus_vote',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
];
