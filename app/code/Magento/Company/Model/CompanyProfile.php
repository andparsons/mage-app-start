<?php

namespace Magento\Company\Model;

/**
 * Populate company data to company object.
 */
class CompanyProfile
{
    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $objectHelper;

    /**
     * @param \Magento\Framework\Api\DataObjectHelper $objectHelper
     */
    public function __construct(
        \Magento\Framework\Api\DataObjectHelper $objectHelper
    ) {
        $this->objectHelper = $objectHelper;
    }

    /**
     * Populate company profile.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @param array $data
     * @return void
     */
    public function populate(\Magento\Company\Api\Data\CompanyInterface $company, array $data)
    {
        $this->objectHelper->populateWithArray(
            $company,
            $data,
            \Magento\Company\Api\Data\CompanyInterface::class
        );
    }
}
