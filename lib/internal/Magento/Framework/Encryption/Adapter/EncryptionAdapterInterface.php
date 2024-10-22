<?php

declare(strict_types=1);

namespace Magento\Framework\Encryption\Adapter;

interface EncryptionAdapterInterface
{
    /**
     * @param $data
     * @return string
     */
    public function encrypt(string $data): string;

    /**
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string;
}
