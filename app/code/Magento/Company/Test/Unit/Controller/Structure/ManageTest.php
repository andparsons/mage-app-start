<?php

namespace Magento\Company\Test\Unit\Controller\Structure;

/**
 * Class ManageTest.
 */
class ManageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var  \Magento\Company\Controller\Structure\Manage
     */
    private $manage;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManager;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $resultFactory = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );
        $this->resultJson = $this->createPartialMock(
            \Magento\Framework\Controller\Result\Json::class,
            ['setData']
        );
        $resultFactory->expects($this->any())
            ->method('create')->will($this->returnValue($this->resultJson));

        $this->structureManager = $this->createMock(
            \Magento\Company\Model\Company\Structure::class
        );
        $this->structureManager->expects($this->any())
            ->method('getAllowedIds')->will(
                $this->returnValue(
                    [
                        'structures' => [1, 2, 5, 7]
                    ]
                )
            );
        $this->request = $this->createMock(
            \Magento\Framework\App\RequestInterface::class
        );

        $logger = $this->createMock(
            \Psr\Log\LoggerInterface::class
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->manage = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Structure\Manage::class,
            [
                'resultFactory' => $resultFactory,
                'structureManager' => $this->structureManager,
                'logger' => $logger,
                '_request' => $this->request
            ]
        );
    }

    /**
     * Test execute.
     *
     * @param int $structureId
     * @param bool $isException
     * @param string $expected
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute($structureId, $isException, $expected)
    {

        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('structure_id')
            ->willReturn($structureId);
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('target_id')
            ->willReturn(1);

        if ($isException) {
            $this->structureManager->expects($this->any())
                ->method('moveNode')->with(13, 1)->willThrowException(new \Exception());
        }

        $result = '';
        $setDataCallback = function ($data) use (&$result) {
            $result = $data['status'];
        };
        $this->resultJson->expects($this->any())->method('setData')->will($this->returnCallback($setDataCallback));
        $this->manage->execute();
        $this->assertEquals($expected, $result);
    }

    /**
     * Execute DataProvider.
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [1, false, 'ok'],
            [13, false, 'error'],
            [7, true, 'error'],
        ];
    }
}
