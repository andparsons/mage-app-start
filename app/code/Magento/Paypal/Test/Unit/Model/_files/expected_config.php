<?php
declare(strict_types=1);

return [
    'cart' => [
        'cart',
        'es_MX',
        true,
        'CREDIT',
        'horizontal',
        'small',
        'pillow',
        'installment',
        'blue',
        'my_label',
        'mx',
        [
            'merchantId' => 'merchant',
            'environment' => 'sandbox',
            'locale' => 'es_MX',
            'allowedFunding' => ['ELV'],
            'disallowedFunding' => ['CREDIT'],
            'styles' => [
                'layout' => 'horizontal',
                'size' => 'small',
                'color' => 'blue',
                'shape' => 'pillow',
                'label' => 'installment',
                'installmentperiod' => 0
            ],
            'isVisibleOnProductPage' => 0
        ]
    ],
    'checkout' => [
        'cart',
        'en_BR',
        true,
        null,
        'horizontal',
        'small',
        'pillow',
        'installment',
        'blue',
        'my_label',
        'br',
        [
            'merchantId' => 'merchant',
            'environment' => 'sandbox',
            'locale' => 'en_BR',
            'allowedFunding' => ['CREDIT', 'ELV'],
            'disallowedFunding' => [],
            'styles' => [
                'layout' => 'horizontal',
                'size' => 'small',
                'color' => 'blue',
                'shape' => 'pillow',
                'label' => 'installment',
                'installmentperiod' => 0
            ],
            'isVisibleOnProductPage' => 0
        ]
    ],
    'mini_cart' => [
        'cart',
        'en',
        false,
        null,
        'horizontal',
        'small',
        'pillow',
        'installment',
        'blue',
        'my_label',
        'br',
        [
            'merchantId' => 'merchant',
            'environment' => 'sandbox',
            'locale' => 'en',
            'allowedFunding' => ['CREDIT', 'ELV'],
            'disallowedFunding' => [],
            'styles' => [
                'layout' => 'vertical',
                'size' => 'responsive',
                'color' => 'gold',
                'shape' => 'rect',
                'label' => 'paypal'
            ],
            'isVisibleOnProductPage' => 0
        ]
    ],
    'mini_cart' => [
        'cart',
        'en',
        false,
        null,
        'horizontal',
        'small',
        'pillow',
        'installment',
        'blue',
        'my_label',
        'br',
        [
            'merchantId' => 'merchant',
            'environment' => 'sandbox',
            'locale' => 'en',
            'allowedFunding' => ['CREDIT', 'ELV'],
            'disallowedFunding' => [],
            'styles' => [
                'layout' => 'vertical',
                'size' => 'responsive',
                'color' => 'gold',
                'shape' => 'rect',
                'label' => 'paypal'
            ],
            'isVisibleOnProductPage' => 0
        ]
    ],
    'product' => [
        'cart',
        'en',
        false,
        'CREDIT',
        'horizontal',
        'small',
        'pillow',
        'installment',
        'blue',
        'my_label',
        'br',
        [
            'merchantId' => 'merchant',
            'environment' => 'sandbox',
            'locale' => 'en',
            'allowedFunding' => ['ELV'],
            'disallowedFunding' => ['CREDIT'],
            'styles' => [
                'layout' => 'vertical',
                'size' => 'responsive',
                'color' => 'gold',
                'shape' => 'rect',
                'label' => 'paypal',
            ],
            'isVisibleOnProductPage' => 0
        ]
    ]
];
