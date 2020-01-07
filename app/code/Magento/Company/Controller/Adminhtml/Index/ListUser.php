<?php
namespace Magento\Company\Controller\Adminhtml\Index;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

/**
 * Class ListUser.
 */
class ListUser extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $customerCollection;

    /**
     * ListUser constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param CollectionFactory $customerCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        CollectionFactory $customerCollectionFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->customerCollection = $customerCollectionFactory->create();
    }

    /**
     * Execute.
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $result = [];
        try {
            $email = $this->getRequest()->getParam('email');
            $this->customerCollection->addFieldToFilter(
                'email',
                [
                    'like' => $email . '%'
                ]
            );
            foreach ($this->customerCollection as $customer) {
                $result[] = $customer->getEmail();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        } catch (\Exception $e) {
            $result = [
                'error' => __('Something went wrong. Please try again later.'),
                'errorcode' => $e->getCode()
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }
}
