<?php
namespace Magento\Signifyd\Model\CaseServices;

use Magento\Signifyd\Api\Data\CaseInterface;

/**
 * Stub implementation for case updating service interface and might be used
 * for test Signifyd webhooks
 */
class StubUpdatingService implements UpdatingServiceInterface
{
    /**
     * @inheritdoc
     */
    public function update(CaseInterface $case, array $data)
    {
        // just stub method and doesn't contain any logic
    }
}
