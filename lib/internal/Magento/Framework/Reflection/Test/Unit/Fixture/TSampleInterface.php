<?php
namespace Magento\Framework\Reflection\Test\Unit\Fixture;

interface TSampleInterface
{
    /**
     * Returns property name for a sample.
     *
     * @return string
     */
    public function getPropertyName();

    /**
     * Doc block without return tag.
     */
    public function getName();
}
