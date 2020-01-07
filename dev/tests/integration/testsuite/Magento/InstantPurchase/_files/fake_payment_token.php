<?php

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;

/** @var PaymentTokenRepositoryInterface $repository */
$repository = Bootstrap::getObjectManager()->get(PaymentTokenRepositoryInterface::class);
/** @var PaymentTokenInterface $token */
$token = Bootstrap::getObjectManager()->create(PaymentTokenInterface::class);
$token->setCustomerId(1);
$token->setPaymentMethodCode('fake');
$token->setPublicHash('fakePublicHash');
$token->setIsActive(true);
$token->setIsVisible(true);
$token->setCreatedAt(strtotime('-1 day'));
$token->setExpiresAt(strtotime('+1 day'));
$repository->save($token);
