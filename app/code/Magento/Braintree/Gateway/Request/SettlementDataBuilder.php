<?php
namespace Magento\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class SettlementDataBuilder
 */
class SettlementDataBuilder implements BuilderInterface
{
    const SUBMIT_FOR_SETTLEMENT = 'submitForSettlement';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            'options' => [
                self::SUBMIT_FOR_SETTLEMENT => true
            ]
        ];
    }
}
