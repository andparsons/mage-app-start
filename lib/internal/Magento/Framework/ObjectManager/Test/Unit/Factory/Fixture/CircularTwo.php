<?php
namespace Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture;

/**
 * Part of the chain for circular dependency test
 */
class CircularTwo
{
    /**
     * @param CircularThree $three
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(CircularThree $three)
    {
    }
}
