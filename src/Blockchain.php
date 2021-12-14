<?php

namespace Krevedko\RaptoreumPhpRpc;

class Blockchain extends RPC
{
    public function getBlockCount()
    {
        return $this->result('getblockcount');
    }
}