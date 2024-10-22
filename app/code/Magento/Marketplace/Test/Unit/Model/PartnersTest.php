<?php

namespace Magento\Marketplace\Test\Unit\Model;

class PartnersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Marketplace\Model\Partners
     */
    private $partnersModelMock;

    private $returnPackages = '
                 {
                    "partners": {
                        "1": {
                            "url_page": "http://test.com",
                            "url_partner_page": "http://test.com",
                            "img": "http://test.com/img",
                            "title": "Test page",
                            "description": "Test page description"
                        },
                        "2": {
                            "url_page": "http://test.com",
                            "url_partner_page": "http://test.com",
                            "img": "http://test.com/img",
                            "title": "Test page",
                            "description": "Test page description"
                        }
                    }
                 }';

    protected function setUp()
    {
        $this->partnersModelMock = $this->getPartnersModelMock(
            [
                'getApiUrl',
                'getCurlClient',
                'getCache',
                'getReferer'
            ]
        );
    }

    /**
     * @var string
     */
    protected $apiUrl = 'www.testpackages';

    /**
     * @covers \Magento\Marketplace\Model\Partners::getPartners
     */
    public function testGetPartners()
    {
        $this->partnersModelMock->expects($this->once())
            ->method('getApiUrl')
            ->will($this->returnValue($this->apiUrl));

        $curlMock = $this->getCurlMock(['post', 'getBody', 'setOptions']);
        $curlMock->expects($this->once())
            ->method('post');
        $curlMock->expects($this->once())
            ->method('setOptions');
        $curlMock->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($this->returnPackages));
        $this->partnersModelMock->expects($this->exactly(3))
            ->method('getCurlClient')
            ->will($this->returnValue($curlMock));

        $cacheMock = $this->getCacheMock(['savePartnersToCache']);
        $cacheMock->expects($this->once())
            ->method('savePartnersToCache');
        $this->partnersModelMock->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cacheMock));
        $this->partnersModelMock->expects($this->once())
            ->method('getReferer');

        $this->partnersModelMock->getPartners();
    }

    /**
     * @covers \Magento\Marketplace\Model\Partners::getPartners
     */
    public function testGetPartnersException()
    {
        $this->partnersModelMock->expects($this->once())
            ->method('getApiUrl')
            ->will($this->returnValue($this->apiUrl));

        $curlMock = $this->getCurlMock(['post', 'getBody', 'setOptions']);
        $curlMock->expects($this->once())
            ->method('post');
        $curlMock->expects($this->once())
            ->method('getBody')
            ->will($this->throwException(new \Exception));
        $this->partnersModelMock->expects($this->exactly(3))
            ->method('getCurlClient')
            ->will($this->returnValue($curlMock));

        $cacheMock = $this->getCacheMock(['savePartnersToCache', 'loadPartnersFromCache']);
        $cacheMock->expects($this->never())
            ->method('savePartnersToCache');
        $cacheMock->expects($this->once())
            ->method('loadPartnersFromCache');
        $this->partnersModelMock->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cacheMock));
        $this->partnersModelMock->expects($this->once())
            ->method('getReferer');

        $this->partnersModelMock->getPartners();
    }

    /**
     * Gets partners block mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Marketplace\Block\Partners
     */
    public function getPartnersBlockMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Marketplace\Block\Partners::class, $methods);
    }

    /**
     * Gets partners model mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Marketplace\Model\Partners
     */
    public function getPartnersModelMock($methods)
    {
        return $this->createPartialMock(\Magento\Marketplace\Model\Partners::class, $methods, []);
    }

    /**
     * Gets partners model mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\HTTP\Client\Curl
     */
    public function getCurlMock($methods)
    {
        return $this->createPartialMock(\Magento\Framework\HTTP\Client\Curl::class, $methods, []);
    }

    /**
     * Gets cache mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\HTTP\Client\Curl
     */
    public function getCacheMock($methods)
    {
        return $this->createPartialMock(\Magento\Marketplace\Helper\Cache::class, $methods, []);
    }
}
