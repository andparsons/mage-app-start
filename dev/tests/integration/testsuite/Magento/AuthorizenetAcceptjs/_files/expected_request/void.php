<?php

declare(strict_types=1);

return [
    'createTransactionRequest' => [
        'merchantAuthentication' => [
            'name' => 'someusername',
            'transactionKey' => 'somepassword',
        ],
        'transactionRequest' =>[
            'transactionType' => 'voidTransaction',
            'refTransId' => '1234',
        ],
    ]
];
