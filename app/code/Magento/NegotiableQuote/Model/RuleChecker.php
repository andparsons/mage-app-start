<?php

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Helper\Quote as QuoteHelper;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\NegotiableQuote\Model\Discount\StateChanges\Applier;

/**
 * Class that verifies whether discount has been removed from quote and logs changes.
 */
class RuleChecker
{
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var HistoryManagementInterface
     */
    private $historyManagement;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var Applier
     */
    private $messageApplier;

    /**
     * @param QuoteHelper $quoteHelper
     * @param CustomerFactory $customerFactory
     * @param HistoryManagementInterface $historyManagement
     * @param RuleRepositoryInterface $ruleRepository
     * @param Applier $messageApplier
     */
    public function __construct(
        QuoteHelper $quoteHelper,
        CustomerFactory $customerFactory,
        HistoryManagementInterface $historyManagement,
        RuleRepositoryInterface $ruleRepository,
        Applier $messageApplier
    ) {
        $this->quoteHelper = $quoteHelper;
        $this->customerFactory = $customerFactory;
        $this->historyManagement = $historyManagement;
        $this->ruleRepository = $ruleRepository;
        $this->messageApplier = $messageApplier;
    }

    /**
     * Check quote for removed discount.
     *
     * @param CartInterface $quote
     * @param string $oldRuleIds
     * @param bool $needLogChanges [optional]
     * @return bool
     */
    public function checkIsDiscountRemoved(CartInterface $quote, $oldRuleIds, $needLogChanges = true)
    {
        $isChanges = false;
        if ($quote->getId() && $oldRuleIds) {
            $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
            $appliedRuleIds = $negotiableQuote->getAppliedRuleIds();
            $oldRuleIdsArray = explode(',', $oldRuleIds);
            $newRuleIdsArray = explode(',', $appliedRuleIds);
            if ($oldRuleIds != $appliedRuleIds &&
                $appliedRuleIds == '' ||
                count($oldRuleIdsArray) > count($newRuleIdsArray)
            ) {
                $removedDiscount = array_diff($oldRuleIdsArray, $newRuleIdsArray);
                if (count($removedDiscount) > 0) {
                    $ruleId = array_pop($removedDiscount);
                    try {
                        $rule = $this->ruleRepository->getById($ruleId);
                        // discount removed, when usage limit is reached
                        $this->applyDiscountRemoved($rule, $quote);
                    } catch (\Exception $e) {
                        $this->messageApplier->setIsDiscountRemoved($quote);
                        $rule = null;
                    }
                    if ($needLogChanges) {
                        $this->addNotificationToHistoryLog($quote, $rule);
                    }
                    $isChanges = true;
                }
            }
        }
        return $isChanges;
    }

    /**
     * Set discount message code to message applier.
     *
     * @param RuleInterface $rule
     * @param CartInterface $quote
     * @return void
     */
    private function applyDiscountRemoved(RuleInterface $rule, CartInterface $quote)
    {
        if ($rule->getRuleId()) {
            if ($this->isUsageLimitReached($rule, $quote)) {
                $this->messageApplier->setIsDiscountRemovedLimit($quote);
            } else {
                $this->messageApplier->setIsDiscountRemoved($quote);
            }
        }
    }

    /**
     * Get rule amount.
     *
     * @param RuleInterface $rule
     * @return string
     */
    private function getRuleAmount(RuleInterface $rule)
    {
        $ruleAmount = __('discount');
        if ($rule->getRuleId()) {
            if ($rule->getSimpleAction() == RuleInterface::DISCOUNT_ACTION_BY_PERCENT) {
                $ruleAmount = __('%1%', intval($rule->getDiscountAmount()));
            } else {
                $ruleAmount = $this->quoteHelper->formatPrice($rule->getDiscountAmount());
            }
        }
        return $ruleAmount;
    }

    /**
     * Check is usage limit reached.
     *
     * @param RuleInterface $rule
     * @param CartInterface $quote
     * @return bool
     */
    private function isUsageLimitReached(RuleInterface $rule, CartInterface $quote)
    {
        $ruleId = $rule->getRuleId();
        if ($ruleId && $rule->getUsesPerCustomer()) {
            $customerId = $quote->getCustomer()->getId();
            /** @var \Magento\SalesRule\Model\Rule\Customer $ruleCustomer */
            $ruleCustomer = $this->customerFactory->create();
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Add info about deleted discount to the history log.
     *
     * @param CartInterface $quote
     * @param RuleInterface|null $rule [optional]
     * @return void
     */
    private function addNotificationToHistoryLog(CartInterface $quote, RuleInterface $rule = null)
    {
        $logRecord['field_title'] = __('Quote Discount')->__toString();
        $logRecord['field_id'] = 'discount';
        if ($rule && $rule->getRuleId()) {
            $ruleAmount = (string)$this->getRuleAmount($rule);
            $logRecord['values'][] = [
                'field_id' => 'rule_remove_' . $rule->getRuleId(),
                'new_value' => __('%1 - deleted', (string)$ruleAmount)->__toString()
            ];
        } else {
            $logRecord['values'][] = [
                'field_id' => 'rule_remove',
                'new_value' => __('Cart rule deleted.')->__toString()
            ];
        }

        $this->historyManagement->addCustomLog(
            $quote->getId(),
            [$logRecord],
            false,
            true
        );
    }
}
