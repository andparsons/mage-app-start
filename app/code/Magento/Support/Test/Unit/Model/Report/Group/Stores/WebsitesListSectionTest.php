<?php
namespace Magento\Support\Test\Unit\Model\Report\Group\Stores;

class WebsitesListSectionTest extends AbstractTest
{
    protected function setUp()
    {
        parent::prepareObjects(\Magento\Support\Model\Report\Group\Stores\WebsitesListSection::class);
    }

    public function testGenerate()
    {
        $websites = [
            '1' => $this->getWebsiteMock(
                [
                    'id' => '1',
                    'name' => 'Main Website',
                    'code' => 'base',
                    'is_default' => '1',
                    'default_group' => $this->getStoreMock(
                        ['id' => '1', 'name' => 'Main Website Store']
                    ),
                    'default_store' => $this->getStoreViewMock(
                        ['id' => '1', 'name' => 'Default Store View']
                    )
                ]
            )
        ];
        $expectedResult = [
            (string)__('Websites List') => [
                'headers' => [
                    __('ID'), __('Name'), __('Code'), __('Is Default'),
                    __('Default Store'), __('Default Store View')
                ],
                'data' => [
                    ['1', 'Main Website', 'base', 'Yes', 'Main Website Store {ID:1}', 'Default Store View {ID:1}']
                ]
            ]
        ];

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn($websites);

        $this->assertEquals($expectedResult, $this->section->generate());
    }
}
