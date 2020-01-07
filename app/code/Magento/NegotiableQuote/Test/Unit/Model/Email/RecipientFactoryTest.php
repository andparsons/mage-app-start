<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Email;

/**
 * RecipientFactory Test.
 */
class RecipientFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerViewHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeResolver;

    /**
     * @var \Magento\NegotiableQuote\Model\Email\RecipientFactory
     */
    private $recipientFactory;

    /**
     * Set up.
     * @return void
     */
    protected function setUp()
    {
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerViewHelper = $this->getMockBuilder(\Magento\Customer\Api\CustomerNameGenerationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->recipientFactory = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Email\RecipientFactory::class,
            [
                'companyManagement' => $this->companyManagement,
                'storeManager' => $this->storeManager,
                'customerViewHelper' => $this->customerViewHelper,
                'localeResolver' => $this->localeResolver,
            ]
        );
    }

    /**
     * Test createForQuote method.
     * @return void
     */
    public function testCreateForQuote()
    {
        $quote = $this->getMockBuilder(
            \Magento\Quote\Api\Data\CartInterface::class
        )->disableOriginalConstructor()->getMock();
        $customer = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
        )->disableOriginalConstructor()->getMock();
        $customer->expects($this->atLeastOnce())->method('getWebsiteId')->willReturn(2);
        $quote->expects($this->atLeastOnce())->method('getCustomer')->willReturn($customer);
        $website = $this->getMockBuilder(
            \Magento\Store\Api\Data\WebsiteInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getStoreIds'])
            ->getMockForAbstractClass();
        $website->expects($this->atLeastOnce())->method('getStoreIds')->willReturn([1]);
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->customerViewHelper->expects($this->atLeastOnce())->method('getCustomerName')->willReturn('Name');
        $company = $this->getMockBuilder(
            \Magento\Company\Api\Data\CompanyInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->companyManagement->expects($this->atLeastOnce())->method('getByCustomerId')->willReturn($company);
        $extensionAttributes = $this->getMockBuilder(
            \Magento\Quote\Api\Data\CartExtensionInterface::class
        )->setMethods(['getNegotiableQuote'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class
        )->disableOriginalConstructor()->getMock();
        $negotiableQuote->expects($this->atLeastOnce())->method('getExpirationPeriod')->willReturn('2020-01-01');
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $this->localeResolver->expects($this->atLeastOnce())->method('getLocale')->willReturn('en_US');
        $this->recipientFactory->createForQuote($quote);
    }
}
