<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Unit test for Magento\Company\Model\SaveValidatorPool class.
 */
class SaveValidatorPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\SaveValidatorInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saveValidatorFactory;

    /**
     * @var \Magento\Company\Model\SaveValidatorPool
     */
    private $saveValidatorPool;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->saveValidatorFactory = $this
            ->getMockBuilder(\Magento\Company\Model\SaveValidatorInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->saveValidatorPool = $objectManager->getObject(
            \Magento\Company\Model\SaveValidatorPool::class,
            [
                'saveValidatorFactory' => $this->saveValidatorFactory,
                'validators' => [\Magento\Company\Model\SaveValidatorInterface::class],
            ]
        );
    }

    /**
     * Test execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $exception = new \Magento\Framework\Exception\InputException();
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $validator = $this->getMockBuilder(\Magento\Company\Model\SaveValidatorInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->saveValidatorFactory->expects($this->once())
            ->method('create')
            ->with(
                \Magento\Company\Model\SaveValidatorInterface::class,
                [
                    'company' => $company,
                    'initialCompany' => $company,
                    'exception' => $exception
                ]
            )
            ->willReturn($validator);
        $validator->expects($this->once())->method('execute');
        $this->saveValidatorPool->execute($company, $company);
    }

    /**
     * Test execute method with InvalidArgumentException.
     *
     * @return void
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Type Magento\Framework\DataObject is not
     * an instance of Magento\Company\Model\SaveValidatorInterface
     */
    public function testExecuteWithInvalidArgumentException()
    {
        $exception = new \Magento\Framework\Exception\InputException();
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $validator = new \Magento\Framework\DataObject();
        $this->saveValidatorFactory->expects($this->once())
            ->method('create')
            ->with(
                \Magento\Company\Model\SaveValidatorInterface::class,
                [
                    'company' => $company,
                    'initialCompany' => $company,
                    'exception' => $exception
                ]
            )
            ->willReturn($validator);

        $this->saveValidatorPool->execute($company, $company);
    }
}
