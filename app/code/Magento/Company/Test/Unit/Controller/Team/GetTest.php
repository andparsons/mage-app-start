<?php

namespace Magento\Company\Test\Unit\Controller\Team;

/**
 * Class GetTest.
 */
class GetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Controller\Team\Get
     */
    private $get;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    /**
     * @var \Magento\Company\Api\TeamRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $teamRepository;

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
        $this->structureManager = $this->createMock(
            \Magento\Company\Model\Company\Structure::class
        );
        $this->structureManager->expects($this->any())
            ->method('getAllowedIds')->will(
                $this->returnValue(
                    [
                        'teams' => [1, 2, 5, 7]
                    ]
                )
            );
        $this->teamRepository = $this->createMock(
            \Magento\Company\Api\TeamRepositoryInterface::class
        );
        $this->request = $this->createMock(
            \Magento\Framework\App\RequestInterface::class
        );
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

        $logger = $this->createMock(
            \Psr\Log\LoggerInterface::class
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->get = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Team\Get::class,
            [
                'resultFactory' => $resultFactory,
                'structureManager' => $this->structureManager,
                'teamRepository' => $this->teamRepository,
                'logger' => $logger,
                '_request' => $this->request
            ]
        );
    }

    /**
     * Test execute.
     *
     * @param int $teamId
     * @param bool $isException
     * @param string $expect
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute($teamId, $isException, $expect)
    {
        $this->request->expects($this->once())->method('getParam')->with('team_id')->willReturn($teamId);

        if ($isException) {
            $this->teamRepository->expects($this->any())
                ->method('get')->willThrowException(new \Exception());
        } else {
            $team = $this->getMockBuilder(\Magento\Company\Api\Data\TeamInterface::class)
                ->setMethods(['getData'])
                ->getMockForAbstractClass();

            $this->teamRepository->expects($this->any())
                ->method('get')->will($this->returnValue($team));
            $team->expects($this->any())->method('getData')->willReturn([]);
        }

        $result = '';
        $setDataCallback = function ($data) use (&$result) {
            $result = $data['status'];
        };

        $this->resultJson->expects($this->any())->method('setData')->will($this->returnCallback($setDataCallback));
        $this->get->execute();
        $this->assertEquals($expect, $result);
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
            [2, true, 'error'],
            [2, true, 'error'],
            [4, true, 'error'],
        ];
    }
}
