<?php

namespace Rasskar\Crypto\Services;

use Exception;
use Psr\Http\Message\StreamInterface;
use Rasskar\Crypto\Interfaces\KeyGeneratorInterface;
use Rasskar\Crypto\Interfaces\CryptoInterface;
use Rasskar\Crypto\Validators\InfoValidator;
use Throwable;

class Decryptor implements CryptoInterface
{
    /**
     * @param KeyGeneratorInterface $keyGenerator
     * @param string $mediaKey
     * @param string $info
     */
    public function __construct(
        protected KeyGeneratorInterface $keyGenerator,
        protected string $mediaKey,
        protected string $info
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

            // Читаем зашифрованные данные
            $fileContent = $stream->getContents();
            if (!$fileContent) {
                throw new Exception("Error reading stream.");
            }

            // Разделяем `fileContent` на `encrypted` и `mac`
            $encrypted = substr($fileContent, 0, -10);
            $mac = substr($fileContent, -10);

            // Генерируем новый MAC (HMAC-SHA256) и проверяем, совпадает ли он с переданным MAC
            $calculatedMac = substr(
                hash_hmac(
                    'sha256',
                    $this->keyGenerator->getIv() . $encrypted,
                    $this->keyGenerator->getMacKey(),
                    true
                ),
                0,
                10
            );
            if (!hash_equals($mac, $calculatedMac)) {
                throw new Exception("Incorrect MAC signature.");
            }

            // Дешифруем AES-256-CBC
            $decrypted = openssl_decrypt(
                $encrypted,
                'aes-256-cbc',
                $this->keyGenerator->getCipherKey(),
                OPENSSL_RAW_DATA,
                $this->keyGenerator->getIv()
            );

            return $decrypted;
        } catch (Throwable $exception) {
            throw $exception;
        }
    }
}
