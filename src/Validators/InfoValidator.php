<?php

namespace Rasskar\Crypto\Validators;

use InvalidArgumentException;

class InfoValidator
{
    /**
     * @var array|string[]
     */
    private array $allowedInfo = [
        "WhatsApp Image Keys",
        "WhatsApp Video Keys",
        "WhatsApp Audio Keys",
        "WhatsApp Document Keys",
    ];

    /**
     * @param string $info
     * @return void
     */
    public function validate(string $info): void
    {
        if (!in_array($info, $this->allowedInfo)) {
            throw new InvalidArgumentException(
                "Invalid value for 'info'. Allowed values are: " . implode(', ', $this->allowedInfo)
            );
        }
    }
}
