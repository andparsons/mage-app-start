<?php
namespace Magento\Company\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\CompanyManagementInterface;

/**
 * Class for saving changes to the company entity performed during the inline edit in admin panel on company grid.
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Company::manage';

    /** @var CompanyInterface */
    private $company;

    /** @var CompanyRepositoryInterface */
    protected $companyRepository;

    /** @var \Magento\Framework\Controller\Result\JsonFactory  */
    protected $resultJsonFactory;

    /** @var \Magento\Framework\Api\DataObjectHelper  */
    protected $dataObjectHelper;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /** @var CompanyManagementInterface */
    protected $companyManagement;

    /**
     * @param Action\Context $context
     * @param CompanyRepositoryInterface $companyRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     */
    public function __construct(
        Action\Context $context,
        CompanyRepositoryInterface $companyRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Psr\Log\LoggerInterface $logger,
        CompanyManagementInterface $companyManagement
    ) {
        parent::__construct($context);
        $this->companyRepository = $companyRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
        $this->companyManagement = $companyManagement;
    }

    /**
     * Save the changes to the company entity performed during the inline edit in admin panel in company grid.
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $companyId) {
            $this->setCompany($this->companyRepository->get($companyId));
            $this->updateCompany($postItems[$companyId]);
            $this->saveCompany($this->getCompany());
        }

        return $resultJson->setData([
            'messages' => $this->getErrorMessages(),
            'error' => $this->isErrorExists()
        ]);
    }

    /**
     * Populate company entity data object with the given data.
     *
     * @param array $data
     * @return void
     */
    protected function updateCompany(array $data)
    {
        $company = $this->getCompany();
        $companyData = $data;
        $this->dataObjectHelper->populateWithArray(
            $company,
            $companyData,
            \Magento\Company\Api\Data\CompanyInterface::class
        );
    }

    /**
     * Save company with error catching.
     *
     * @param CompanyInterface $company
     * @return void
     */
    protected function saveCompany(CompanyInterface $company)
    {
        try {
            $this->companyRepository->save($company);
        } catch (\Magento\Framework\Exception\InputException $e) {
            $this->getMessageManager()->addError(
                '[Company ID: ' . $this->getCompany()->getId() . '] ' . __(
                    'can not be saved'
                )
            );
            $this->logger->critical($e);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->getMessageManager()->addError(
                '[Company ID: ' . $this->getCompany()->getId() . '] ' . __(
                    'can not be saved'
                )
            );
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->getMessageManager()->addError(
                '[Company ID: ' . $this->getCompany()->getId() . '] ' . __(
                    'can not be saved'
                )
            );
            $this->logger->critical($e);
        }
    }

    /**
     * Get an array of error messages of errors occurred during the save process.
     *
     * @return array
     */
    protected function getErrorMessages()
    {
        $messages = [];
        foreach ($this->getMessageManager()->getMessages()->getItems() as $error) {
            $messages[] = $error->getText();
        }
        return $messages;
    }

    /**
     * Check if errors exist.
     * @see \Magento\Company\Model\Company\Save for possible errors.
     *
     * @return bool
     */
    protected function isErrorExists()
    {
        return (bool)$this->getMessageManager()->getMessages(true)->getCount();
    }

    /**
     * Set company.
     *
     * @param CompanyInterface $company
     * @return $this
     */
    protected function setCompany(CompanyInterface $company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Receive company.
     *
     * @return CompanyInterface
     */
    protected function getCompany()
    {
        return $this->company;
    }

    /**
     * Checks if an admin user is allowed to manage companies.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Company::manage');
    }
}
