<?php
return [
    'publishers' => [
        'amqp-magento' => [
            'name' => 'amqp-magento',
            'connection' => 'db',
            'exchange' => 'magento-db'
        ],
    ],
    'consumers' => [
        'inventoryQtyCounter' => [
            'connection' => 'db'
        ],
    ]
];
