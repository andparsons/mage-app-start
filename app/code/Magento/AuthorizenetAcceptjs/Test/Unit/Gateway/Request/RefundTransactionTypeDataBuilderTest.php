<?php

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\RefundTransactionTypeDataBuilder;
use PHPUnit\Framework\TestCase;

class RefundTransactionTypeDataBuilderTest extends TestCase
{
    private const REQUEST_TYPE_REFUND = 'refundTransaction';

    public function testBuild()
    {
        $builder = new RefundTransactionTypeDataBuilder();

        $expected = [
            'transactionRequest' => [
                'transactionType' => self::REQUEST_TYPE_REFUND
            ]
        ];

        $this->assertEquals($expected, $builder->build([]));
    }
}
