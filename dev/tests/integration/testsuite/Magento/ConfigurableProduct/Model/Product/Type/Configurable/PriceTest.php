<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

use Magento\Catalog\Model\Product\Type\AbstractType;

/**
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/tax_rule.php
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    /**
     *
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     *
     */
    public function testGetFinalPrice()
    {
        $this->assertPrice(10);
    }

    /**
     * @magentoConfigFixture current_store tax/display/type 1
     */
    public function testGetFinalPriceExcludingTax()
    {
        $this->assertPrice(10);
    }

    /**
     * @magentoConfigFixture current_store tax/display/type 2
     */
    public function testGetFinalPriceIncludingTax()
    {
        //lowest price of configurable variation + 10%
        $this->assertPrice(11);
    }

    /**
     * @magentoConfigFixture current_store tax/display/type 3
     */
    public function testGetFinalPriceIncludingExcludingTax()
    {
        //lowest price of configurable variation + 10%
        $this->assertPrice(11);
    }


    /**
     *
     */
    public function testGetFinalPriceWithSelectedSimpleProduct()
    {
        $product = $this->getProduct(1);
        $product->addCustomOption('simple_product', 20, $this->getProduct(20));
        $this->assertPrice(20, $product);
    }

    /**
     *
     */
    public function testGetFinalPriceWithCustomOption()
    {
        $product = $this->getProduct(1);
        $product->setProductOptions(
            [
                [
                    'id' => 1,
                    'option_id' => 0,
                    'previous_group' => 'text',
                    'title' => 'Test Field',
                    'type' => 'field',
                    'is_require' => 1,
                    'sort_order' => 0,
                    'price' => 100,
                    'price_type' => 'fixed',
                    'sku' => '1-text',
                    'max_characters' => 100,
                ],
            ]
        )->setCanSaveCustomOptions(true)->save();

        $product = $this->getProduct(1);
        $optionId = array_keys($product->getOptions());
        $optionId = reset($optionId);
        $product->addCustomOption(AbstractType::OPTION_PREFIX . $optionId, 'text');
        $product->addCustomOption('option_ids', $optionId);
        $this->assertPrice(110, $product);
    }

    /**
     * Test
     *
     * @param $expectedPrice
     * @param null $product
     * @return void
     */
    protected function assertPrice($expectedPrice, $product = null)
    {
        $product = $product ?: $this->getProduct(1);

        /** @var $model \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price */
        $model = $this->objectManager->create(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price'
        );

        // final price is the lowest price of configurable variations
        $this->assertEquals(round($expectedPrice, 2), round($model->getFinalPrice(1, $product), 2));
    }

    /**
     * @param int $id
     * @return \Magento\Catalog\Model\Product
     */
    private function getProduct($id)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        $product->load($id);
        return $product;
    }
}
