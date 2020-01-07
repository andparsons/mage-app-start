<?php

declare(strict_types=1);

return [
    'updateHeldTransactionRequest' => [
        'merchantAuthentication' => [
            'name' => 'someusername',
            'transactionKey' => 'somepassword'
        ],
        'heldTransactionRequest' => [
            'action' => 'approve',
            'refTransId' => '1234',
        ]
    ]
];
