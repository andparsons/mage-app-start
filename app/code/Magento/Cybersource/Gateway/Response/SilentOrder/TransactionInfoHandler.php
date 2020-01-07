<?php
namespace Magento\Cybersource\Gateway\Response\SilentOrder;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Class TransactionInfoHandler
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class TransactionInfoHandler implements HandlerInterface
{
    /**
     * Request suffix
     */
    const REQUEST_SUFFIX = 'req_';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $fieldsToStore = explode(',', $this->config->getValue('paymentInfoKeys'));

        $paymentDO = SubjectReader::readPayment($handlingSubject);

        foreach ($fieldsToStore as $field) {
            $requestFieldName = null;
            if (isset($response[$field])) {
                $requestFieldName = $field;
            } elseif (isset($response[self::REQUEST_SUFFIX . $field])) {
                $requestFieldName = self::REQUEST_SUFFIX . $field;
            }

            if (!$requestFieldName) {
                continue;
            }

            $paymentDO->getPayment()->setAdditionalInformation(
                $field,
                $response[$requestFieldName]
            );
        }
    }
}
