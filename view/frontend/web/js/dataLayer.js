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
                var count = this.getPrevCart()?.summary_count;
                var previousCart = this.getPrevCart();
                var that = this;

                cart.subscribe(function () {
                    if (
                        cart().summary_count > count
                        || (cart().summary_count && !count)
                    ) {
                        var newCart = cart();

                        // Get Newly added Items
                        var addedItems = that.getNewItems(previousCart, newCart);

                        // Get Cart datalyer Data
                        var layerData = that.getCartData('add_to_cart', addedItems);

                        window.dataLayer.push(layerData);
                    }

                    else if (
                        cart().summary_count < count
                    ) {
                        var newCart = cart();

                        // Get Removed Items
                        var addedItems = that.getNewItems(newCart, previousCart);

                        // Get Cart datalyer Data
                        var layerData = that.getCartData('remove_from_cart', addedItems);

                        window.dataLayer.push(layerData);
                    }

                    // Save cart so state is maintained during navigation
                    that.savePrevCart(cart())

                    // Update previous cart for next call
                    previousCart = that.cloneCart(cart());
                    count = cart().summary_count;
                });
            },


            getNewItems: function (oldCart, newCart) {
                var newItems = [];

                if (!newCart.items) {
                    return;
                }

                if (!oldCart.items) {
                    return newCart.items;
                }

                newCart.items.forEach(function (elem) {
                    var similar = oldCart.items.find(function (item) {
                        return item.item_id == elem.item_id;
                    });
                    if (similar) {
                        if (elem.qty > similar.qty) {
                            newItems.push({ ...elem, qty: elem.qty - similar.qty });
                        }
                    } else {
                        newItems.push(elem);
                    }
                });

                return newItems;
            },


            getCartData: function (event, newItems) {
                var eventData = {
                    event,
                    ecommerce: {
                        currency: 'CAD',
                        items: []
                    }
                }

                newItems.forEach((elem) => {
                    eventData.ecommerce.items.push({
                        item_id: elem.product_sku,
                        item_name: elem.product_name,
                        quantity: elem.qty,
                        price: elem.product_price_value,
                        category: elem.product_category,
                    })
                })

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
