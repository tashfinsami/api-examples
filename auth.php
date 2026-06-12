<?php

session_start();
header("Content-Type: application/json");

require_once "db.php";

function respond($data) {
    echo json_encode($data);
    exit;
}

/* =========================
   GET CLEAN PATH
========================= */
$method = $_SERVER["REQUEST_METHOD"];

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$script = $_SERVER["SCRIPT_NAME"];

$path = str_replace($script, "", $uri);

/* get JSON body */
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   ROUTES
========================= */

/* ---------- SIGNUP ---------- */
if ($path === "/signup" && $method === "POST") {

    $name = $data["name"];
    $email = $data["email"];
    $password = password_hash($data["password"], PASSWORD_DEFAULT);

    $conn->query("
        INSERT INTO users(name,email,password)
        VALUES('$name','$email','$password')
    ");

    respond(["message" => "Signup successful"]);
}

/* ---------- SIGNIN ---------- */
if ($path === "/login" && $method === "POST") {

    $email = $data["email"];
    $password = $data["password"];

    $result = $conn->query("
        SELECT * FROM users WHERE email='$email'
    ");

    $user = $result->fetch_assoc();

    if (!$user || !password_verify($password, $user["password"])) {
        respond(["error" => "Invalid credentials"]);
    }

    $_SESSION["user_id"] = $user["id"];

    respond([
        "message" => "Login successful",
        "user_id" => $user["id"]
    ]);
}

/* ---------- LOGOUT ---------- */
if ($path === "/logout" && $method === "POST") {

    session_start();

    session_destroy();

    respond([
        "message" => "Logged out"
    ]);
}

/* ---------- fallback ---------- */
respond(["error" => "Route not found"]);