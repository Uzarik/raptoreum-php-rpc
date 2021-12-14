<?php

namespace Krevedko\RaptoreumPhpRpc;

class Smartnode extends RPC
{
    public function getWinners(int $count = 50)
    {
        /**
         * $count must be string or error will be thrown
         */
        return $this->result('smartnode', ['winners', (string) $count]);
    }

    public function getList($mode = 'json', $filter = '')
    {
        /**
         * $count must be string or error will be thrown
         */
        return $this->result('smartnodelist', [$mode, $filter]);
    }
}