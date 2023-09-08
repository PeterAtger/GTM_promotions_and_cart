<?php

/**
 * @author      Peter Atef <info@scandiweb.com>
 * @package     Scandiweb_GTM
 * @copyright   Copyright (c) 2023 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Scandiweb\GTM\Block;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Scandiweb\GTM\Helper\Name;

/**
 * Class DataLayer
 * @package Scandiweb\Gtm\Block
 */
class GeneralEvent extends Template
{
    const GENERAL_EVENT_NAME = 'general';

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Name
     */
    protected $nameHelper;

    /**
     * @var LocaleResolver
     */
    protected $localeResolver;

    /**
     * DataLayer constructor.
     * @param Context $context
     * @param Http $request
     * @param Name $nameHelper
     * @param LocaleResolver $localeResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        Http $request,
        Name $nameHelper,
        LocaleResolver $localeResolver,
        array $data = []
    ) {
        $this->request = $request;
        $this->nameHelper = $nameHelper;
        $this->context = $context;
        $this->localeResolver = $localeResolver;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getPageName()
    {
        $frontAction = $this->request->getFullActionName();

        return $this->nameHelper->getEccomPageName($frontAction);
    }

    /**
     * Generate general event push (layer)
     * @return array
     */
    public function collectLayer()
    {
        $layer['pageType'] = strtoupper($this->getPageName());
        try {
            $layer['store_view'] = $this->context->getStoreManager()->getStore()->getCode();
        } catch (NoSuchEntityException $e) {
        }
        $layer['language'] = strtoupper($this->localeResolver->getLocale());
        $layer['event'] = self::GENERAL_EVENT_NAME;

        return $layer;
    }
}
