<?php
include 'model/category.php';

include_once('connect.php');

$sql = "SELECT * FROM Category";
$category_id;

if ((int)basename(strtok($_SERVER["REQUEST_URI"], '?'))) {
    $category_id = (int)basename(strtok($_SERVER["REQUEST_URI"], '?'));
    $sql = $sql . " WHERE id = $category_id";
}

$json = file_get_contents('php://input');

switch ($_SERVER["REQUEST_METHOD"]) {
    case "DELETE":
        $response = new Response();
        if (!isset($category_id)) {
            $response->code = 400;
            $response->status = false;
            $response->message = "id is require";
            die($response->to_json());
        }
        $sql = "DELETE FROM Category WHERE Category.id = $category_id";
        if ($conn->query($sql) === FALSE) {
            die($conn->error);
            $response->code = 400;
            $response->status = false;
            $response->message = $conn->error;
        }
        die($response->to_json());
        break;

    case "POST":
        if ($json) {
            define("UPLOAD_DIR", $_SERVER['DOCUMENT_ROOT'] . "/");
            $data = json_decode($json);
            $image_base64 = base64_decode($data->logo);
            $file_name = "uploads/" . uniqid() . ".png";
            $upload_dir = UPLOAD_DIR . $file_name;
            $status = file_put_contents($upload_dir, $image_base64);
            $response = new Response();

            if ($status) {
                $sql_brand = "INSERT INTO Category (name, logo) VALUES ('" . $data->name . "','" . $file_name . "')";
                if ($conn->query($sql_brand) === FALSE) {
                    die($conn->error);
                    $response->code = 400;
                    $response->status = false;
                    $response->message = $conn->error;
                } else {
                    $category = new Category();
                    $category->id = $conn->insert_id;
                    $category->name = $data->name;
                    $category->logo = $file_name;
                    $response->data = $category;
                }
            } else {
                $response->code = 400;
                $response->status = false;
                $response->message = "Upload image failed";
            }
            die($response->to_json());
        }
        break;

    case "PUT":
        $response = new Response();
        $data = json_decode($json);
        $file_name;
        $sql = "UPDATE Category SET ";
        if (!isset($category_id)) {
            $response->code = 400;
            $response->status = false;
            $response->message = "id is require";
            die($response->to_json());
        }
        if ($data->name) {
            $sql = $sql . "name = '" . $data->name . "'";
        }
        if ($data->logo) {
            define("UPLOAD_DIR", $_SERVER['DOCUMENT_ROOT'] . "/");
            $image_base64 = base64_decode($data->logo);
            $file_name = "uploads/" . uniqid() . ".png";
            $upload_dir = UPLOAD_DIR . $file_name;
            $status = file_put_contents($upload_dir, $image_base64);
            if ($status) {
                if ($data->name) {
                    $sql = $sql . ", ";
                }
                $sql = $sql . "logo = '" . $file_name . "' ";
            } else {
                $response->code = 400;
                $response->status = false;
                $response->message = "Upload image failed";
                die($response->to_json());
            }
        }

        $sql = $sql . "WHERE Category.id = $category_id";
        if ($conn->query($sql) === FALSE) {
            die($conn->error);
            $response->code = 400;
            $response->status = false;
            $response->message = $conn->error;
        } else {
            $category = new Category();
            $category->id = $category_id;
            $category->name = $data->name;
            $category->logo = $file_name;
            $response->data = $category;
        }
        die($response->to_json());
        break;

    case "GET":

        if (array_key_exists('brand_id', $_GET) && $_GET["brand_id"]) {
            $brand_id = $_GET["brand_id"];
            $sql = "SELECT Category.id, Category.name, Category.logo
                    FROM Category
                    INNER JOIN Product ON Product.category_id = Category.id
                    WHERE Product.brand_id = $brand_id
                    GROUP BY Category.id";
        }

        $result = $conn->query($sql);
        $response = new Response();
        if ($result->num_rows == 0) {
            die($response->to_json());
        } else if ($result->num_rows == 1) {
            $data = $result->fetch_assoc();
            $category = new Category();
            $category->id = (int)$data["id"];
            $category->name = empty($data["name"]) ? null : $data["name"];
            $category->logo = empty($data["logo"]) ? null : $data["logo"];

            $response->data = $category;
            die($response->to_json());
        } else {
            $categories = array();
            while ($data = $result->fetch_assoc()) {
                $category = new Category();
                $category->id = (int)$data["id"];
                $category->name = empty($data["name"]) ? null : $data["name"];
                $category->logo = empty($data["logo"]) ? null : $data["logo"];

                array_push($categories, $category);
            }
            $response->data = $categories;
            die($response->to_json());
        }
        break;
}
