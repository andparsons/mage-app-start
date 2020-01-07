<?php
namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class for getting customer group from negotiable quote session for tier prices.
 */
class CustomerGroupRetriever implements \Magento\Customer\Model\Group\RetrieverInterface
{
    /**
     * @var \Magento\Customer\Model\Group\RetrieverInterface
     */
    private $customerGroupRetriever;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var NegotiableQuoteManagementInterface
     */
    private $negotiableQuoteManagement;

    /**
     * @param RequestInterface $request
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\Customer\Model\Group\RetrieverInterface $customerGroupRetriever
     */
    public function __construct(
        RequestInterface $request,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\Customer\Model\Group\RetrieverInterface $customerGroupRetriever
    ) {
        $this->request = $request;
        $this->negotiableQuoteManagement = $negotiableQuoteManagement;
        $this->customerGroupRetriever = $customerGroupRetriever;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupId()
    {
        if ($this->request->getParam('quote_id')) {
            try {
                $quote = $this->negotiableQuoteManagement->getNegotiableQuote($this->request->getParam('quote_id'));
                return $quote->getCustomer()->getGroupId();
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                //do nothing if negotiable quote isn't exist
            }
        }
        return $this->customerGroupRetriever->getCustomerGroupId();
    }
}
