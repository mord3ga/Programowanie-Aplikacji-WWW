<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "bookstore";

$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$link) {
    echo '<b>Connection error:</b>';
}
if (!mysqli_select_db($dbname)) {
    echo '<b>Database error:</b>';
}
