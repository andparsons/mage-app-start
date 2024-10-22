<?php
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * Test class for \Magento\Sales\Model\Order\Config
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $orderConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->orderConfig = $this->objectManager->create(Config::class);
    }

    /**
     * Correct display of the list of "Order States" after assigning
     * the state "complete" to a custom order status.
     *
     * @magentoDataFixture Magento/Sales/_files/order_status_assign_state_complete.php
     */
    public function testCorrectCompleteStatusInStatesList()
    {
        $allStates = $this->orderConfig->getStates();
        /** @var Status $completeStatus */
        $completeStatus = $this->objectManager->create(Status::class)
            ->load(\Magento\Sales\Model\Order::STATE_COMPLETE);
        $completeState = $allStates[$completeStatus->getStatus()];

        $this->assertEquals($completeStatus->getLabel(), $completeState->getText());
    }
}
