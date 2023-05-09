<?php
include 'model/product.php';
include 'model/category.php';
include_once('connect.php');

if ($_SERVER["REQUEST_METHOD"] != "GET") {
    $response = new Response();
    die($response->to_json());
}

// get all category
$sql = "SELECT * FROM Category";
$result = $conn->query($sql);
$response = new Response();
if ($result->num_rows == 0) {
    die($response->to_json());
} else {
    $categories = array();
    while ($data = $result->fetch_assoc()) {
        $category = new Category();
        $category->id = (int)$data["id"];
        $category->name = empty($data["name"]) ? null : $data["name"];
        $category->logo = empty($data["logo"]) ? null : $data["logo"];

        $condition = "AND Category.id = " . $category->id . " AND Product.is_disable = 0";
        $category->products = get_product($conn, 0, 10, $condition);

        array_push($categories, $category);
    }
    $response->data = $categories;
    die($response->to_json());
}

function get_product(\mysqli $conn, $offset, $limit, $condition)
{
    $sql_proudct = "SELECT Product.id, Product.type, Product.sku, Product.name, Product.short_descr, Product.descr, Product.in_stock, Product.event_text, Product.event_color, Product.sale_price, Product.regular_price, Product.images, Product.is_disable, Product.position, Product.parent, Attribute.name as 'attribute_name', Attribute.value as 'attribute_value', Category.id as 'category_id', Category.name as 'category_name', Category.logo as 'category_logo', Brand.id as 'brand_id', Brand.name as 'brand_name', Brand.logo as 'breand_logo'
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
            $product->in_stock = (int)$row["in_stock"];
            $product->event_text = (empty($row["event_text"]) || $row["event_text"] == null) ? null : $row["event_text"];
            $product->event_color = (empty($row["event_color"]) || $row["event_color"] == null) ? null : $row["event_color"];
            $product->sale_price = $row["sale_price"] == null ? null : (float)$row["sale_price"];
            $product->regular_price = $row["regular_price"] == null ? null : (float)$row["regular_price"];
            $product->images = $row["images"];
            $product->is_disable = (int)$row["is_disable"];
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

        $sql_variation_proudct = "SELECT Product.id, Product.type, Product.sku, Product.name, Product.short_descr, Product.descr, Product.in_stock, Product.event_text, Product.event_color, Product.sale_price, Product.regular_price, Product.images, Product.is_disable, Product.position, Product.parent, Attribute.name as 'attribute_name', Attribute.value as 'attribute_value', Category.id as 'category_id', Category.name as 'category_name', Category.logo as 'category_logo', Brand.id as 'brand_id', Brand.name as 'brand_name', Brand.logo as 'breand_logo'
                    FROM Product
                    LEFT JOIN Category ON Product.category_id = Category.id
                    LEFT JOIN Brand ON Product.brand_id = Brand.id
                    LEFT JOIN Product_Attribute ON Product.id = Product_Attribute.product_id
                    LEFT JOIN Attribute ON Product_Attribute.attribute_id = Attribute.id
                    WHERE Product.published = TRUE AND Product.type='variation' AND Product.parent IN" . prepare_variation_id($variation_id);

        $result_variation = $conn->query($sql_variation_proudct);
        // $conn->close();

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
                $varition_product->in_stock = (int)$row["in_stock"];
                $varition_product->event_text = (empty($row["event_text"]) || $row["event_text"] == null) ? null : $row["event_text"];
                $varition_product->event_color = (empty($row["event_color"]) || $row["event_color"] == null) ? null : $row["event_color"];
                $varition_product->sale_price = $row["sale_price"] == null ? null : (float)$row["sale_price"];
                $varition_product->regular_price = $row["regular_price"] == null ? null : (float)$row["regular_price"];
                $varition_product->images = $row["images"];
                $varition_product->is_disable = (int)$row["is_disable"];
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
