<?php

/**
 * @author      Peter Atef <info@scandiweb.com>
 * @package     Scandiweb_GTM
 * @copyright   Copyright (c) 2023 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Scandiweb\GTM\CustomerData;

use Magento\Checkout\CustomerData\DefaultItem as BaseDefaultItem;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Helper\Image;
use Magento\Msrp\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Checkout\Helper\Data as CheckoutData;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Framework\Escaper;
use Magento\Catalog\Model\CategoryFactory;

class DefaultItem extends BaseDefaultItem
{
    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var Data
     */
    protected $msrpHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ConfigurationPool
     */
    protected $configurationPool;

    /**
     * @var CheckoutData
     */
    protected $checkoutHelper;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var ItemResolverInterface
     */
    protected $itemResolver;


    /**
     * @param Image $imageHelper
     * @param Data $msrpHelper
     * @param CategoryFactory $categoryFactory
     * @param UrlInterface $urlBuilder
     * @param ConfigurationPool $configurationPool
     * @param CheckoutData $checkoutHelper
     * @param Escaper|null $escaper
     * @param ItemResolverInterface|null $itemResolver
     * @param GTMData $gtmData
     * @codeCoverageIgnore
     */
    public function __construct(
        Image $imageHelper,
        Data $msrpHelper,
        CategoryFactory $categoryFactory,
        UrlInterface $urlBuilder,
        ConfigurationPool $configurationPool,
        CheckoutData $checkoutHelper,
        Escaper $escaper = null,
        ItemResolverInterface $itemResolver = null,
    ) {
        $this->configurationPool = $configurationPool;
        $this->imageHelper = $imageHelper;
        $this->msrpHelper = $msrpHelper;
        $this->_categoryFactory = $categoryFactory;
        $this->urlBuilder = $urlBuilder;
        $this->checkoutHelper = $checkoutHelper;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(ItemResolverInterface::class);
    }

    /**
     * Overridden to add category and brand
     * 
     * @inheritdoc
     */
    protected function doGetItemData()
    {
        $imageHelper = $this->imageHelper->init($this->getProductForThumbnail(), 'mini_cart_product_thumbnail');
        $productName = $this->escaper->escapeHtml($this->item->getProduct()->getName());

        return [
            'options' => $this->getOptionList(),
            'qty' => $this->item->getQty() * 1,
            'item_id' => $this->item->getId(),
            'configure_url' => $this->getConfigureUrl(),
            'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
            'product_id' => $this->item->getProduct()->getId(),
            'product_category' => $this->getCategoryNameByProduct($this->item->getProduct()),
            'product_name' => $productName,
            'product_sku' => $this->item->getProduct()->getSku(),
            'product_url' => $this->getProductUrl(),
            'product_has_url' => $this->hasProductUrl(),
            'product_price' => $this->checkoutHelper->formatPrice($this->item->getCalculationPrice()),
            'product_price_value' => $this->item->getCalculationPrice(),
            'product_image' => [
                'src' => $imageHelper->getUrl(),
                'alt' => $imageHelper->getLabel(),
                'width' => $imageHelper->getWidth(),
                'height' => $imageHelper->getHeight(),
            ],
            'canApplyMsrp' => $this->msrpHelper->isShowBeforeOrderConfirm($this->item->getProduct())
                && $this->msrpHelper->isMinimalPriceLessMsrp($this->item->getProduct()),
            'message' => $this->item->getMessage(),
        ];
    }

    /**
     * Returns product for thumbnail.
     *
     * @return \Magento\Catalog\Model\Product
     * @codeCoverageIgnore
     */
    protected function getProductForThumbnail()
    {
        return $this->itemResolver->getFinalProduct($this->item);
    }

    /**
     * @param Product $product
     *
     * @return string
     */
    public function getCategoryNameByProduct($product)
    {
        $categoryIds  = $product->getCategoryIds();
        $categoryName = '';
        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $category     = $this->_categoryFactory->create()->load($categoryId);
                $categoryName .= '/' . $category->getName();
            }
        }

        return trim($categoryName, '/');
    }
}
