<?php

namespace Magento\Company\Test\Unit\Controller\Adminhtml\Index;

/**
 * Class ListUserTest.
 */
class ListUserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Controller\Adminhtml\Index\ListUser
     */
    private $list;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit\Framework\MockObject_MockObject
     */
    private $resultRawFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection|\PHPUnit\Framework\MockObject_MockObject
     */
    private $customerCollection;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->response = $this->createPartialMock(
            \Magento\Framework\Controller\Result\Raw::class,
            [
                'setHeader',
                'setContents'
            ]
        );
        $this->resultRawFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RawFactory::class,
            ['create']
        );
        $this->resultRawFactory->expects($this->once())->method('create')->willReturn($this->response);
        $this->request = $this->createMock(
            \Magento\Framework\App\RequestInterface::class
        );
        $this->customerCollection = $this->createPartialMock(
            \Magento\Customer\Model\ResourceModel\Customer\Collection::class,
            ['load', 'getIdFieldName', 'addFieldToFilter']
        );
        $this->customerCollection->expects($this->any())
            ->method('getIdFieldName')->will($this->returnValue('id'));
        $customerCollectionFactory = $this->createPartialMock(
            \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory::class,
            ['create']
        );
        $customerCollectionFactory->expects($this->any())
            ->method('create')->will($this->returnValue($this->customerCollection));

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->list = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Adminhtml\Index\ListUser::class,
            [
                'resultRawFactory' => $this->resultRawFactory,
                'customerCollectionFactory' => $customerCollectionFactory,
                '_request' => $this->request
            ]
        );
    }

    /**
     * Test execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->request->expects($this->once())->method('getParam')->with('email')->willReturn('example@test');
        $this->customerCollection->expects($this->once())
            ->method('addFieldToFilter')->with(
                'email',
                [
                    'like' => 'example@test%'
                ]
            )->will($this->returnSelf());
        $item = new \Magento\Framework\DataObject(['email' => 'example@test.com', 'id' => 1]);
        $this->customerCollection->addItem($item);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Raw::class, $this->list->execute());
    }

    /**
     * Test execute with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $exception = new \Exception();
        $this->request->expects($this->once())->method('getParam')->with('email')->willThrowException($exception);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Raw::class, $this->list->execute());
    }

    /**
     * Test execute with LocalizedException.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $phrase = new \Magento\Framework\Phrase(__('Exception'));
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->request->expects($this->once())->method('getParam')->with('email')->willThrowException($exception);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Raw::class, $this->list->execute());
    }
}
