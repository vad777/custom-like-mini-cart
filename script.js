jQuery(document).ready(function($) {

    function openSideCart() {
        $('#woo-side-mini-cart').addClass('woo-side-mini-cart--active');
    }

    function closeSideCart() {
        $('#woo-side-mini-cart').removeClass('woo-side-mini-cart--active');
    }

    $('.woo-side-mini-cart__close, .woo-side-mini-cart__overlay').on('click', closeSideCart);

    // ✅ Принудительное обновление мини-корзины после добавления товара
    $(document.body).on('added_to_cart', function() {
        $.get(ajax_object.ajax_url, { action: 'woocommerce_get_refreshed_fragments' }, function(data) {
            if (data && data.fragments) {
                $('.woo-side-mini-cart__items').html(data.fragments['div.woo-side-mini-cart__items']);
                openSideCart();
            }
        });
    });

    // ✅ Принудительное обновление после удаления товара
    $(document.body).on('removed_from_cart', function() {
        $(document.body).trigger('wc_fragment_refresh');
    });

    // ✅ Обновление количества товара по AJAX
    $(document).on('input', '.cart-qty-input', function() {
        var cartItemKey = $(this).data('cart_item_key');
        var newQty = parseInt($(this).val());

        if (isNaN(newQty) || newQty < 1) {
            newQty = 1;
            $(this).val(1);
        }

        $.post(ajax_object.ajax_url, {
            action: 'update_cart_item_qty',
            cart_item_key: cartItemKey,
            new_qty: newQty
        }, function(response) {
            if (response.success) {
                $('.woo-side-mini-cart__items').html(response.data.cart_html);
                openSideCart();
            }
        });
    });

    // ✅ Убедимся, что корзина всегда обновляется после загрузки фрагментов WooCommerce
    $(document.body).on('wc_fragments_refreshed', function(event, fragments) {
        if (fragments && fragments['div.woo-side-mini-cart__items']) {
            $('.woo-side-mini-cart__items').html(fragments['div.woo-side-mini-cart__items']);
        }
        openSideCart();
    });

});
