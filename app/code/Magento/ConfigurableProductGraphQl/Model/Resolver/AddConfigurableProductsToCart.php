<?php
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\AddProductsToCart;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * Add configurable products to cart GraphQl resolver
 * {@inheritdoc}
 */
class AddConfigurableProductsToCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var AddProductsToCart
     */
    private $addProductsToCart;

    /**
     * @param GetCartForUser $getCartForUser
     * @param AddProductsToCart $addProductsToCart
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        AddProductsToCart $addProductsToCart
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->addProductsToCart = $addProductsToCart;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['input']['cart_id']) || empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (!isset($args['input']['cart_items']) || empty($args['input']['cart_items'])
            || !is_array($args['input']['cart_items'])
        ) {
            throw new GraphQlInputException(__('Required parameter "cart_items" is missing'));
        }
        $cartItems = $args['input']['cart_items'];

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        $this->addProductsToCart->execute($cart, $cartItems);

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
