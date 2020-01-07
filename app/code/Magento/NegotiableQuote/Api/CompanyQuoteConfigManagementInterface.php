<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Api;

use Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface;

/**
 * Interface for managing company quote config
 *
 * @api
 * @since 100.0.0
 */
interface CompanyQuoteConfigManagementInterface
{
    /**
     * Get quote config by company id
     *
     * @param int $companyId
     * @return CompanyQuoteConfigInterface
     */
    public function getByCompanyId($companyId);
}
