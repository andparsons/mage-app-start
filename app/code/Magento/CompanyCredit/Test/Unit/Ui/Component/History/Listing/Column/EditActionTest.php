<?php

namespace Magento\CompanyCredit\Test\Unit\Ui\Component\History\Listing\Column;

/**
 * Class EditActionTest.
 */
class EditActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Ui\Component\History\Listing\Column\EditAction
     */
    private $editActionColumn;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $context = $this->createMock(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class
        );
        $processor = $this->createMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class
        );
        $context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $context->expects($this->once())->method('getFilterParam')->willReturn(1);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->editActionColumn = $objectManager->getObject(
            \Magento\CompanyCredit\Ui\Component\History\Listing\Column\EditAction::class,
            [
                'context' => $context,
                'urlBuilder' => $this->urlBuilder
            ]
        );
        $this->editActionColumn->setData('name', 'action');
    }

    /**
     * Test method for prepareDataSource.
     *
     * @return void
     */
    public function testPrepareDataSource()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    ['entity_id' => 1, 'type' => 1, 'comment' => 'test1'],
                    ['entity_id' => 1, 'type' => 4, 'comment' => 'test2'],
                ]
            ]
        ];

        $creditModal = 'company_form.company_form.modalContainer.company_credit_form_modal';
        $amountField = $creditModal . '.reimburse_balance.amount';
        $expected = [
            'data' => [
                'items' => [
                    ['entity_id' => 1, 'type' => 1, 'comment' => 'test1'],
                    [
                        'entity_id' => 1,
                        'type' => 4,
                        'comment' => 'test2',
                        'credit_comment' => 'test2',
                        'action' => [
                            'edit' => [
                                'href' => 'credit/*/edit/id/1',
                                'label' => __('Edit'),
                                'hidden' => false,
                                'callback' => [
                                    [
                                        'provider' => $creditModal,
                                        'target' => 'openModal',
                                        'params' => [
                                            'url' => 'credit/*/edit/id/1',
                                            'item' => [
                                                'entity_id' => 1,
                                                'type' => 4,
                                                'comment' => 'test2',
                                                'credit_comment' => 'test2'
                                            ],
                                        ],
                                    ],
                                    [
                                        'provider' => $amountField,
                                        'target' => 'disable'
                                    ]
                                ]
                            ]
                        ],
                    ],
                ]
            ]
        ];

        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')
            ->with('credit/*/edit', ['id' => 1, 'store' => 1])->willReturn('credit/*/edit/id/1');

        $this->assertEquals($expected, $this->editActionColumn->prepareDataSource($dataSource));
    }
}
