<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Unit test for Magento\Company\Model\SaveHandlerPool class.
 */
class SaveHandlerPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\SaveValidatorPool
     */
    private $saveValidatorPool;

    /**
     * Test execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $handler = $this->getMockBuilder(\Magento\Company\Model\SaveHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->saveValidatorPool = $objectManager->getObject(
            \Magento\Company\Model\SaveHandlerPool::class,
            [
                'handlers' => [$handler],
            ]
        );
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $handler->expects($this->once())->method('execute');

        $this->saveValidatorPool->execute($company, $company);
    }

    /**
     * Test execute method with InvalidArgumentException.
     *
     * @return void
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage is not an instance of Magento\Company\Model\SaveHandlerInterface
     */
    public function testExecuteWithInvalidArgumentException()
    {
        $handler = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->saveValidatorPool = $objectManager->getObject(
            \Magento\Company\Model\SaveHandlerPool::class,
            [
                'handlers' => [$handler],
            ]
        );
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->saveValidatorPool->execute($company, $company);
    }
}
