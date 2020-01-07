<?php
declare(strict_types=1);

namespace Magento\SalesRuleStaging\Test\Unit\Model;

use Magento\SalesRule\Model\Rule;
use Magento\SalesRuleStaging\Model\SalesRuleStagingAdapter;
use Magento\Staging\Model\ResourceModel\Db\CampaignValidator;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for SalesRuleStagingAdapter
 */
class SalesRuleStagingAdapterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SalesRuleStagingAdapter
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var CampaignValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignValidator;

    /**
     * @var Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rule;

    /**
     * @var int
     */
    private $version;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $this->campaignValidator = $this->getMockBuilder(CampaignValidator::class)
            ->disableOriginalConstructor()
            ->setMethods(['canBeScheduled'])
            ->getMock();
        $this->rule = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->version = 2;

        $this->model = $this->objectManager->getObject(
            SalesRuleStagingAdapter::class,
            [
                'entityManager' => $this->entityManager,
                'campaignValidator' => $this->campaignValidator
            ]
        );
    }

    public function testSchedule()
    {
        $arguments = ['created_in' => $this->version];

        $this->campaignValidator->expects($this->once())
            ->method('canBeScheduled')
            ->with($this->rule, $this->version, null)
            ->willReturn(true);
        $this->entityManager->expects($this->once())
            ->method('save')
            ->with($this->rule, $arguments)
            ->willReturn(true);

        $this->assertSame(true, $this->model->schedule($this->rule, $this->version));
    }

    /**
     * @expectedException \Magento\Framework\Exception\ValidatorException
     */
    public function testScheduleWithException()
    {
        $this->campaignValidator->expects($this->once())
            ->method('canBeScheduled')
            ->with($this->rule, $this->version, null)
            ->willReturn(false);
        $this->model->schedule($this->rule, $this->version);
    }
}
