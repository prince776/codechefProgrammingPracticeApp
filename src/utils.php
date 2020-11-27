<?php

define("LIMIT", 20);
define("OFFSET", 0);

class ErrorMsg
{
    public $data;
    public $msg;
}

class JsonData
{
    public $data;
    public $msg;
}

function sendJson($res, $data, $msg = "Data Sent", $status = 200)
{
    $jsonData = new JsonData();
    $jsonData->data = $data;
    $jsonData->msg = $msg;
    return $res->withStatus($status)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($jsonData));
}

function sendError($res, $data, $msg = "Error Occured", $status = 400)
{
    $error = new ErrorMsg();
    $error->data = $data;
    $error->msg = $msg; 
    return $res->withStatus($status)
    ->withHeader('Content-Type', 'application/json')
    ->write(json_encode($error));
}