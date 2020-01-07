<?php
namespace Magento\Company\Controller\Adminhtml\Email;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Validate company controller.
 */
class Validate extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
    ) {
        parent::__construct($context);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->companyRepository = $companyRepository;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $isCompanyEmailAvailable = $this->isCompanyEmailAvailable($this->getRequest()->getParam('company_email'));

        $resultJson->setData(
            [
                'is_company_email_available' => $isCompanyEmailAvailable,
            ]
        );

        return $resultJson;
    }

    /**
     * Check if there are no companies with this email.
     *
     * @param string $email
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isCompanyEmailAvailable($email)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(CompanyInterface::COMPANY_EMAIL, $email)
            ->create();
        return !$this->companyRepository->getList($searchCriteria)->getTotalCount();
    }
}
