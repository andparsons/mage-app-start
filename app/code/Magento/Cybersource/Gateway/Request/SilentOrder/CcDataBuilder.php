<?php

namespace Magento\Cybersource\Gateway\Request\SilentOrder;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class CcDataBuilder
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class CcDataBuilder implements BuilderInterface
{
    const CARD_TYPE = 'card_type';

    const CARD_NUMBER = 'card_number';

    const CARD_EXPIRY_DATE = 'card_expiry_date';

    const CARD_CVN = 'card_cvn';

    const PAYMENT_METHOD = 'payment_method';

    /**
     * Map for CC type field. Magento scope => Cybersource scope
     *
     * @var array
     */
    private static $ccTypeMap = [
        'AE' => '003',
        'VI' => '001',
        'MC' => '002',
        'DI' => '004',
        'DN' => '005',
        'JCB' => '007',
        'MD' => '024',
        'MI' => '042'
    ];

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject)
    {
        if (!isset(
            $buildSubject['cc_type'],
            self::$ccTypeMap[$buildSubject['cc_type']]
        )) {
            throw new LocalizedException(
                __('The credit card type field needs to be provided. Select the field and try again.')
            );
        }

        return [
            self::CARD_TYPE => self::$ccTypeMap[$buildSubject['cc_type']],
            self::CARD_NUMBER => '',
            self::CARD_EXPIRY_DATE => '',
            self::CARD_CVN => '',
            self::PAYMENT_METHOD => 'card'
        ];
    }
}
