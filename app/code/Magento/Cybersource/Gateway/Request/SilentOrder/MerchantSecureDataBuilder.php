<?php
namespace Magento\Cybersource\Gateway\Request\SilentOrder;

use Magento\Framework\Config\ScopeInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class MerchantSecureDataBuilder
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class MerchantSecureDataBuilder implements BuilderInterface
{
    const MERCHANT_SECURE_DATA1 = 'merchant_secure_data1';

    const MERCHANT_SECURE_DATA2 = 'merchant_secure_data2';

    const MERCHANT_SECURE_DATA3 = 'merchant_secure_data3';

    /**
     * @var ScopeInterface
     */
    private $scope;

    /**
     * @param ScopeInterface $scope
     */
    public function __construct(ScopeInterface $scope)
    {
        $this->scope = $scope;
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
        return [
            static::MERCHANT_SECURE_DATA1 => $paymentDO->getOrder()->getId(),
            static::MERCHANT_SECURE_DATA2 => $paymentDO->getOrder()->getStoreId(),
            static::MERCHANT_SECURE_DATA3 => $this->scope->getCurrentScope()
        ];
    }
}
