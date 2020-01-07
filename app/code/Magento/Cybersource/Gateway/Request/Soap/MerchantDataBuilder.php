<?php
namespace Magento\Cybersource\Gateway\Request\Soap;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Cybersource\Gateway\Request\SilentOrder\TransactionDataBuilder;

/**
 * Adds merchant data to request.
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class MerchantDataBuilder implements BuilderInterface
{
    /**
     * Merchant id key
     */
    const MERCHANT_ID = 'merchant_id';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $storeId = $paymentDO->getOrder()->getStoreId();

        return [
            'merchantID' => $this->config->getValue(self::MERCHANT_ID, $storeId),
            'merchantReferenceCode' => $paymentDO->getPayment()
                ->getAdditionalInformation(
                    TransactionDataBuilder::REFERENCE_NUMBER
                )
        ];
    }
}
