<?php

include __DIR__ . '/../../Store/_files/website.php';
include __DIR__ . '/rules.php';

/** @var \Magento\SalesRule\Model\Rule $rule2 */
$rule2->setWebsiteIds($website->getId())
    ->save();

/** @var \Magento\SalesRule\Model\Rule $rule3 */
$rule3->setWebsiteIds(implode(',', [1, $website->getId()]))
    ->save();
