<?php

header("Content-Type: application/json");

// --------------------
// DB CONNECTION
// --------------------
$conn = new mysqli("localhost", "root", "", "project_middleware");

if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

// --------------------
// HELPERS (JSON)
// --------------------
function respond($data) {
    echo json_encode($data);
    exit;
}

// --------------------
// INPUT (JSON body)
// --------------------
$input = json_decode(file_get_contents("php://input"), true);

// --------------------
// METHOD
// --------------------
$method = $_SERVER["REQUEST_METHOD"];

// ======================================================
// CREATE (POST)
// ======================================================
if ($method === "POST") {

    $name = $input["name"] ?? null;
    $number = $input["number"] ?? null;
    $email = $input["email"] ?? null;

    $sql = "INSERT INTO users (name, number, email)
            VALUES ('$name', '$number', '$email')";

    if ($conn->query($sql)) {
        respond([
            "status" => "success",
            "message" => "User created"
        ]);
    } else {
        respond(["error" => $conn->error]);
    }
}

// ======================================================
// READ (GET)
// ======================================================
elseif ($method === "GET") {

    // single user
    if (isset($_GET["id"])) {

        $id = $_GET["id"];

        $result = $conn->query("SELECT * FROM users WHERE id=$id");

        respond([
            "status" => "success",
            "data" => $result->fetch_assoc()
        ]);
    }

    // all users
    $result = $conn->query("SELECT * FROM users");

    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    respond([
        "status" => "success",
        "data" => $users
    ]);
}

// ======================================================
// UPDATE (PUT)
// ======================================================
elseif ($method === "PUT") {

    $id = $input["id"];
    $name = $input["name"];
    $number = $input["number"];
    $email = $input["email"];

    $sql = "UPDATE users
            SET name='$name',
                number='$number',
                email='$email'
            WHERE id=$id";

    if ($conn->query($sql)) {
        respond([
            "status" => "success",
            "message" => "User updated"
        ]);
    } else {
        respond(["error" => $conn->error]);
    }
}

// ======================================================
// DELETE (DELETE)
// ======================================================
elseif ($method === "DELETE") {

    $id = $input["id"];

    $sql = "DELETE FROM users WHERE id=$id";

    if ($conn->query($sql)) {
        respond([
            "status" => "success",
            "message" => "User deleted"
        ]);
    } else {
        respond(["error" => $conn->error]);
    }
}

else {
    respond(["error" => "Invalid method"]);
}