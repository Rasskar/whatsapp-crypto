<?php

namespace Rasskar\Crypto\Facades;

use Psr\Http\Message\StreamInterface;
use Rasskar\Crypto\Interfaces\KeyGeneratorInterface;
use Rasskar\Crypto\Services\Encryptor;
use Rasskar\Crypto\Services\Decryptor;

class MediaCrypto
{
    /**
     * @param StreamInterface $stream
     * @param KeyGeneratorInterface $keyGenerator
     * @param string $mediaKey
     * @param string $info
     * @return string
     * @throws \Throwable
     */
    public static function encrypt(
        StreamInterface $stream,
        KeyGeneratorInterface $keyGenerator,
        string $mediaKey,
        string $info
    ): string {
        return (new Encryptor($keyGenerator, $mediaKey, $info))->process($stream);
    }

    /**
     * @param StreamInterface $stream
     * @param KeyGeneratorInterface $keyGenerator
     * @param string $mediaKey
     * @param string $info
     * @return string
     * @throws \Throwable
     */
    public static function decrypt(
        StreamInterface $stream,
        KeyGeneratorInterface $keyGenerator,
        string $mediaKey,
        string $info
    ): string {
        return (new Decryptor($keyGenerator, $mediaKey, $info))->process($stream);
    }
}
