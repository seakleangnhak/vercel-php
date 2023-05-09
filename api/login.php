<?php
include_once('connect.php');

$response = new Response();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $response->code = 400;
    $response->status = false;
    $response->message = "Accept only POST method";
    die($response->to_json());
}

$json = file_get_contents('php://input');
$name;
$pass;

if ($json) {
    $data = json_decode($json);
    $name = $data->username;
    $pass = $data->passward;
    
    if (empty($name)) {
        $response->code = 400;
        $response->status = false;
        $response->message = "username is required";
        die($response->to_json());
    }
    if (empty($pass)) {
        $response->code = 400;
        $response->status = false;
        $response->message = "passward is required";
        die($response->to_json());
    }
} else {
    $response->code = 400;
    $response->status = false;
    $response->message = "username and passward are required";
    die($response->to_json());
}

// get all category
$sql = "SELECT * FROM Accounts WHERE name = '$name'";
$result = $conn->query($sql);
$response = new Response();

if ($result) {
    if ($result->num_rows == 0) {
        $response->code = 400;
        $response->status = false;
        $response->message = "username not found";
        die($response->to_json());
    } else {
        $account = new stdClass();
        while ($row = $result->fetch_assoc()) {
            if (password_verify($pass, $row["pass"])) {
                $account->id = (int)$row["id"];
                $account->username = $row["name"];
                $response->message = "Success";
                $response->data = $account;
                die($response->to_json());
            } else {
                $response->code = 400;
                $response->status = false;
                $response->message = "Incorrect passward";
                die($response->to_json());
            }
        }
    }
} else {
    $response->code = 400;
    $response->status = false;
    $response->message = $conn->error;
    $response->data = $sql;
    die($response->to_json());
}

