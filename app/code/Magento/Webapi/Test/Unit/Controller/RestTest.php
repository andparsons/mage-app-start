<?php

namespace Magento\Webapi\Test\Unit\Controller;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\AuthorizationException;

/**
 * Test Rest controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class RestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Webapi\Controller\Rest
     */
    protected $_restController;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\Framework\Webapi\Rest\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Webapi\Controller\Rest\Router\Route
     */
    protected $_routeMock;

    /**
     * @var \stdClass|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_serviceMock;

    /**
     * @var \Magento\Framework\Oauth\OauthInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_oauthServiceMock;

    /**
     * @var \Magento\Framework\Webapi\Authorization|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authorizationMock;

    /**
     * @var \Magento\Framework\Webapi\ServiceInputProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceInputProcessorMock;

    /**
     * @var \Magento\Webapi\Model\Rest\Swagger\Generator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $swaggerGeneratorMock;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var  \Magento\Store\Api\Data\StoreInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var  \Magento\Webapi\Controller\Rest\SchemaRequestProcessor | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $schemaRequestProcessor;

    /**
     * @var  \Magento\Webapi\Controller\Rest\SynchronousRequestProcessor | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $synchronousRequestProcessor;

    /**
     * @var  \Magento\Webapi\Controller\Rest\RequestProcessorPool | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestProcessorPool;

    const SERVICE_METHOD = 'testMethod';

    const SERVICE_ID = \Magento\Webapi\Controller\Rest::class;

    protected function setUp()
    {
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_requestMock = $this->getRequestMock();
        $this->_requestMock->expects($this->any())->method('getHttpHost')->willReturn('testHostName.com');
        $this->_responseMock = $this->getResponseMock();
        $routerMock = $this->getMockBuilder(\Magento\Webapi\Controller\Rest\Router::class)->setMethods(['match'])
            ->disableOriginalConstructor()->getMock();

        $this->_routeMock = $this->getRouteMock();
        $this->_serviceMock = $this->getMockBuilder(self::SERVICE_ID)->setMethods([self::SERVICE_METHOD])
            ->disableOriginalConstructor()->getMock();

        $this->_oauthServiceMock = $this->getMockBuilder(\Magento\Framework\Oauth\OauthInterface::class)
            ->setMethods(['validateAccessTokenRequest'])->getMockForAbstractClass();
        $this->_authorizationMock = $this->getMockBuilder(\Magento\Framework\Webapi\Authorization::class)
            ->disableOriginalConstructor()->getMock();

        $paramsOverriderMock = $this->getMockBuilder(\Magento\Webapi\Controller\Rest\ParamsOverrider::class)
            ->setMethods(['overrideParams'])
            ->disableOriginalConstructor()->getMock();

        $dataObjectProcessorMock = $this->getMockBuilder(\Magento\Framework\Reflection\DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodReturnType'])
            ->getMockForAbstractClass();

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()->getMock();

        $errorProcessorMock = $this->createMock(\Magento\Framework\Webapi\ErrorProcessor::class);
        $errorProcessorMock->expects($this->any())->method('maskException')->will($this->returnArgument(0));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->serviceInputProcessorMock = $this->getMockBuilder(\Magento\Framework\Webapi\ServiceInputProcessor::class)
            ->disableOriginalConstructor()->setMethods(['process'])->getMock();

        $areaListMock = $this->createMock(\Magento\Framework\App\AreaList::class);
        $areaMock = $this->createMock(\Magento\Framework\App\AreaInterface::class);
        $areaListMock->expects($this->any())->method('getArea')->will($this->returnValue($areaMock));
        $this->storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->requestProcessorPool = $this->getRequestProccessotPoolMock();

        $this->_restController =
            $objectManager->getObject(
                \Magento\Webapi\Controller\Rest::class,
                [
                    'request'               => $this->_requestMock,
                    'response'              => $this->_responseMock,
                    'router'                => $routerMock,
                    'objectManager'         => $objectManagerMock,
                    'layout'                => $layoutMock,
                    'oauthService'          => $this->_oauthServiceMock,
                    'authorization'         => $this->_authorizationMock,
                    'serviceInputProcessor' => $this->serviceInputProcessorMock,
                    'errorProcessor'        => $errorProcessorMock,
                    'areaList'              => $areaListMock,
                    'paramsOverrider'       => $paramsOverriderMock,
                    'dataObjectProcessor'   => $dataObjectProcessorMock,
                    'storeManager'          => $this->storeManagerMock,
                    'requestProcessorPool'  => $this->requestProcessorPool,
                ]
            );

        $this->_routeMock->expects($this->any())->method('getServiceClass')->will($this->returnValue(self::SERVICE_ID));
        $this->_routeMock->expects($this->any())->method('getServiceMethod')
            ->will($this->returnValue(self::SERVICE_METHOD));

        $routerMock->expects($this->any())->method('match')->will($this->returnValue($this->_routeMock));

        $objectManagerMock->expects($this->any())->method('get')->will($this->returnValue($this->_serviceMock));
        $this->_responseMock->expects($this->any())->method('prepareResponse')->will($this->returnValue([]));
        $this->_serviceMock->expects($this->any())->method(self::SERVICE_METHOD)->will($this->returnValue(null));

        $dataObjectProcessorMock->expects($this->any())->method('getMethodReturnType')
            ->with(self::SERVICE_ID, self::SERVICE_METHOD)
            ->will($this->returnValue('null'));

        $paramsOverriderMock->expects($this->any())->method('overrideParams')->will($this->returnValue([]));

        parent::setUp();
    }

    public function testDispatchSchemaRequest()
    {
        $params = [
            \Magento\Framework\Webapi\Request::REQUEST_PARAM_SERVICES => 'foo',
        ];
        $this->_requestMock->expects($this->any())
            ->method('getPathInfo')
            ->willReturn(\Magento\Webapi\Controller\Rest\SchemaRequestProcessor::PROCESSOR_PATH);

        $this->_requestMock->expects($this->any())
            ->method('getParams')
            ->will($this->returnValue($params));

        $schema = 'Some REST schema content';
        $this->swaggerGeneratorMock->expects($this->any())->method('generate')->willReturn($schema);
        $this->requestProcessorPool->getProcessor($this->_requestMock)->process($this->_requestMock);

        $this->assertEquals($schema, $this->_responseMock->getBody());
    }

    public function testDispatchAllSchemaRequest()
    {
        $params = [
            \Magento\Framework\Webapi\Request::REQUEST_PARAM_SERVICES => 'all',
        ];
        $this->_requestMock->expects($this->any())
            ->method('getPathInfo')
            ->willReturn(\Magento\Webapi\Controller\Rest\SchemaRequestProcessor::PROCESSOR_PATH);
        $this->_requestMock->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap([
                    [
                        \Magento\Framework\Webapi\Request::REQUEST_PARAM_SERVICES,
                        null,
                        'all',
                    ],
                ])
            );
        $this->_requestMock->expects($this->any())
            ->method('getParams')
            ->will($this->returnValue($params));
        $this->_requestMock->expects($this->any())
            ->method('getRequestedServices')
            ->will($this->returnValue('all'));

        $schema = 'Some REST schema content';
        $this->swaggerGeneratorMock->expects($this->any())->method('generate')->willReturn($schema);
        $this->requestProcessorPool->getProcessor($this->_requestMock)->process($this->_requestMock);

        $this->assertEquals($schema, $this->_responseMock->getBody());
    }

    /**
     * @return object|\Magento\Webapi\Controller\Rest\RequestProcessorPool
     */
    private function getRequestProccessotPoolMock()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->swaggerGeneratorMock = $this->getMockBuilder(\Magento\Webapi\Model\Rest\Swagger\Generator::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate', 'getListOfServices'])
            ->getMockForAbstractClass();

        $this->schemaRequestProcessor = $objectManager->getObject(
            \Magento\Webapi\Controller\Rest\SchemaRequestProcessor::class,
            [
                'swaggerGenerator' => $this->swaggerGeneratorMock,
                'response'         => $this->_responseMock,
            ]
        );

        $this->synchronousRequestProcessor =
            $this->getMockBuilder(\Magento\Webapi\Controller\Rest\SynchronousRequestProcessor::class)
                ->setMethods(['process'])
                ->disableOriginalConstructor()
                ->getMock();

        return $objectManager->getObject(
            \Magento\Webapi\Controller\Rest\RequestProcessorPool::class,
            [
                'requestProcessors' => [
                    'syncSchema' => $this->schemaRequestProcessor,
                    'sync'       => $this->synchronousRequestProcessor,
                ],
            ]
        );
    }

    /**
     * @return \Magento\Webapi\Controller\Rest\Router\Route | \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRouteMock()
    {
        return $this->getMockBuilder(\Magento\Webapi\Controller\Rest\Router\Route::class)
            ->setMethods([
                'isSecure',
                'getServiceMethod',
                'getServiceClass',
                'getAclResources',
                'getParameters',
            ])
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \Magento\Framework\Webapi\Rest\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRequestMock()
    {
        return $this->getMockBuilder(\Magento\Framework\Webapi\Rest\Request::class)
            ->setMethods(
                [
                    'isSecure',
                    'getRequestData',
                    'getParams',
                    'getParam',
                    'getRequestedServices',
                    'getPathInfo',
                    'getHttpHost',
                    'getMethod',
                ]
            )->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \Magento\Framework\Webapi\Rest\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResponseMock()
    {
        return $this->getMockBuilder(\Magento\Framework\Webapi\Rest\Response::class)
            ->setMethods(['sendResponse', 'prepareResponse', 'setHeader'])
            ->disableOriginalConstructor()
            ->getMock();
    }
}
