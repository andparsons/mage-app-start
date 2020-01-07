<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Company\Controller\Adminhtml\Index;

use Magento\Company\Model\Company\DataProvider;

/**
 * Class SavePluginTest.
 */
class SavePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\NegotiableQuote\Helper\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $companyHelper;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Company\Controller\Adminhtml\Index\SavePlugin
     */
    private $savePlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->companyHelper = $this->createMock(\Magento\NegotiableQuote\Helper\Company::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->savePlugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Company\Controller\Adminhtml\Index\SavePlugin::class,
            [
                'request' => $this->request,
                'companyHelper' => $this->companyHelper,
            ]
        );
    }

    /**
     * Test for afterSetCompanyRequestData method.
     *
     * @return void
     */
    public function testAfterSetCompanyRequestData()
    {
        $params = [
            DataProvider::DATA_SCOPE_SETTINGS => [
                'is_quote_enabled' => true,
            ]
        ];
        $subject = $this->createMock(\Magento\Company\Controller\Adminhtml\Index\Save::class);
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $this->request->expects($this->once())->method('getParams')->willReturn($params);
        $quoteConfig = $this->createMock(\Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface::class);
        $this->companyHelper->expects($this->once())
            ->method('getQuoteConfig')->with($company)->willReturn($quoteConfig);
        $quoteConfig->expects($this->once())->method('setIsQuoteEnabled')
            ->with($params[DataProvider::DATA_SCOPE_SETTINGS]['is_quote_enabled'])->willReturnSelf();
        $this->assertEquals($company, $this->savePlugin->afterSetCompanyRequestData($subject, $company));
    }
}
