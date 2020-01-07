<?php

namespace Magento\ServicesId\Model;

use Magento\ServicesId\Exception\InstanceIdGenerationException;

interface GeneratorInterface
{
    /**
     * Generate new Instance ID
     *
     * @return string
     * @throws InstanceIdGenerationException
     */
    public function generateInstanceId(): string;
}
