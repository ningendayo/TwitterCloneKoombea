<?php

namespace api;

class Response
{
    private array $response = [];
    private bool $cleared = false;

    public function __construct()
    {
    }

    public function addValue($key, $value): Response
    {
        $this->response[$key] = $value;
        return $this;
    }

    public function removeValue($key): Response
    {
        unset($this->response[$key]);
        return $this;
    }

    public function printError($message = 'Error no especificado', $error_code = 500)
    {
        header('Content-Type:application/json;utf-8');
        //$this->response = array();
        $this->addValue('status', false);
        $this->addValue('message', $message);
        http_response_code($error_code);
        die(str_replace(['\u0000*', '\u0000'], '', json_encode($this->response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES)));
    }

    public function cleared(): bool
    {
        return sizeof($this->response) == 0;
    }

    public function clearResponse()
    {
        $this->response = [];
    }


    public function printResponse()
    {
        header('Content-Type:application/json;utf-8');
        if (count($this->response) > 0) {
            $this->addValue('status', true);
            $this->response = array_reverse($this->response);
        } else {
            $this->addValue('status', false);
            $this->addValue('message', 'The response object is empty');
        }
        die (json_encode($this->response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES));
    }

}