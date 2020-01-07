<?php

return [
    'communication' => [
        'topics' => [
            'customerCreated' => [
                'name' => 'customerCreated',
                'is_synchronous' => 3,
                'request' => \Magento\Customer\Api\Data\CustomerInterface::class,
                'request_type' => 'object_interface',
                'response' => null,
                'handlers' => [],
            ],
        ]
    ]
];
