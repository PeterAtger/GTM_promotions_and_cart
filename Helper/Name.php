<?php

/**
 * @author      Peter Atef <info@scandiweb.com>
 * @package     Scandiweb_GTM
 * @copyright   Copyright (c) 2023 Scandiweb, Ltd (https://scandiweb.com)
 */


namespace Scandiweb\GTM\Helper;

use Magento\Theme\Block\Html\Header\Logo;

/**
 * Class Name
 * @package Scandiweb\GTM\Helper
 */
class Name
{
    /**
     * @var Logo
     */
    protected $logo;

    /**
     * Name constructor.
     * @param Logo $logo
     */
    public function __construct(Logo $logo)
    {
        $this->logo = $logo;
    }

    /**
     * @param string $pageName
     * @param bool $isMain
     * @return string
     */
    public function getEccomPageName($pageName, $isMain = false)
    {
        $pageNames = [
            'cms_index_index' => 'homepage',
            'catalog_category_view' => 'category',
            'catalog_product_view' => 'product',
            'weltpixel_quickview_catalog_product_view' => 'product',
            'checkout_index_index' => 'checkout',
            'customer_account_login' => 'login',
            'checkout_onepage_success' => 'success',
            'catalogsearch_result_index' => 'search_result',
            'checkout_cart_index' => 'cart',
            'customer-account-index' => 'account',
            'cms_page_view' => 'CMS',
            'wishlist_index_index' => 'wishlist'
        ];

        return $this->isPageInList($pageNames, $pageName) ? $pageNames[$pageName] : 'CMS';
    }

    /**
     * @param $pageNames
     * @param $pageName
     * @param $isMain
     * @return bool
     */
    private function isPageInList($pageNames, $pageName): bool
    {
        if (!array_key_exists($pageName, $pageNames)) {
            return false;
        }

        return true;
    }
}
