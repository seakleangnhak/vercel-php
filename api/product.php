<?php
include 'model/product.php';
include 'model/pagination.php';
include_once('connect.php');

$product_id;

if ((int)basename(strtok($_SERVER["REQUEST_URI"], '?'))) {
    $product_id = (int)basename(strtok($_SERVER["REQUEST_URI"], '?'));
}

$json = file_get_contents('php://input');

switch ($_SERVER["REQUEST_METHOD"]) {
    case "POST":
        if ($json) {
            define("UPLOAD_DIR", $_SERVER['DOCUMENT_ROOT'] . "/");
            $data = json_decode($json);
            $image_base64 = base64_decode($data->images);
            $file_name = "uploads/" . uniqid() . ".png";
            $upload_dir = UPLOAD_DIR . $file_name;
            $status = file_put_contents($upload_dir, $image_base64);
            $response = new Response();

            if ($status) {
                $event_text = str_replace("'", "\\'", $data->event_text);
                $event_text = empty($event_text) ? "null" : "'$event_text'";
                $event_color = str_replace("'", "\\'", $data->event_color);
                $event_color = empty($event_color) ? "null" : "'$event_color'";

                $sql_product = "INSERT INTO Product (type, sku, name, published, short_descr, descr, in_stock, is_disable, event_text, event_color, sale_price, regular_price, images, parent, position, category_id, brand_id) 
                VALUES ('" . $data->type . "', null,'" . str_replace("'", "\\'", $data->name) . "'," . $data->published . ", null ,'" . str_replace("'", "\\'", $data->descr) . "'," . $data->in_stock . "," . $data->is_disable . "," . $event_text . "," . $event_color . ", null," . $data->regular_price . ",'" . $file_name . "', null," . $data->position . "," . $data->category_id . "," . $data->brand_id . ")";

                if ($conn->query($sql_product) !== FALSE) {
                    // $product_id = $conn->insert_id;
                } else {
                    $response->code = 400;
                    $response->status = false;
                    $response->message = $conn->error;
                    $response->data = $sql_product;
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
        $need_comma;
        $sql = "UPDATE Product SET ";
        if (!isset($product_id)) {
            $response->code = 400;
            $response->status = false;
            $response->message = "id is require";
            die($response->to_json());
        }

        if ($data->name) {
            $need_comma = true;
            $sql = $sql . "name = '" . str_replace("'", "\\'", $data->name) . "'";
        }
        if ($data->descr) {
            if ($need_comma) {
                $sql = $sql . ", ";
            }
            $need_comma = true;
            $sql = $sql . "descr = '" . str_replace("'", "\\'", $data->descr) . "'";
        }
        if ($data->regular_price) {
            if ($need_comma) {
                $sql = $sql . ", ";
            }
            $need_comma = true;
            $sql = $sql . "regular_price = " . $data->regular_price;
        }
        if (isset($data->is_disable)) {
            if ($need_comma) {
                $sql = $sql . ", ";
            }
            $need_comma = true;
            $sql = $sql . "is_disable = " . $data->is_disable;
        }
        if (isset($data->in_stock)) {
            if ($need_comma) {
                $sql = $sql . ", ";
            }
            $need_comma = true;
            $sql = $sql . "in_stock = " . $data->in_stock;
        }
        if ($data->event_text) {
            if ($need_comma) {
                $sql = $sql . ", ";
            }
            $need_comma = true;
            $sql = $sql . "event_text = '" . str_replace("'", "\\'", $data->event_text) . "'";
        } else if ($data->event_text == "") {
            if ($need_comma) {
                $sql = $sql . ", ";
            }
            $need_comma = true;
            $sql = $sql . "event_text = null";
        }
        if ($data->event_color) {
            if ($need_comma) {
                $sql = $sql . ", ";
            }
            $need_comma = true;
            $sql = $sql . "event_color = '" . str_replace("'", "\\'", $data->event_color) . "'";
        } else if ($data->event_color == "") {
            if ($need_comma) {
                $sql = $sql . ", ";
            }
            $need_comma = true;
            $sql = $sql . "event_color = null";
        }
        if ($data->category_id) {
            if ($need_comma) {
                $sql = $sql . ", ";
            }
            $need_comma = true;
            $sql = $sql . "category_id = " . $data->category_id;
        }
        if ($data->brand_id) {
            if ($need_comma) {
                $sql = $sql . ", ";
            }
            $need_comma = true;
            $sql = $sql . "brand_id = " . $data->brand_id;
        }
        if ($data->images) {
            define("UPLOAD_DIR", $_SERVER['DOCUMENT_ROOT'] . "/");
            $data = json_decode($json);
            $image_base64 = base64_decode($data->images);
            $file_name = "uploads/" . uniqid() . ".png";
            $upload_dir = UPLOAD_DIR . $file_name;
            $status = file_put_contents($upload_dir, $image_base64);
            if ($status) {
                if ($need_comma) {
                    $sql = $sql . ", ";
                }
                $need_comma = true;
                $sql = $sql . "images = '" . $file_name . "'";
            } else {
                $response->code = 400;
                $response->status = false;
                $response->message = "Upload image failed";
                die($response->to_json());
            }
        }
        $sql = $sql . " WHERE Product.id = $product_id";

        if ($conn->query($sql) === FALSE) {
            $response->code = 400;
            $response->status = false;
            $response->message = $conn->error;
            $response->data = $sql;
        }
        die($response->to_json());
        break;
    case "GET":
        $offset = 0;
        $page = 1;
        $limit = 15;
        $condition = "";

        if (isset($product_id)) {
            $condition = "AND Product.id = $product_id";

            $products = get_product($conn, null, null, $condition)[0];

            $response = new Response();
            $response->data = $products;
            echo $response->to_json();
        } else {
            if (isset($_GET["limit"])) {
                $limit = (int)$_GET["limit"];
            }

            if (isset($_GET["page"])) {
                $page = $_GET["page"] < 0 ? 0 : (int)$_GET["page"];
                $offset = ($page - 1) * $limit;
            }

            if (isset($_GET["category_id"])) {
                $condition = "AND Category.id = " . $_GET["category_id"];
            }

            if (isset($_GET["brand_id"])) {
                $condition .= " AND Brand.id = " . $_GET["brand_id"];
            }

            if (isset($_GET["is_disable"])) {
                $condition .= " AND Product.is_disable = " . (int)$_GET["is_disable"];
            }

            if (isset($_GET["product_name"])) {
                $condition .= " AND Product.name LIKE '%" . $_GET["product_name"] . "%'";
            }

            $sql_count = "SELECT COUNT(*)
                FROM Product
                LEFT JOIN Category ON Product.category_id = Category.id
                LEFT JOIN Brand ON Product.brand_id = Brand.id
                LEFT JOIN Product_Attribute ON Product.id = Product_Attribute.product_id
                LEFT JOIN Attribute ON Product_Attribute.attribute_id = Attribute.id
                WHERE Product.published = TRUE AND NOT Product.type='variation' $condition";

            $count_result = $conn->query($sql_count);
            $total_rows = (int)$count_result->fetch_array()[0];
            $total_pages = ceil($total_rows / $limit);

            $products = get_product($conn, $offset, $limit, $condition);

            $pagination = new Pagination();
            $pagination->current_page = $page;
            $pagination->total_page = $total_pages;
            $pagination->from = $offset;
            $pagination->to = $offset + count($products);
            $pagination->total = $total_rows;
            $pagination->data = $products;

            $response = new Response();
            $response->data = $pagination;
            echo $response->to_json();
        }
        break;
    case "DELETE":
        $response = new Response();
        if (empty($product_id) || $product_id == null) {
            $response->code = 400;
            $response->status = false;
            $response->message = "id is required";
            die($response->to_json());
        }

        $sql = "DELETE FROM Product_Attribute WHERE product_id = $product_id";
        $conn->query($sql);

        $sql = "DELETE FROM Product WHERE id = $product_id";
        if ($conn->query($sql) === FALSE) {
            $response->code = 400;
            $response->status = false;
            $response->message = $conn->error;
            $response->data = $sql;
        }
        die($response->to_json());
        break;
}

function get_product(\mysqli $conn, $offset, $limit, $condition)
{
    global $_GET;
    $sql_proudct = "SELECT Product.id, Product.type, Product.sku, Product.name, Product.short_descr, Product.event_text, Product.event_color, Product.descr, Product.sale_price, Product.regular_price, Product.images, Product.is_disable, Product.in_stock, Product.position, Product.parent, Attribute.name as 'attribute_name', Attribute.value as 'attribute_value', Category.id as 'category_id', Category.name as 'category_name', Category.logo as 'category_logo', Brand.id as 'brand_id', Brand.name as 'brand_name', Brand.logo as 'brand_logo'
                FROM Product
                LEFT JOIN Category ON Product.category_id = Category.id
                LEFT JOIN Brand ON Product.brand_id = Brand.id
                LEFT JOIN Product_Attribute ON Product.id = Product_Attribute.product_id
                LEFT JOIN Attribute ON Product_Attribute.attribute_id = Attribute.id
                WHERE Product.published = TRUE AND NOT Product.type='variation' $condition ORDER BY Product.id DESC ";

    if ($offset !== null && $limit !== null) {
        $sql_proudct = $sql_proudct . " LIMIT $offset, $limit";
    }

    $result = $conn->query($sql_proudct);

    $products = array();
    $variation_id = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $product = new Product();
            $product->id = (int)$row["id"];
            $product->type = $row["type"];
            $product->sku = $row["sku"];
            $product->name = $row["name"];
            $product->short_descr = $row["short_descr"];
            $product->descr = $row["descr"];
            $product->event_text = (empty($row["event_text"]) || $row["event_text"] == null) ? null : $row["event_text"];
            $product->event_color = (empty($row["event_color"]) || $row["event_color"] == null) ? null : $row["event_color"];
            $product->sale_price = $row["sale_price"] == null ? null : (float)$row["sale_price"];
            $product->regular_price = $row["regular_price"] == null ? null : (float)$row["regular_price"];
            $product->images = $row["images"];
            $product->is_disable = (int)$row["is_disable"];
            $product->in_stock = (int)$row["in_stock"];
            $product->position = $row["position"] == null ? null : (int)$row["position"];
            $product->parent = $row["parent"] == null ? null : (int)$row["parent"];
            $product->attribute_name = $row["attribute_name"];
            $product->attribute_value = $row["attribute_value"];
            $product->category_id = $row["category_id"] == null ? null : (int)$row["category_id"];
            $product->category_name = $row["category_name"];
            $product->category_logo = $row["category_logo"];
            $product->brand_id = $row["brand_id"] == null ? null : (int)$row["brand_id"];
            $product->brand_name = $row["brand_name"];
            $product->brand_logo = $row["brand_logo"];

            if ($product->type == 'variable') {
                array_push($variation_id, $product->id);
            }

            array_push($products, $product);
        }

        $sql_variation_proudct = "SELECT Product.id, Product.type, Product.sku, Product.name, Product.short_descr, Product.descr, Product.event_text, Product.event_color, Product.sale_price, Product.regular_price, Product.images, Product.is_disable, Product.in_stock, Product.position, Product.parent, Attribute.name as 'attribute_name', Attribute.value as 'attribute_value', Category.id as 'category_id', Category.name as 'category_name', Category.logo as 'category_logo', Brand.id as 'brand_id', Brand.name as 'brand_name', Brand.logo as 'brand_logo'
                    FROM Product
                    LEFT JOIN Category ON Product.category_id = Category.id
                    LEFT JOIN Brand ON Product.brand_id = Brand.id
                    LEFT JOIN Product_Attribute ON Product.id = Product_Attribute.product_id
                    LEFT JOIN Attribute ON Product_Attribute.attribute_id = Attribute.id
                    WHERE Product.published = TRUE AND Product.type='variation' AND Product.parent IN" . prepare_variation_id($variation_id);

        if (isset($_GET["is_disable"])) {
            $sql_variation_proudct .= " AND Product.is_disable = " . $_GET["is_disable"];
        }

        $result_variation = $conn->query($sql_variation_proudct);
        $conn->close();

        $varition_products = array();
        if ($result_variation->num_rows > 0) {
            while ($row = $result_variation->fetch_assoc()) {
                $varition_product = new Product();
                $varition_product->id = (int)$row["id"];
                $varition_product->type = $row["type"];
                $varition_product->sku = $row["sku"];
                $varition_product->name = $row["name"];
                $varition_product->short_descr = $row["short_descr"];
                $varition_product->descr = $row["descr"];
                $varition_product->event_text = (empty($row["event_text"]) || $row["event_text"] == null) ? null : $row["event_text"];
                $varition_product->event_color = (empty($row["event_color"]) || $row["event_color"] == null) ? null : $row["event_color"];
                $varition_product->sale_price = $row["sale_price"] == null ? null : (float)$row["sale_price"];
                $varition_product->regular_price = $row["regular_price"] == null ? null : (float)$row["regular_price"];
                $varition_product->images = $row["images"];
                $varition_product->is_disable = (int)$row["is_disable"];
                $varition_product->in_stock = (int)$row["in_stock"];
                $varition_product->position = (int)$row["position"];
                $varition_product->parent = $row["parent"] == null ? null : (int)$row["parent"];
                $varition_product->attribute_name = $row["attribute_name"];
                $varition_product->attribute_value = $row["attribute_value"];
                $varition_product->category_id = $row["category_id"] == null ? null : (int)$row["category_id"];
                $varition_product->category_name = $row["category_name"];
                $varition_product->category_logo = $row["category_logo"];
                $varition_product->brand_id = $row["brand_id"] == null ? null : (int)$row["brand_id"];
                $varition_product->brand_name = $row["brand_name"];
                $varition_product->brand_logo = $row["brand_logo"];

                array_push($varition_products, $varition_product);
            }
        }

        return prepare_variable_product($products, $varition_products);
    } else {
        return $products;
    }
}

function prepare_variation_id($variation_id)
{
    $str = "";
    for ($i = 0; $i < count($variation_id); $i++) {
        if ($i == 0 && $i == (count($variation_id) - 1)) {
            $str = "($variation_id[$i])";
        } elseif ($i == 0) {
            $str = "($variation_id[$i]";
        } elseif ($i == (count($variation_id) - 1)) {
            $str = $str . ", $variation_id[$i])";
        } else {
            $str = $str . ", $variation_id[$i]";
        }
    }
    return $str;
}

function prepare_variable_product($products, $varition_products)
{
    $temp = array();
    foreach ($products as $product) {
        if ($product->type == 'variable') {
            $varitions = get_varition_for($varition_products, $product->id);
            $product->variation = $varitions;

            $varition_products = fillter_already_varition_product($varition_products, $product->id);
        }
        array_push($temp, $product);
    }
    return $temp;
}

function get_varition_for($varition_products, $id)
{
    $temp = array();
    foreach ($varition_products as $product) {
        if ($product->parent == $id) {
            array_push($temp, $product);
        }
    }
    usort($temp, "sort_product_by_position");
    return $temp;
}

function sort_product_by_position($a, $b)
{
    return $a->position < $b->position ? -1 : 1;
}

function fillter_already_varition_product($a, $fillter_parent_id)
{
    $temp = array();
    foreach ($a as $product) {
        if ($product->parent != $fillter_parent_id) {
            array_push($temp, $product);
        }
    }
    return $temp;
}
