<?php
class Pagination
{
    public $current_page;
    public $total_page;
    public $from;
    public $to;
    public $total;
    public $data;

    public function to_string()
    {
        return json_encode($this);
    }
}
