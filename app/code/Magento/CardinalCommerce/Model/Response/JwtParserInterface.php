<?php

namespace Magento\CardinalCommerce\Model\Response;

/**
 * Parses content of CardinalCommerce response JWT.
 */
interface JwtParserInterface
{
    /**
     * Returns response JWT content.
     *
     * @param string $jwt
     * @return array
     */
    public function execute(string $jwt): array;
}
