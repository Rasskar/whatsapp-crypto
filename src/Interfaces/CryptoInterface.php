<?php

namespace Rasskar\Crypto\Interfaces;

use Psr\Http\Message\StreamInterface;

interface CryptoInterface
{
    /**
     * @param StreamInterface $stream
     * @return string
     */
    public function process(StreamInterface $stream): string;
}
