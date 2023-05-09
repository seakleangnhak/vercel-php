<?php
class Response
{
    public $code;
    public $status;
    public $message;
    public $data;

    public function __construct($code = 200, $status = true, $message = "Ok", $data = null)
    {
        $this->code = $code;
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }

    public function to_json()
    {
        return json_encode($this);
    }
}
