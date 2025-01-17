<?php
namespace Magento\Support\Test\Unit\Model\Report\Group\Modules;

use Magento\Support\Model\Report\Group\Modules\AllModulesSection;

class AllModulesSectionTest extends AbstractModulesSectionTest
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function generateDataProvider()
    {
        $headers = ['Module', 'Code Pool', 'Config Version', 'DB Version', 'DB Data Version', 'Output', 'Enabled'];
        return [
            [
                'className' => \Magento\Support\Model\Report\Group\Modules\AllModulesSection::class,
                'dbVersions' => [
                    'schemaVersions' => [
                        ['Magento_Cms', '2.0.0']
                    ],
                    'dataVersions' => [
                        ['Magento_Cms', '2.0.0']
                    ],
                ],
                'enabledModules' => [
                    ['Magento_Cms', true]
                ],
                'allModules' => [
                    'Magento_Cms' => '2.0.0',
                    'Vendor_HelloWorld' => '1.0.0'
                ],
                'modulesInfo' => [
                    'modulePathMap' => [
                        ['Magento_Cms', 'app/code/Magento/Cms/'],
                        ['Vendor_HelloWorld', 'app/code/Vendor/HelloWorld/']
                    ],
                    'customModuleMap' => [
                        ['Magento_Cms', false],
                        ['Vendor_HelloWorld', true]
                    ],
                    'outputFlagInfoMap' => [
                        ['Magento_Cms', ['[Default Config] => Enabled']],
                        ['Vendor_HelloWorld', ['[Default Config] => Disabled']]
                    ]
                ],
                'expectedResult' => [
                    AllModulesSection::REPORT_TITLE => [
                        'headers' => $headers,
                        'data' => [
                            [
                                'Magento_Cms' . "\n" . '{app/code/Magento/Cms/}',
                                'core',
                                '2.0.0',
                                '2.0.0',
                                '2.0.0',
                                '[Default Config] => Enabled',
                                'Yes'
                            ],
                            [
                                'Vendor_HelloWorld' . "\n" . '{app/code/Vendor/HelloWorld/}',
                                'custom',
                                '1.0.0',
                                'n/a',
                                'n/a',
                                '[Default Config] => Disabled',
                                'No'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'className' => \Magento\Support\Model\Report\Group\Modules\AllModulesSection::class,
                'dbVersions' => [
                    'schemaVersions' => [],
                    'dataVersions' => []
                ],
                'enabledModules' => [],
                'allModules' => [
                    'Vendor_HelloWorld' => '1.0.0'
                ],
                'modulesInfo' => [
                    'modulePathMap' => [
                        ['Vendor_HelloWorld', 'app/code/Vendor/HelloWorld/']
                    ],
                    'customModuleMap' => [
                        ['Vendor_HelloWorld', true]
                    ],
                    'outputFlagInfoMap' => [
                        ['Vendor_HelloWorld', ['[Default Config] => Disabled']]
                    ]
                ],
                'expectedResult' => [
                    AllModulesSection::REPORT_TITLE => [
                        'headers' => $headers,
                        'data' => [
                            [
                                'Vendor_HelloWorld' . "\n" . '{app/code/Vendor/HelloWorld/}',
                                'custom',
                                '1.0.0',
                                'n/a',
                                'n/a',
                                '[Default Config] => Disabled',
                                'No'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
