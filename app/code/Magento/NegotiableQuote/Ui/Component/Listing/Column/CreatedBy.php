<?php
namespace Magento\NegotiableQuote\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\NegotiableQuote\Model\Creator;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class for replacing customer id to customer and creator name in UI grid.
 */
class CreatedBy extends Column
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerNameGeneration;

    /**
     * @var \Magento\NegotiableQuote\Model\Creator
     */
    private $creator;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Customer\Api\CustomerNameGenerationInterface $customerNameGeneration
     * @param Creator $creator
     * @param array $components [optional]
     * @param array $data [optional]
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Api\CustomerNameGenerationInterface $customerNameGeneration,
        Creator $creator,
        array $components = [],
        array $data = []
    ) {
        $this->customerRepository = $customerRepositoryInterface;
        $this->customerNameGeneration = $customerNameGeneration;
        $this->creator = $creator;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Replace customer id to customer and creator name in $dataSource.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
                $customer = $this->customerRepository->getById($item['customer_id']);
                $customerName = $this->customerNameGeneration->getCustomerName($customer);
                $adminTypes = [UserContextInterface::USER_TYPE_ADMIN, UserContextInterface::USER_TYPE_INTEGRATION];
                if (in_array($item['creator_type'], $adminTypes)) {
                    $creatorName = $this->creator->retrieveCreatorName($item['creator_type'], $item['creator_id']);
                    $customerName = __(
                        '%creator (for %customer)',
                        ['creator' => $creatorName, 'customer' => $customerName]
                    );
                }
                $item[$this->getData('name')] = $customerName;
            }
        }

        return $dataSource;
    }
}
