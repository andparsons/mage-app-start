<?php
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config;

/**
 * GraphQL config element.
 */
interface ConfigElementInterface
{
    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @return string
     */
    public function getDescription() : string;
}
