<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Ui\DataProvider\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Modifier for complex products.
 */
class PriceByType implements ModifierInterface
{
    /**
     * @var \Magento\Ui\DataProvider\Modifier\ModifierInterface[]
     */
    private $modifiersByType = [];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $defaultModifier;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Ui\DataProvider\Modifier\ModifierInterface[] $modifiers
     * @param string $defaultModifier [optional]
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        array $modifiers,
        $defaultModifier = 'simple'
    ) {
        $this->request = $request;
        $this->modifiersByType = $modifiers;
        $this->defaultModifier = $defaultModifier;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        foreach ($this->modifiersByType as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }
        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        if (empty($data['items'])) {
            return $data;
        }

        $websiteId = $this->getWebsiteId();

        foreach ($data['items'] as $key => $item) {
            $item['website_id'] = $websiteId;
            if (!empty($item['type_id'])) {
                if (!empty($this->modifiersByType[$item['type_id']])) {
                    $modifier = $this->modifiersByType[$item['type_id']];
                } else {
                    $modifier = $this->modifiersByType[$this->defaultModifier];
                }
                $data['items'][$key] = $modifier->modifyData($item);
            }
        }

        return $data;
    }

    /**
     * Get website id from websites filter.
     *
     * @return int
     */
    private function getWebsiteId()
    {
        $websiteId = 0;
        $params = $this->request->getParams();
        if (isset($params['filters']['websites'])) {
            $websiteId = (int)$params['filters']['websites'];
        }

        return $websiteId;
    }
}
