<?php
namespace Magento\CompanyCredit\Model;

/**
 * Class CompanyStatus is used to check company status.
 */
class CompanyStatus
{
    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
    ) {
        $this->companyRepository = $companyRepository;
    }

    /**
     * Check if company exists and refund operation can be performed for it.
     *
     * @param int $companyId
     * @return bool
     */
    public function isRefundAvailable($companyId)
    {
        $company = $this->getCompany($companyId);
        return $company && in_array(
            $company->getStatus(),
            [
                \Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED,
                \Magento\Company\Api\Data\CompanyInterface::STATUS_BLOCKED,
            ]
        );
    }

    /**
     * Check if company exists and revert operation can be performed for it.
     *
     * @param int $companyId
     * @return bool
     */
    public function isRevertAvailable($companyId)
    {
        $company = $this->getCompany($companyId);
        return $company && !in_array(
            $company->getStatus(),
            [
                \Magento\Company\Api\Data\CompanyInterface::STATUS_PENDING,
                \Magento\Company\Api\Data\CompanyInterface::STATUS_REJECTED,
            ]
        );
    }

    /**
     * Get company by ID.
     *
     * @param int $companyId
     * @return \Magento\Company\Api\Data\CompanyInterface|null
     */
    private function getCompany($companyId)
    {
        try {
            $company = $this->companyRepository->get($companyId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $company = null;
        }

        return $company;
    }
}
