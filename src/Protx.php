<?php

namespace Krevedko\RaptoreumPhpRpc;

class Protx extends RPC
{
    public function info(string $protxHash)
    {
        return $this->result('protx', ["info", $protxHash]);
    }

    public function quickSetup(string $txid, int $index, string $ipPort, string $feeAddress)
    {
        return $this->result('protx', ["quick_setup", $txid, (string) $index, $ipPort, $feeAddress]);
    }
}