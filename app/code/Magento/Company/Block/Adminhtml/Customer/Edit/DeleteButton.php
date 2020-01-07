<?php
namespace Magento\Company\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Delete button in customer edit form.
 */
class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $customerIdKey = 'id';

    /**
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        UrlInterface $urlBuilder,
        RequestInterface $request,
        AccountManagementInterface $accountManagement
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->accountManagement = $accountManagement;
    }

    /**
     * Returns data required for button rendering.
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $canModify = $customerId && !$this->accountManagement->isReadonly($customerId);
        $data = [];
        if ($customerId && $canModify) {
            $data = [
                'label' => __('Delete Customer'),
                'class' => 'delete',
                'id' => 'customer-delete-button',
                'data_attribute' => [
                    'mage-init' => '{"Magento_Company/js/actions/delete-customer":'
                                    . '{"url": "' . $this->getDeleteUrl() . '",
                                        "validate": "' . $this->getValidateUrl() . '"}}',
                ],
                'on_click' => '',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Returns url for ajax validation.
     *
     * @return string
     */
    public function getValidateUrl()
    {
        return $this->urlBuilder->getUrl(
            'company/customer/superUserValidator',
            ['customer_ids' => $this->getCustomerId()]
        );
    }

    /**
     * Returns url for customer removal.
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->urlBuilder->getUrl(
            '*/*/delete',
            ['id' => $this->getCustomerId()]
        );
    }

    /**
     * Return the customer Id.
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->request->getParam($this->customerIdKey);
    }
}
