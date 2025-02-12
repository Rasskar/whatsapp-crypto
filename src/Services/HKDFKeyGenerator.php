<?php

namespace Rasskar\Crypto\Services;

use Rasskar\Crypto\Interfaces\KeyGeneratorInterface;

class HKDFKeyGenerator implements KeyGeneratorInterface
{
    /**
     * @var string - Вектор инициализации
     */
    private string $iv = '';

    /**
     * @var string - Ключ шифрования
     */
    private string $cipherKey = '';

    /**
     * @var string - Ключ MAC
     */
    private string $macKey = '';

    /**
     * @param string $mediaKey
     * @param string $info
     * @return void
     */
    public function generate(string $mediaKey, string $info): void
    {
        // Расширяем 32-байтовый mediaKey до 112 байт с помощью HKDF-SHA256
        $mediaKeyExpanded = hash_hkdf("sha256", $mediaKey, 112, $info);

        $this->iv = substr($mediaKeyExpanded, 0, 16); // Вектор инициализации (IV) – первые 16 байт
        $this->cipherKey = substr($mediaKeyExpanded, 16, 32); // Ключ шифрования – с 16 до 48 байт
        $this->macKey = substr($mediaKeyExpanded, 48, 32); // Ключ MAC – с 48 до 80 байт
    }

    /**
     * @return string
     */
    public function getIv(): string
    {
        return $this->iv;
    }

    /**
     * @return string
     */
    public function getCipherKey(): string
    {
        return $this->cipherKey;
    }

    /**
     * @return string
     */
    public function getMacKey(): string
    {
        return $this->macKey;
    }
}
