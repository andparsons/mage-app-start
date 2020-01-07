<?php
namespace Magento\CompanyCredit\Test\Unit\Block\Customer;

use Magento\Company\Api\AuthorizationInterface;
use Magento\CompanyCredit\Block\Customer\Link;

/**
 * Class LinkTest
 */
class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Link
     */
    private $model;

    public function testConstruct()
    {
        $resourceValue = 'resource_value';
        $data = [
            'resource' => $resourceValue,
        ];

        $authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Link::class,
            [
                'authorization' => $authorizationMock,
                'data' => $data,
            ]
        );

        $authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with($resourceValue);

        $this->model->toHtml();
    }

    public function testConstructWithoutResource()
    {
        $authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Link::class,
            [
                'authorization' => $authorizationMock,
            ]
        );

        $authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with(null);

        $this->model->toHtml();
    }
}
