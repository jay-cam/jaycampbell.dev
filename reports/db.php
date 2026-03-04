<?php
$mysqli = new mysqli(
    "127.0.0.1",
    "collector_cse135",
    "iH@t8Cse135!@",
    "collector"
);

if ($mysqli->connect_error) {
    die("Database connection failed");
}
?>