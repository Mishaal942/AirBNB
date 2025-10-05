<?php
// db.php
// Database connection (use this include in all PHP files)
$DB_HOST = 'localhost';
$DB_USER = 'uppbmi0whibtc';
$DB_PASS = 'bjgew6ykgu1v';
$DB_NAME = 'db6rsy0yn28pzg';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("DB Connection failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
