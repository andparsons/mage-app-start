<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Company\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CompanyRepositoryInterfacePluginTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyRepositoryInterfacePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Company\Api\CompanyRepositoryInterfacePlugin
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepositoryInterfacePlugin;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Extractor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extractor;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerResource;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\CompanyQuoteConfigRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyQuoteConfigRepository;

    /**
     * @var \Magento\NegotiableQuote\Helper\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteGrid;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $purgedContentsHandler;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subject;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->extractor = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Purged\Extractor::class)
            ->setMethods(['extractCustomer'])
            ->disableOriginalConstructor()->getMock();

        $this->customerResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Customer::class)
            ->setMethods(['getCustomerIdsByCompanyId'])
            ->disableOriginalConstructor()->getMock();

        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->setMethods(['getById'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->companyQuoteConfigRepository = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\CompanyQuoteConfigRepositoryInterface::class)
            ->setMethods(['save'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->companyHelper = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Company::class)
            ->setMethods(['getQuoteConfig'])
            ->disableOriginalConstructor()->getMock();

        $this->quoteGrid = $this->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::class)
            ->setMethods(['refreshValue'])
            ->disableOriginalConstructor()->getMock();

        $this->purgedContentsHandler = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Purged\Handler::class)
            ->setMethods(['process'])
            ->disableOriginalConstructor()->getMock();

        $this->subject = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->setMethods([
                'getId',
                'dataHasChangedFor',
                'getCompanyName'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->companyRepositoryInterfacePlugin = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Plugin\Company\Api\CompanyRepositoryInterfacePlugin::class,
            [
                'extractor' => $this->extractor,
                'customerResource' => $this->customerResource,
                'customerRepository' => $this->customerRepository,
                'companyQuoteConfigRepository' => $this->companyQuoteConfigRepository,
                'companyHelper' => $this->companyHelper,
                'quoteGrid' => $this->quoteGrid,
                'purgedContentsHandler' => $this->purgedContentsHandler
            ]
        );
    }

    /**
     * Test aroundSave method.
     *
     * @return void
     */
    public function testAroundSave()
    {
        $closure = function () {
        };

        $companyId = 34;
        $companyName = 'Test Company';
        $this->company->expects($this->exactly(3))->method('getId')->willReturn($companyId);
        $this->company->expects($this->exactly(1))->method('dataHasChangedFor')->willReturn(true);
        $this->company->expects($this->exactly(1))->method('getCompanyName')->willReturn($companyName);

        $this->quoteGrid->expects($this->exactly(1))->method('refreshValue')->willReturnSelf();

        $quoteConfig = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface::class)
            ->setMethods(['setCompanyId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quoteConfig->expects($this->exactly(1))->method('setCompanyId')->willReturnSelf();

        $this->companyHelper->expects($this->exactly(1))->method('getQuoteConfig')->willReturn($quoteConfig);

        $this->companyQuoteConfigRepository->expects($this->exactly(1))->method('save')->willReturn(true);

        $this->companyRepositoryInterfacePlugin->aroundSave($this->subject, $closure, $this->company);
    }

    /**
     * Test beforeDelete method.
     *
     * @return void
     */
    public function testBeforeDelete()
    {
        $companyId = 34;
        $this->company->expects($this->exactly(1))->method('getId')->willReturn($companyId);

        $customers = [2 => 2];
        $this->customerResource->expects($this->exactly(1))
            ->method('getCustomerIdsByCompanyId')->willReturn($customers);

        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->customerRepository->expects($this->exactly(1))->method('getById')->willReturn($customer);

        $customerData = [1, 2, 3];
        $this->extractor->expects($this->exactly(1))->method('extractCustomer')->willReturn($customerData);

        $this->purgedContentsHandler->expects($this->exactly(1))->method('process');

        $this->companyRepositoryInterfacePlugin->beforeDelete($this->subject, $this->company);
    }
}
