<?php

namespace Rasskar\Crypto\Services;

use Exception;
use Psr\Http\Message\StreamInterface;
use Rasskar\Crypto\Interfaces\CryptoInterface;
use Rasskar\Crypto\Interfaces\KeyGeneratorInterface;
use Rasskar\Crypto\Validators\InfoValidator;
use Throwable;

class Encryptor implements CryptoInterface
{
    /**
     * @param string $mediaKey
     * @param string $info
     * @param KeyGeneratorInterface $keyGenerator
     */
    public function __construct(
        protected KeyGeneratorInterface $keyGenerator,
        protected string $mediaKey,
        protected string $info,
    ) {
    }

    /**
     * @param StreamInterface $stream
     * @return string
     * @throws Throwable
     */
    public function process(StreamInterface $stream): string
    {
        try {
            // Проверяем что info содержит допустимое значение
            (new InfoValidator())->validate($this->info);

            // Расширяем mediaKey до 112 байт с помощью HKDF и разделяем на ключи
            $this->keyGenerator->generate($this->mediaKey, $this->info);

            // Читаем поток и получаем содержимое файла
            $fileContent = $stream->getContents();
            if (!$fileContent) {
                throw new Exception("Error reading stream.");
            }

            // Шифруем данные с помощью AES-256-CBC
            $encrypted = openssl_encrypt(
                $fileContent,
                'aes-256-cbc',
                $this->keyGenerator->getCipherKey(),
                OPENSSL_RAW_DATA,
                $this->keyGenerator->getIv()
            );
            if (!$encrypted) {
                throw new Exception("Error while encrypting data.");
            }

            // Генерируем MAC (HMAC-SHA256) по `iv + encrypted`
            $mac = substr(
                hash_hmac(
                    'sha256',
                    $this->keyGenerator->getIv() . $encrypted,
                    $this->keyGenerator->getMacKey(),
                    true
                ),
                0,
                10
            );

            return $encrypted . $mac;
        } catch (Throwable $exception) {
            throw $exception;
        }
    }
}
