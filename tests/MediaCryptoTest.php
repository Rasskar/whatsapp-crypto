<?php

use PHPUnit\Framework\TestCase;
use Rasskar\Crypto\Facades\MediaCrypto;
use GuzzleHttp\Psr7\Stream;
use Rasskar\Crypto\Services\HKDFKeyGenerator;

class MediaCryptoTest extends TestCase
{
    private array $allowedInfo = [
        "IMAGE" => "WhatsApp Image Keys",
        "VIDEO" => "WhatsApp Video Keys",
        "AUDIO" => "WhatsApp Audio Keys"
    ];

    private array $originalFiles;
    private array $encryptedFiles;

    protected function setUp(): void
    {
        $samplesPath = __DIR__ . '/../samples/';

        $this->originalFiles = glob($samplesPath . '*.original');
        $this->encryptedFiles = glob($samplesPath . '*.encrypted');
    }

    private function getInfoFromFileName(string $filename): string
    {
        foreach (array_keys($this->allowedInfo) as $type) {
            if (stripos($filename, $type) !== false) {
                return $this->allowedInfo[$type];
            }
        }

        $this->fail("Не удалось определить 'info' для файла: $filename");
    }

    public function testEncryption(): void
    {
        foreach ($this->originalFiles as $originalFile) {
            $keyFile = str_replace('.original', '.key', $originalFile);

            if (!file_exists($keyFile)) {
                $this->fail("Ключ не найден для файла $originalFile");
            }

            $mediaKey = file_get_contents($keyFile);
            $stream = new Stream(fopen($originalFile, 'r'));
            $keyGenerator = new HKDFKeyGenerator();
            $info = $this->getInfoFromFileName($originalFile);

            $encryptedData = MediaCrypto::encrypt($stream, $keyGenerator, $mediaKey, $info);
            $this->assertNotEmpty($encryptedData, "Шифрование не должно возвращать пустую строку ($originalFile)");

            $expectedEncryptedFile = str_replace('.original', '.encrypted', $originalFile);

            if (file_exists($expectedEncryptedFile)) {
                $expectedData = file_get_contents($expectedEncryptedFile);
                $this->assertEquals($expectedData, $encryptedData, "Зашифрованный файл не совпадает ($originalFile)");
            }
        }
    }

    public function testDecryption(): void
    {
        foreach ($this->encryptedFiles as $encryptedFile) {
            $keyFile = str_replace('.encrypted', '.key', $encryptedFile);

            if (!file_exists($keyFile)) {
                $this->fail("Ключ не найден для файла $encryptedFile");
            }

            $mediaKey = file_get_contents($keyFile);
            $encryptedStream = new Stream(fopen($encryptedFile, 'r'));
            $keyGenerator = new HKDFKeyGenerator();
            $info = $this->getInfoFromFileName($encryptedFile);

            $decryptedData = MediaCrypto::decrypt($encryptedStream, $keyGenerator, $mediaKey, $info);
            $expectedOriginalFile = str_replace('.encrypted', '.original', $encryptedFile);

            if (!file_exists($expectedOriginalFile)) {
                $this->fail("Оригинальный файл не найден для $encryptedFile");
            }

            $expectedData = file_get_contents($expectedOriginalFile);
            $this->assertEquals($expectedData, $decryptedData, "Дешифрованный файл не совпадает ($encryptedFile)");
        }
    }
}
