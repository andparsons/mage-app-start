<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\CompanyQuoteConfigManagementInterface;
use Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterfaceFactory;

/**
 * Class for managing quotes
 */
class CompanyQuoteConfigManagement implements CompanyQuoteConfigManagementInterface
{
    /**
     * Company quote config factory.
     *
     * @var CompanyQuoteConfigInterfaceFactory
     */
    protected $companyQuoteConfigFactory;

    /**
     * @param CompanyQuoteConfigInterfaceFactory $companyQuoteConfigFactory
     */
    public function __construct(
        CompanyQuoteConfigInterfaceFactory $companyQuoteConfigFactory
    ) {
        $this->companyQuoteConfigFactory = $companyQuoteConfigFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getByCompanyId($companyId)
    {
        return $this->companyQuoteConfigFactory->create()->load($companyId);
    }
}
