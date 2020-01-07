<?php
declare(strict_types=1);

namespace Magento\GiftCardAccountGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftCardAccount\Api\GiftCardAccountManagementInterface;
use Magento\GiftCardAccount\Model\Giftcardaccount as ModelGiftcardaccount;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\GiftCardAccountGraphQl\Model\Money\Formatter as MoneyFormatter;
use Magento\GiftCardAccount\Api\GiftCardAccountRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * @inheritdoc
 */
class GetAppliedGiftCardsFromCart implements ResolverInterface
{
    /**
     * @var GiftCardAccountManagementInterface
     */
    private $giftCardAccountManagement;

    /**
     * @var CartTotalRepositoryInterface
     */
    private $cartTotalRepository;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var MoneyFormatter
     */
    private $moneyFormatter;

    /**
     * @var GiftCardAccountRepositoryInterface
     */
    private $giftCardAccountRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @param GiftCardAccountManagementInterface $giftCardAccountManagement
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param Json $json
     * @param GiftCardAccountRepositoryInterface $giftCardAccountRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param MoneyFormatter $moneyFormatter
     */
    public function __construct(
        GiftCardAccountManagementInterface $giftCardAccountManagement,
        CartTotalRepositoryInterface $cartTotalRepository,
        Json $json,
        GiftCardAccountRepositoryInterface $giftCardAccountRepository,
        SearchCriteriaBuilder $criteriaBuilder,
        MoneyFormatter $moneyFormatter
    ) {
        $this->giftCardAccountManagement = $giftCardAccountManagement;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->json = $json;
        $this->giftCardAccountRepository = $giftCardAccountRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->moneyFormatter = $moneyFormatter;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $cart = $value['model'];
        $cartId = $cart->getId();
        /** @var ModelGiftcardaccount $giftCardAccount */
        $giftCardAccount = $this->giftCardAccountManagement->getListByQuoteId($cartId);

        $giftCardAccounts = $this->getByCodes($giftCardAccount->getGiftCards());
        $cartGiftCards = $this->getGiftCardSegmentsFromCart($cartId);
        $appliedGiftCards = [];
        $store = $context->getExtensionAttributes()->getStore();
        foreach ($giftCardAccounts as $giftAccount) {
            $appliedGiftCards[]= [
                'code' => $giftAccount->getCode(),
                'current_balance' => $this->moneyFormatter->formatAmountAsMoney($giftAccount->getBalance(), $store),
                'applied_balance' => $this->moneyFormatter->formatAmountAsMoney(
                    $cartGiftCards[$giftAccount->getCode()][ModelGiftcardaccount::AMOUNT],
                    $store
                ),
                'expiration_date' => $giftAccount->getDateExpires(),
            ];
        }
        return $appliedGiftCards;
    }

    /**
     * Get giftcard segments from the cart
     *
     * @param string $cartId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getGiftCardSegmentsFromCart(string $cartId)
    {
        $cartTotal = $this->cartTotalRepository->get($cartId);
        $totalSegments = $cartTotal->getTotalSegments();
        $cartGiftCards = [];
        if (isset($totalSegments['giftcardaccount'])) {
            /** @var \Magento\Quote\Model\Cart\TotalSegment $totalSegment */
            $totalSegment = $totalSegments['giftcardaccount'];
            $extensionAttributes = $totalSegment->getExtensionAttributes();
            $giftCardsTotals = $this->json->unserialize($extensionAttributes->getGiftCards());
            if (is_array($giftCardsTotals)) {
                foreach ($giftCardsTotals as $giftCardTotal) {
                    if (isset($giftCardTotal[ModelGiftcardaccount::CODE])) {
                        $cartGiftCards[$giftCardTotal[ModelGiftcardaccount::CODE]] = $giftCardTotal;
                    }
                }
            }
        }
        return $cartGiftCards;
    }

    /**
     * Retrieve set of giftcard accounts based on the codes
     *
     * @param array $giftCardCodes
     * @return array
     */
    private function getByCodes(array $giftCardCodes): array
    {
        $found = $this->giftCardAccountRepository->getList(
            $this->criteriaBuilder->addFilter('code', $giftCardCodes, 'in')->create()
        )->getItems();
        return $found;
    }
}
