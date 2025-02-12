<?php

namespace Rasskar\Crypto\Interfaces;

interface KeyGeneratorInterface
{
    /**
     * @param string $mediaKey
     * @param string $info
     * @return void
     */
    public function generate(string $mediaKey, string $info): void;

    /**
     * @return string
     */
    public function getIv(): string;

    /**
     * @return string
     */
    public function getCipherKey(): string;

    /**
     * @return string
     */
    public function getMacKey(): string;
}
