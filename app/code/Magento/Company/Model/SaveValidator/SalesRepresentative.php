<?php

namespace Magento\Company\Model\SaveValidator;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Checks if company sales representative exists.
 */
class SalesRepresentative implements \Magento\Company\Model\SaveValidatorInterface
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterface
     */
    private $company;

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $userCollectionFactory;

    /**
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     */
    public function __construct(
        \Magento\Company\Api\Data\CompanyInterface $company,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
    ) {
        $this->company = $company;
        $this->userCollectionFactory = $userCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $salesId = $this->company->getSalesRepresentativeId();
        if ($salesId) {
            /** @var \Magento\User\Model\ResourceModel\User\Collection $userCollection */
            $userCollection = $this->userCollectionFactory->create();
            if (!$userCollection->addFieldToFilter('main_table.user_id', $salesId)->load()->getSize()) {
                throw new NoSuchEntityException(
                    __(
                        'No such entity with %fieldName = %fieldValue',
                        [
                            'fieldName' => 'salesRepresentativeId',
                            'fieldValue' => $salesId
                        ]
                    )
                );
            }
        }
    }
}
