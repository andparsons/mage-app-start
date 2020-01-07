<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\NegotiableQuote\Api\CompanyQuoteConfigRepositoryInterface;
use Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface;
use Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterfaceFactory;

/**
 * Company quote config repository object.
 */
class CompanyQuoteConfigRepository implements CompanyQuoteConfigRepositoryInterface
{

    /**
     * Company quote config factory.
     *
     * @var CompanyQuoteConfigInterfaceFactory
     */
    protected $companyQuoteConfigFactory;

    /**
     * Company quote config resource
     *
     * @var ResourceModel\CompanyQuoteConfig $companyQuoteConfigResource
     */
    protected $companyQuoteConfigResource;

    /**
     * @param CompanyQuoteConfigInterfaceFactory $companyQuoteConfigFactory
     * @param ResourceModel\CompanyQuoteConfig $companyQuoteConfigResource
     */
    public function __construct(
        CompanyQuoteConfigInterfaceFactory $companyQuoteConfigFactory,
        ResourceModel\CompanyQuoteConfig $companyQuoteConfigResource
    ) {
        $this->companyQuoteConfigFactory = $companyQuoteConfigFactory;
        $this->companyQuoteConfigResource = $companyQuoteConfigResource;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CompanyQuoteConfigInterface $quoteConfig)
    {
        try {
            $this->companyQuoteConfigResource->save($quoteConfig);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('There was an error saving company quote config.'));
        }

        return true;
    }
}
