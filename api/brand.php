<?php
include '../model/brand.php';

include_once('connect.php');

$sql = "SELECT * FROM Brand";
$brand_id;

if ((int)basename(strtok($_SERVER["REQUEST_URI"], '?'))) {
    $brand_id = (int)basename(strtok($_SERVER["REQUEST_URI"], '?'));
    $sql = $sql . " WHERE id = $brand_id";
}

$json = file_get_contents('php://input');

switch ($_SERVER["REQUEST_METHOD"]) {
    case "DELETE":
        $response = new Response();
        if (!isset($brand_id)) {
            $response->code = 400;
            $response->status = false;
            $response->message = "id is require";
            die($response->to_json());
        }
        $sql = "DELETE FROM Brand WHERE Brand.id = $brand_id";
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
                $sql_brand = "INSERT INTO Brand (name, logo) VALUES ('" . $data->name . "','" . $file_name . "')";
                if ($conn->query($sql_brand) === FALSE) {
                    die($conn->error);
                    $response->code = 400;
                    $response->status = false;
                    $response->message = $conn->error;
                } else {
                    $brand = new Brand();
                    $brand->id = $conn->insert_id;
                    $brand->name = $data->name;
                    $brand->logo = $file_name;
                    $response->data = $brand;
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
        $sql = "UPDATE Brand SET ";
        if (!isset($brand_id)) {
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

        $sql = $sql . "WHERE Brand.id = $brand_id";
        if ($conn->query($sql) === FALSE) {
            die($conn->error);
            $response->code = 400;
            $response->status = false;
            $response->message = $conn->error;
        } else {
            $brand = new Brand();
            $brand->id = $brand_id;
            $brand->name = $data->name;
            $brand->logo = $file_name;
            $response->data = $brand;
        }
        die($response->to_json());
        break;

    case "GET":
        if ($_GET["category_id"]) {
            $category_id = $_GET["category_id"];
            $sql = "SELECT Brand.id, Brand.name, Brand.logo
            FROM Brand
            INNER JOIN Product ON Product.brand_id = Brand.id
            WHERE Product.category_id = $category_id
            GROUP BY Brand.id";
        }
        print_r(SQLite3::version());
        $result = $conn->query($sql);
        $response = new Response();
        if ($result->num_rows == 0) {
            die($response->to_json());
        } else if ($result->num_rows == 1) {
            $data = $result->fetch_assoc();
            $brand = new Brand();
            $brand->id = (int)$data["id"];
            $brand->name = empty($data["name"]) ? null : $data["name"];
            $brand->logo = empty($data["logo"]) ? null : $data["logo"];

            $response->data = $brand;
            die($response->to_json());
        } else {
            $brands = array();
            while ($data = $result->fetch_assoc()) {
                $brand = new Brand();
                $brand->id = (int)$data["id"];
                $brand->name = empty($data["name"]) ? null : $data["name"];
                $brand->logo = empty($data["logo"]) ? null : $data["logo"];

                array_push($brands, $brand);
            }
            $response->data = $brands;
            die($response->to_json());
        }
        break;
}
