<?php

return [
    'communication' => [
        'topics' => [
            'customerCreated' => [
                'name' => 'customerCreated',
                'is_synchronous' => false,
                'request' => \Magento\Customer\Api\Data\CustomerInterface::class,
                'request_type' => 'object_interface',
                'response' =>  null,
                'handlers' => [],
                'some_incorrect_key' => 'value'
            ],
        ]
    ]
];
