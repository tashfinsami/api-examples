<?php

session_start();
header("Content-Type: application/json");

require_once "db.php";

function respond($data) {
    echo json_encode($data);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$script = $_SERVER["SCRIPT_NAME"];

$path = str_replace($script, "", $uri);

/* ======================================================
   GET /me
====================================================== */
if ($method === "GET" && $path === "/me") {

    if (!isset($_SESSION["user_id"])) {
        respond(["error" => "Not logged in"]);
    }

    $id = $_SESSION["user_id"];

    $result = $conn->query("
        SELECT id, name, email
        FROM users
        WHERE id=$id
    ");

    respond($result->fetch_assoc());
}

/* ======================================================
   GET /users (all OR search by email)
====================================================== */
elseif ($method === "GET" && $path === "/users") {

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
    $result = $conn->query("SELECT id, name, email FROM users");

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    respond($users);
}

/* ======================================================
   PUT /me (update self)
====================================================== */
elseif ($method === "PUT" && $path === "/me") {

    if (!isset($_SESSION["user_id"])) {
        respond(["error" => "Not logged in"]);
    }

    $input = json_decode(file_get_contents("php://input"), true);

    $id = $_SESSION["user_id"];
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

    if (!isset($_SESSION["user_id"])) {
        respond(["error" => "Not logged in"]);
    }

    $id = $_SESSION["user_id"];

    $conn->query("DELETE FROM users WHERE id=$id");

    session_destroy();

    respond(["message" => "Account deleted"]);
}

/* ======================================================
   fallback
====================================================== */
else {
    respond(["error" => "Route not found"]);
}