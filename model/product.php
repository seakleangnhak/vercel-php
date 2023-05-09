<?php
class Product
{
    public $id;
    public $type;
    public $name;
    public $sku;
    public $short_descr;
    public $descr;
    public $event_text;
    public $event_color;
    public $sale_price;
    public $regular_price;
    public $between_print;
    public $images;
    public $is_disable;
    public $attribute_name;
    public $attribute_value;
    public $category_id;
    public $category_name;
    public $category_logo;
    public $brand_id;
    public $brand_name;
    public $brand_logo;
    public $position;
    public $parent;
    public $variation;

    public function to_json()
    {
        return json_encode($this);
    }
}
