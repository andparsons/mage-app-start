<?php
namespace Magento\Paypal\Block\Adminhtml\Billing\Agreement\View\Tab;

class InfoTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testCustomerGridAction()
    {
        /** @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection $billingAgreementCollection */
        $billingAgreementCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection::class
        )->load();
        $agreementId = $billingAgreementCollection->getFirstItem()->getId();
        $this->dispatch('backend/paypal/billing_agreement/view/agreement/' . $agreementId);

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//a[@name="billing_agreement_info"]',
                $this->getResponse()->getBody()
            ),
            'Response for billing agreement info doesn\'t contain billing agreement info tab'
        );

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//a[contains(text(), "customer@example.com")]',
                $this->getResponse()->getBody()
            ),
            'Response for billing agreement info doesn\'t contain Customer info'
        );
    }
}
