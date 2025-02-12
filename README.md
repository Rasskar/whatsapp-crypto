# WhatsApp Crypto

[![Latest Stable Version](https://img.shields.io/packagist/v/rasskar/whatsapp-crypto.svg?style=flat-square)](https://packagist.org/packages/rasskar/whatsapp-crypto)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-blue.svg?style=flat-square)](https://www.php.net/)

## Описание
`rasskar/whatsapp-crypto` — это библиотека с декораторами для PSR-7 потоков, которые шифруют и расшифровывают медиафайлы в соответствии с алгоритмом WhatsApp.  
Она использует **AES-256-CBC**, HMAC-SHA256 и HKDF для безопасного шифрования.

## Установка через Сomposer

```bash
composer require rasskar/whatsapp-crypto
```

## Требования
- PHP 8.0 или выше
- OpenSSL

## Информационные строки для HKDF
HKDF позволяет указывать информационные строки, специфичные для контекста/приложения.
В данном случае контекстом является тип файла, для каждого из которых своя информационная строка:

| Media Type | Application Info         |
| --------- | ------------------------ |
| IMAGE     | `WhatsApp Image Keys`    |
| VIDEO     | `WhatsApp Video Keys`    |
| AUDIO     | `WhatsApp Audio Keys`    |

## Шифрование
```php
require 'vendor/autoload.php';

use GuzzleHttp\Psr7\Stream;
use Rasskar\Crypto\Facades\MediaCrypto;
use Rasskar\Crypto\Services\KeyGenerator;

// Исходный файл
$filePath = 'input.jpg';
$mediaKey = random_bytes(32);
$info = "WhatsApp Image Keys";

// Открываем поток
$stream = new Stream(fopen($filePath, 'rb'));

// Создаём  HKDFKeyGenerator
$keyGenerator = new HKDFKeyGenerator();

// Используем фасад MediaCrypto для шифрования
$encryptedData = MediaCrypto::encrypt($stream, $keyGenerator, $mediaKey, $info);

// Сохраняем зашифрованный файл
file_put_contents('output.enc', $encryptedData);

echo "Файл зашифрован: output.enc";
```

## Расшифровка
```php
require 'vendor/autoload.php';

use GuzzleHttp\Psr7\Stream;
use Rasskar\Crypto\Facades\MediaCrypto;
use Rasskar\Crypto\Services\KeyGenerator;

// Файл с зашифрованными данными
$filePath = 'output.enc';
$mediaKey = "..."; // Должен быть таким же, как при шифровании
$info = "WhatsApp Image Keys";

// Открываем поток
$encryptedStream = new Stream(fopen($encryptedFile, 'r'));

// Создаём  HKDFKeyGenerator
$keyGenerator = new HKDFKeyGenerator();

// Дешифруем файл используя фасад MediaCrypto
$decryptedData = MediaCrypto::decrypt($filePath, $keyGenerator, $mediaKey, $info);

// Сохраняем расшифрованный файл
file_put_contents('output.jpg', $decryptedData);

echo "Файл расшифрован: output.jpg";
```