<?php

namespace Magento\CompanyCredit\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\CompanyCredit\Action\ReimburseFacade;
use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Psr\Log\LoggerInterface;

/**
 * Controller for credit balance reimbursement from backend.
 */
class Reimburse extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Company::index';

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var ReimburseFacade
     */
    private $reimburseFacade;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceFormatter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param ReimburseFacade $reimburseFacade
     * @param PriceCurrencyInterface $priceFormatter
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ReimburseFacade $reimburseFacade,
        PriceCurrencyInterface $priceFormatter,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->reimburseFacade = $reimburseFacade;
        $this->priceFormatter = $priceFormatter;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $companyId = $this->getRequest()->getParam('id');
        $reimburseBalance = $this->getRequest()->getParam('reimburse_balance');
        $amount = (float)$reimburseBalance['amount'];
        $comment = $reimburseBalance['credit_comment'];
        $purchaseOrder = $reimburseBalance['purchase_order'];

        $result = $this->jsonFactory->create();

        try {
            $credit = $this->reimburseFacade->execute(
                $companyId,
                $amount,
                $comment,
                $purchaseOrder
            );

            $result->setData([
                'status' => 'success',
                'balance' => $this->getCreditResultData($credit)
            ]);
        } catch (LocalizedException $e) {
            $result->setData([
                'status' => 'error',
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $result->setData([
                'status' => 'error',
                'error' => __('Something went wrong. Please try again later.')
            ]);
            $this->logger->critical($e);
        }

        return $result;
    }

    /**
     * Get an array containing information about a company's credit.
     *
     * @param CreditLimitInterface $credit
     * @return array
     */
    private function getCreditResultData(CreditLimitInterface $credit)
    {
        $currency = $credit->getCurrencyCode();

        return [
            'outstanding_balance' => $this->getFormattedPrice($credit->getBalance(), $currency),
            'is_negative' => $credit->getBalance() < 0,
            'credit_limit' => $this->getFormattedPrice($credit->getCreditLimit(), $currency),
            'available_credit' => $this->getFormattedPrice($credit->getAvailableLimit(), $currency)
        ];
    }

    /**
     * Return formatted price.
     *
     * @param float $price
     * @param string $currency
     * @return float
     */
    private function getFormattedPrice($price, $currency)
    {
        return $this->priceFormatter->format(
            $price,
            false,
            null,
            null,
            $currency
        );
    }
}
