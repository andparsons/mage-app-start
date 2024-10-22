<?php
namespace Magento\ImportExport\Test\Unit\Model\Import;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

abstract class AbstractImportTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @param array|null $methods
     * @return ProcessingErrorAggregatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getErrorAggregatorObject($methods = null)
    {
        $errorFactory = $this->getMockBuilder(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $errorFactory->method('create')->willReturn(
            $this->objectManagerHelper->getObject(
                \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::class
            )
        );
        return $this->getMockBuilder(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator::class
        )->setMethods($methods)
            ->setConstructorArgs(['errorFactory' => $errorFactory])
            ->getMock();
    }
}
