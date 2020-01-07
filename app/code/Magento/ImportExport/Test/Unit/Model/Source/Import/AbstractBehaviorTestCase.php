<?php

/**
 * Abstract class for behavior tests
 */
namespace Magento\ImportExport\Test\Unit\Model\Source\Import;

abstract class AbstractBehaviorTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Model for testing
     *
     * @var \Magento\ImportExport\Model\Source\Import\AbstractBehavior
     */
    protected $_model;

    protected function tearDown()
    {
        unset($this->_model);
    }
}
