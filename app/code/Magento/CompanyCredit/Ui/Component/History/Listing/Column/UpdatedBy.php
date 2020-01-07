<?php

namespace Magento\CompanyCredit\Ui\Component\History\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class UpdatedBy.
 */
class UpdatedBy extends Column
{
    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $nameGeneration;

    /**
     * UpdatedBy constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerNameGenerationInterface $nameGeneration
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\User\Model\UserFactory $userFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerNameGenerationInterface $nameGeneration,
        array $components = [],
        array $data = []
    ) {
        $this->userFactory = $userFactory;
        $this->customerRepository = $customerRepository;
        $this->nameGeneration = $nameGeneration;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName])) {
                    $userType = $item['user_type'];
                    $item[$fieldName] = $this->getUserName($item[$fieldName], $userType);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get user name.
     *
     * @param int $key
     * @param int $userType
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getUserName($key, $userType)
    {
        $userName = '';
        if ($userType == UserContextInterface::USER_TYPE_ADMIN) {
            $userName = $this->userFactory->create()->load($key)->getName();
        } elseif ($userType == UserContextInterface::USER_TYPE_CUSTOMER) {
            $user = $this->customerRepository->getById($key);
            $userName = $this->nameGeneration->getCustomerName($user);
        }

        return $userName;
    }
}
