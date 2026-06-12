<?php

$conn = new mysqli("localhost", "root", "", "project_middleware");

if ($conn->connect_error) {
    header("Content-Type: application/json");
    die(json_encode(["error" => "DB connection failed"]));
}

$conn->set_charset("utf8mb4");