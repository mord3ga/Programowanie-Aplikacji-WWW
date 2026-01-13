<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "bookstore";

$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$link) {
    error_log('Database connection error');
}
if (!mysqli_select_db($link, $dbname)) {
    error_log('Database selection error');
}

$admin_login = 'admin';
$admin_pass = 'admin';