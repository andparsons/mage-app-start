<?php
namespace Magento\Framework\App\Test\Unit\PageCache;

use \Magento\Framework\App\PageCache\Version;

use Magento\TestFramework\ObjectManager;

class VersionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Version instance
     *
     * @var Version
     */
    protected $version;

    /**
     * Cookie manager mock
     *
     * @var \Magento\Framework\Stdlib\CookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManagerMock;

    /**
     * Cookie manager mock
     *
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieMetadataFactoryMock;

    /**
     * Request mock
     *
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * Create cookie and request mock, version instance
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cookieManagerMock = $this->createMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->cookieMetadataFactoryMock = $this->getMockBuilder(
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
        )
            ->disableOriginalConstructor()->getMock();
        $this->version = $objectManager->getObject(
            \Magento\Framework\App\PageCache\Version::class,
            [
                'cookieManager' => $this->cookieManagerMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'request' => $this->requestMock
            ]
        );
    }

    /**
     * Handle private content version cookie
     * Set cookie if it is not set.
     * Increment version on post requests.
     * In all other cases do nothing.
     */

    /**
     * @dataProvider processProvider
     * @param bool $isPost
     */
    public function testProcess($isPost)
    {
        $this->requestMock->expects($this->once())->method('isPost')->will($this->returnValue($isPost));
        if ($isPost) {
            $publicCookieMetadataMock = $this->createMock(\Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class);
            $publicCookieMetadataMock->expects($this->once())
                ->method('setPath')
                ->with('/')
                ->will($this->returnSelf());

            $publicCookieMetadataMock->expects($this->once())
                ->method('setDuration')
                ->with(Version::COOKIE_PERIOD)
                ->will($this->returnSelf());

            $publicCookieMetadataMock->expects($this->once())
                ->method('setSecure')
                ->with(false)
                ->will($this->returnSelf());
                
            $publicCookieMetadataMock->expects($this->once())
                ->method('setHttpOnly')
                ->with(false)
                ->will($this->returnSelf());

            $this->cookieMetadataFactoryMock->expects($this->once())
                ->method('createPublicCookieMetadata')
                ->with()
                ->will(
                    $this->returnValue($publicCookieMetadataMock)
                );

            $this->cookieManagerMock->expects($this->once())
                ->method('setPublicCookie');
        }
        $this->version->process();
    }

    /**
     * Data provider for testProcess
     *
     * @return array
     */
    public function processProvider()
    {
        return [
            "post" => [true],
            "notPost" => [false]
        ];
    }
}
