<?php

namespace Magento\NegotiableQuote\Controller\Adminhtml;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Quote
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Quote extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_NegotiableQuote::view_quotes';

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var NegotiableQuoteManagementInterface
     */
    protected $negotiableQuoteManagement;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Action\Context $context
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     */
    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->negotiableQuoteManagement = $negotiableQuoteManagement;
    }

    /**
     * Initialize order model instance
     *
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    protected function getQuote()
    {
        $id = $this->getRequest()->getParam('quote_id');
        try {
            $quote = $this->quoteRepository->get($id, ['*']);
            if (!$this->canViewQuote($quote)) {
                throw new NoSuchEntityException();
            }
        } catch (NoSuchEntityException $e) {
            $this->addNotFoundError();
            return null;
        } catch (InputException $e) {
            $this->addNotFoundError();
            return null;
        }

        return $quote;
    }

    /**
     * Add not found error to message manager
     *
     * @return void
     */
    protected function addNotFoundError()
    {
        $this->messageManager->addError(__('Requested quote was not found'));
        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
    }

    /**
     * Can view quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    protected function canViewQuote(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        return (bool) $quote->getExtensionAttributes()
            ->getNegotiableQuote()->getIsRegularQuote();
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initAction()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Sales::sales_order');
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('Quotes'), __('Quotes'));
        return $resultPage;
    }

    /**
     * Retrieve redirect url
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function getRedirect(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($quote && $quote->getId()) {
            $resultRedirect->setPath(
                'quotes/quote/view',
                ['quote_id' => $quote->getId()]
            );
        } else {
            $resultRedirect->setPath('quotes/quote');
        }
        return $resultRedirect;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function getResultJson()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_JSON);
    }
}
