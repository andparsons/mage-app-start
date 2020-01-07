<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Webapi\GiftCardAccount;

/**
 * Class GiftCardAccountManagementTest
 */
class GiftCardAccountManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GiftCardAccount\Api\GiftCardAccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $originalInterface;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var int
     */
    private $cartId = 1;

    /**
     * @var string
     */
    private $giftCartCode = 'gift_card_code';

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\GiftCardAccount\GiftCardAccountManagement
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    private $giftCardAccountManagement;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->originalInterface =
            $this->createMock(\Magento\GiftCardAccount\Api\GiftCardAccountManagementInterface::class);
        $this->validator = $this->createMock(\Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->giftCardAccountManagement = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Webapi\GiftCardAccount\GiftCardAccountManagement::class,
            [
                'originalInterface' => $this->originalInterface,
                'validator' => $this->validator
            ]
        );
    }

    /**
     * Test deleteQuoteById
     */
    public function testDeleteByQuoteId()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        $this->originalInterface->expects($this->any())->method('deleteByQuoteId')->willReturn(true);

        $this->assertEquals(
            true,
            $this->giftCardAccountManagement->deleteByQuoteId($this->cartId, $this->giftCartCode)
        );
    }

    /**
     * Test saveByQuoteId
     */
    public function testSaveByQuoteId()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        $this->originalInterface->expects($this->any())->method('saveByQuoteId')->willReturn(true);
        /**
         * @var \Magento\GiftCardAccount\Api\Data\GiftCardAccountInterface $giftCardAccountData
         */
        $giftCardAccountData = $this->createMock(\Magento\GiftCardAccount\Api\Data\GiftCardAccountInterface::class);

        $this->assertEquals(true, $this->giftCardAccountManagement->saveByQuoteId($this->cartId, $giftCardAccountData));
    }
}
