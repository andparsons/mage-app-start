<?php

declare(strict_types=1);

return [
    'createTransactionRequest' => [
        'merchantAuthentication' =>[
            'name' => 'someusername',
            'transactionKey' => 'somepassword',
        ],
        'transactionRequest' => [
            'transactionType' => 'priorAuthCaptureTransaction',
            'refTransId' => '1234',
            'userFields' => [
                'userField' => [
                    [
                        'name' => 'transactionType',
                        'value' => 'priorAuthCaptureTransaction',
                    ],
                ],
            ],
        ],
    ]
];
