define(["ko",
    "uiComponent",
    "Magento_Customer/js/customer-data",
    "uiRegistry",
    "jquery",
    'prototype'
],
    function (
        ko,
        Component,
        customerData,
        uiRegistry,
        $
    ) {
        "use strict";

        return Component.extend({
            initialize: function (config) {
                this._super();

                window.top.dataLayer = window.top.dataLayer || [];
                this.initializeCartWatcher();
                this.watchPromotions();
            },

            watchPromotions: function () {
                $('[data-promoId]').each(function () {
                    var id = $(this).attr('data-promoId');
                    var name = $(this).attr('data-promoname');
                    var creativeName = $(this).attr('data-promocreative');

                    if (id) {
                        var productData = $(this).children().find('strong:first').parent().contents();

                        var eventData = {
                            event: 'view_promotion',
                            ecommerce: {
                                promotion_id: id,
                                promotion_name: name,
                                creative_name: creativeName,
                                items: []
                            }
                        }

                        if (productData.length === 2) {
                            eventData.ecommerce.items.push({
                                item_id: productData[1].textContent,
                                item_name: productData[0].textContent
                            })
                        } else {
                            eventData.ecommerce.items.push({
                                item_id: id,
                                item_name: name
                            })
                        }

                        window.dataLayer.push(eventData)
                    }

                    $(this).click(
                        function () {
                            window.dataLayer.push({
                                ...eventData,
                                event: 'select_promotion'
                            })
                        }
                    )
                });
            },

            initializeCartWatcher: function () {
                var cart = customerData.get("cart");
                var previousCart = this.getPrevCart();
                var that = this;

                cart.subscribe(function () {
                    var newCart = cart();
                    var addedItems = [];
                    var removedItems = [];

                    if (newCart?.items && previousCart?.items) {
                        newCart.items.forEach(newItem => {
                            const matchedItem = previousCart.items.find(prevItem => prevItem.product_sku === newItem.product_sku);
                            if (matchedItem) {
                                const qtyDiff = newItem.qty - matchedItem.qty;
                                if (qtyDiff !== 0) {
                                    const updatedItem = { ...newItem };
                                    if (qtyDiff < 0) {
                                        updatedItem.qty = -qtyDiff;
                                        removedItems.push(updatedItem);
                                    } else if (qtyDiff > 0) {
                                        updatedItem.qty = qtyDiff;
                                        addedItems.push(updatedItem);
                                    }
                                }
                            } else {
                                addedItems.push(newItem);
                            }
                        });

                        previousCart.items.forEach(prevItem => {
                            const matchedItem = newCart.items.find(newItem => newItem.product_sku === prevItem.product_sku);
                            if (!matchedItem) {
                                const removedItem = { ...prevItem };
                                removedItem.qty = prevItem.qty;
                                removedItems.push(removedItem);
                            }
                        });
                    }

                    if (removedItems.length > 0 && !(window.location.pathname === '/checkout/' && window.location.hash === '#payment') && window.location.pathname !== '/checkout/onepage/success/' ) {
                        const layerData = that.getCartData('remove_from_cart', removedItems);
                        window.dataLayer.push(layerData);
                    }
                    if (addedItems.length > 0) {
                        const layerData = that.getCartData('add_to_cart', addedItems);
                        window.dataLayer.push(layerData);
                    }

                    // Save cart so state is maintained during navigation
                    that.savePrevCart(cart())

                    // Update previous cart for next call
                    previousCart = that.cloneCart(cart());
                });
            },

            getCartData: function (event, newItems) {
                var currency = '';
                var value = 0;
                var eventData = {
                    event,
                    ecommerce: {
                        items: []
                    }
                }

                newItems.forEach((elem) => {
                    value += elem.product_price_value * elem.qty;
                    currency = elem.currency;
                    console.log(elem);

                    eventData.ecommerce.items.push({
                        item_id: elem.product_sku,
                        item_name: elem.product_name,
                        quantity: elem.qty,
                        price: elem.product_price_value,
                        item_brand: elem.product_brand,
                        item_type: elem.item_type,
                        currency: elem.currency,
                        ...elem.product_category
                    });
                });
                eventData.ecommerce.value = value.toFixed(2);
                eventData.ecommerce.currency = currency;

                return eventData;
            },

            getPromotions: function () {
                $('[class="pagebuilder-column"]').has('figure').filter(function () {
                    return $(this).parent().parent().hasClass('pagebuilder-mobile-hidden') && !$(this).parents().is('footer');;
                });
            },

            cloneCart: function (cart) {
                return JSON.parse(JSON.stringify(cart));
            },

            savePrevCart: function (cart) {
                localStorage.setItem('gtm_cart', JSON.stringify(cart))
            },

            getPrevCart: function () {
                return JSON.parse(localStorage.getItem('gtm_cart')) ?? {};
            }


        })
    });
