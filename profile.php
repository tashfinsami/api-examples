<?php

header("Content-Type: application/json");

require_once "db.php";
require_once "vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret = file_get_contents("secret.key");

function respond($data) {
    echo json_encode($data);
    exit;
}

/* =========================
   GET PATH
========================= */
$method = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$script = $_SERVER["SCRIPT_NAME"];
$path = str_replace($script, "", $uri);

/* =========================
   GET TOKEN + VERIFY
   (used by protected routes)
========================= */
function getUserId($secret) {

    $headers = getallheaders();
    $auth = $headers["Authorization"] ?? "";

    if (!$auth) {
        respond(["error" => "Token missing"]);
    }

    $token = str_replace("Bearer ", "", $auth);

    try {
        $decoded = JWT::decode($token, new Key($secret, "HS256"));
        return $decoded->user_id;
    } catch (Exception $e) {
        respond(["error" => "Invalid or expired token"]);
    }
}

/* ======================================================
   GET /me
====================================================== */
if ($method === "GET" && $path === "/me") {

    $id = getUserId($secret);

    $result = $conn->query("
        SELECT id, name, email
        FROM users
        WHERE id=$id
    ");

    respond($result->fetch_assoc());
}

/* ======================================================
   GET /users (all or search by email)
====================================================== */
elseif ($method === "GET" && $path === "/users") {

    $id_dump = getUserId($secret); //for safety check only

    /* search by email */
    if (isset($_GET["email"])) {

        $email = $_GET["email"];

        $result = $conn->query("
            SELECT id, name, email
            FROM users
            WHERE email='$email'
            LIMIT 1
        ");

        $user = $result->fetch_assoc();

        if (!$user) {
            respond(["error" => "User not found"]);
        }

        respond($user);
    }

    /* all users */
    $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
    $limit = isset($_GET["limit"]) ? (int)$_GET["limit"] : 10;

    $page = max(1, $page);
    $limit = max(1, min(50, $limit));

    $offset = ($page - 1) * $limit;

    /* get total users */
    $totalResult = $conn->query("SELECT COUNT(*) as total FROM users");
    $totalRow = $totalResult->fetch_assoc();
    $total = (int)$totalRow["total"];

    $totalPages = max(1, ceil($total / $limit));

    /* backend check */
    if ($page > $totalPages) {
        respond(["error" => "Page out of range",]);
    }

    $result = $conn->query("
        SELECT id, name, email
        FROM users
        LIMIT $limit OFFSET $offset
    ");

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    respond($users);
}

/* ======================================================
   PUT /me
====================================================== */
elseif ($method === "PUT" && $path === "/me") {

    $id = getUserId($secret);

    $input = json_decode(file_get_contents("php://input"), true);

    $name = $input["name"];
    $email = $input["email"];

    $conn->query("
        UPDATE users
        SET name='$name', email='$email'
        WHERE id=$id
    ");

    respond(["message" => "Profile updated"]);
}

/* ======================================================
   DELETE /me
====================================================== */
elseif ($method === "DELETE" && $path === "/me") {

    $id = getUserId($secret);

    $conn->query("DELETE FROM users WHERE id=$id");

    respond(["message" => "Account deleted"]);
}

/* ======================================================
   fallback
====================================================== */
else {
    respond(["error" => "Route not found"]);
}