<?php

namespace Magento\NegotiableQuote\Model\Plugin\User\Model;

/**
 * This plugin detects changes and admin account and stores appropriate data in quotes.
 */
class User
{
    const FIELD_FIRSTNAME = 'firstname';
    const FIELD_LASTNAME = 'lastname';

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid
     */
    private $quoteGrid;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Extractor
     */
    private $extractor;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Handler
     */
    private $purgedContentsHandler;

    /**
     * User constructor.
     *
     * @param \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid $quoteGrid
     * @param \Magento\NegotiableQuote\Model\Purged\Extractor $extractor
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Company\Model\ResourceModel\Customer $customerResource
     * @param \Magento\NegotiableQuote\Model\Purged\Handler $purgedContentsHandler
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid $quoteGrid,
        \Magento\NegotiableQuote\Model\Purged\Extractor $extractor,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Company\Model\ResourceModel\Customer $customerResource,
        \Magento\NegotiableQuote\Model\Purged\Handler $purgedContentsHandler
    ) {
        $this->quoteGrid = $quoteGrid;
        $this->extractor = $extractor;
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerResource = $customerResource;
        $this->purgedContentsHandler = $purgedContentsHandler;
    }

    /**
     * Update quote after company changed.
     *
     * @param \Magento\User\Api\Data\UserInterface $subject
     * @param \Closure $proceed
     * @return \Magento\User\Api\Data\UserInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\User\Api\Data\UserInterface $subject,
        \Closure $proceed
    ) {
        $userNameChanged = $this->hasNameChanges($subject);

        /** @var \Magento\User\Model\User $result */
        $result = $proceed();

        if ($userNameChanged) {
            $this->quoteGrid->refreshValue(
                \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::SALES_REP_ID,
                $subject->getId(),
                \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::SALES_REP,
                $this->getSalesRepName($subject)
            );
        }

        return $result;
    }

    /**
     * Check if user has name changes.
     *
     * @param \Magento\User\Api\Data\UserInterface $user
     * @return bool
     */
    private function hasNameChanges(\Magento\User\Api\Data\UserInterface $user)
    {
        $hasNameChanges = false;

        if ($user->getId() && $user->hasDataChanges()) {
            $hasNameChanges = $user->dataHasChangedFor(self::FIELD_FIRSTNAME)
                || $user->dataHasChangedFor(self::FIELD_LASTNAME);
        }

        return $hasNameChanges;
    }

    /**
     * Retrieve sales representative full name.
     *
     * @param \Magento\User\Api\Data\UserInterface $user
     * @return string
     */
    private function getSalesRepName(\Magento\User\Api\Data\UserInterface $user)
    {
        $userName = $user->getFirstName() . ' ' . $user->getLastName();

        return $userName;
    }

    /**
     * Store admin date in quote before admin delete.
     *
     * @param \Magento\User\Api\Data\UserInterface $subject
     * @return void
     */
    public function beforeDelete(
        \Magento\User\Api\Data\UserInterface $subject
    ) {
        $associatedCustomerData = $this->extractor->extractUser($subject);
        $builder = $this->searchCriteriaBuilder->addFilter('sales_representative_id', $subject->getId());
        $companyList = $this->companyRepository->getList($builder->create())->getItems();

        foreach ($companyList as $company) {
            $userIds = $this->customerResource->getCustomerIdsByCompanyId($company->getId());

            foreach ($userIds as $userId) {
                $this->purgedContentsHandler->process($associatedCustomerData, $userId, false);
            }
        }
    }
}
