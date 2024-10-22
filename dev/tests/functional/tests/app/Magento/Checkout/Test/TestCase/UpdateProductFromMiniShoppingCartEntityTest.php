<?php

namespace Magento\Checkout\Test\TestCase;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Util\Command\Cli\EnvWhitelist;

/**
 * Preconditions:
 * 1. Create product according to dataset.
 * 2. Go to frontend.
 * 3. Add product to cart.
 *
 * Steps:
 * 1. Click on mini shopping cart icon.
 * 2. Click Edit.
 * 3. Fill data from dataset.
 * 4. Click Update.
 * 5. Perform all assertions.
 *
 * @group Mini_Shopping_Cart
 * @ZephyrId MAGETWO-29812
 */
class UpdateProductFromMiniShoppingCartEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'extended_acceptance_test';
    const SEVERITY = 'S0';
    /* end tags */

    /**
     * Catalog product view page.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * DomainWhitelist CLI
     *
     * @var EnvWhitelist
     */
    private $envWhitelist;

    /**
     * Inject data.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogProductView $catalogProductView
     * @param FixtureFactory $fixtureFactory
     * @param EnvWhitelist $envWhitelist
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CatalogProductView $catalogProductView,
        FixtureFactory $fixtureFactory,
        EnvWhitelist $envWhitelist
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->catalogProductView = $catalogProductView;
        $this->fixtureFactory = $fixtureFactory;
        $this->envWhitelist = $envWhitelist;
    }

    /**
     * Update product from mini shopping cart.
     * @param array $originalProduct
     * @param array $checkoutData
     * @param boolean $useMiniCartToEditQty
     * @param array $shippingAddress
     * @param array $shipping
     * @param array $payment
     * @param Customer $customer
     * @return array
     */
    public function test(
        array $originalProduct,
        array $checkoutData,
        $useMiniCartToEditQty = false,
        $shippingAddress = null,
        $shipping = null,
        $payment = null,
        Customer $customer = null
    ) {
        // Preconditions:
        $this->envWhitelist->addHost('example.com');
        if ($customer !== null) {
            $customer->persist();
        }
        $product = $this->createProduct($originalProduct);
        $this->addToCart($product);

        // Steps:
        $productData = $product->getData();
        $productData['checkout_data'] = $checkoutData;
        $newProduct = $this->createProduct([explode('::', $originalProduct[0])[0]], [$productData]);
        $miniShoppingCart = $this->cmsIndex->getCartSidebarBlock();
        $miniShoppingCart->openMiniCart();

        if ($useMiniCartToEditQty) {
            $miniShoppingCart->getCartItem($newProduct)->editQty($newProduct->getCheckoutData());
        } else {
            $miniShoppingCart->getCartItem($newProduct)->clickEditItem();
            $this->catalogProductView->getViewBlock()->addToCart($newProduct);
            $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
        }
        // Prepare data for asserts:
        $cart['data']['items'] = ['products' => [$newProduct]];
        $deletedCart['data']['items'] = ['products' => [$product]];

        return [
            'deletedCart' => $this->fixtureFactory->createByCode('cart', $deletedCart),
            'cart' => $this->fixtureFactory->createByCode('cart', $cart),
            'checkoutData' => [
                'shippingAddress' => $shippingAddress,
                'shipping' => $shipping,
                'payment' => $payment
            ]
        ];
    }

    /**
     * Create product.
     *
     * @param array $product
     * @param array $data [optional]
     * @return FixtureInterface
     */
    protected function createProduct(array $product, array $data = [])
    {
        $createProductsStep = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $product, 'data' => $data]
        );
        return $createProductsStep->run()['products'][0];
    }

    /**
     * Add product to cart.
     *
     * @param FixtureInterface $product
     * @return void
     */
    protected function addToCart(FixtureInterface $product)
    {
        $addToCartStep = $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => [$product]]
        );
        $addToCartStep->run();
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->envWhitelist->removeHost('example.com');
    }
}
