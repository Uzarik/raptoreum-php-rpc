<?php

namespace Krevedko\RaptoreumPhpRpc;

class RPC
{
    protected $Request;
    protected $raw;

    public function __construct()
    {
        $this->Request = new Request();
    }

    public function result($method, $params = [])
    {
        $this->raw = $this->Request->send($method, $params);
        return $this->raw['result']->result;
    }
}