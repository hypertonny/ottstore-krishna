<?php
 error_reporting(0);
date_default_timezone_set('Asia/Kolkata');
define('DB_SERVER', 'localhost'); //localhost
define('DB_USERNAME', 'root'); // db username
define('DB_PASSWORD', ''); // db password
define('DB_DATABASE', 'ott'); // db name
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$img_url = 'xyz';
?>