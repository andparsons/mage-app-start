<?php
return [
    'config' => [
        'publishers' => [
            'inventory.counter.updated' => [
                'connections' => [
                    'amqp' => [
                        'name' => 'db',
                        'exchange' => 'magento-db'
                    ],
                ]
            ]
        ],
        'consumers' => [
            'inventoryQtyCounter' => [
                'connection' => 'db'
            ]
        ]
    ]
];
