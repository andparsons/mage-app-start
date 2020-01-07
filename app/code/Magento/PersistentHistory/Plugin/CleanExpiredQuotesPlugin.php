<?php
namespace Magento\PersistentHistory\Plugin;

/**
 * Class CleanExpiredQuotesPlugin
 */
class CleanExpiredQuotesPlugin
{
    /**
     * @param \Magento\Sales\Cron\CleanExpiredQuotes $subject
     * @return void
     */
    public function beforeExecute(\Magento\Sales\Cron\CleanExpiredQuotes $subject)
    {
        $subject->setExpireQuotesAdditionalFilterFields(['is_persistent' => 0]);
    }
}
