<?php
include '../model/response.php';

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // should do a check here to match $_SERVER['HTTP_ORIGIN'] to a
    // whitelist of safe domains
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
}
header('Content-Type: application/json');
$header = getallheaders();

// check header
// if ($header['Device'] != "flutter") {
//     die("Need device header");
// }

// $servername = "localhost";
// $username = "u399807655_flutter_db";
// $password = "*MigratioN#06";
// $dbname = "u399807655_flutter_db";

$servername = "localhost";
$dbname = "d20719985_botracomputer";
$username = "id20719985_admin";
$password = "NhAkSLsl^^98";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    $response = new Response(500, false, $conn->connect_error);
    die($response->to_json());
}

// $conn->close();
