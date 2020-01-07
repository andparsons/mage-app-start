<?php
return [
    '@' => ['type' => \Magento\Cms\Block\Widget\Page\Link::class, 'module' => 'Magento_Cms'],
    'name' => 'CMS Link 2',
    'description' => 'Second Link Example',
    'parameters' => [
        'types' => [
            'type' => 'multiselect',
            'visible' => '1',
            'source_model' => \Magento\Cms\Model\Config\Source\Page::class,
        ],
    ]
];
