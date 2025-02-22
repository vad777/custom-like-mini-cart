<?php
/**
 * Woo Side Mini Cart Manual
 *
 * Этот файл содержит класс `Woo_Side_Mini_Cart_Manual`, который управляет боковой мини-корзиной WooCommerce.
 * Функционал:
 * - Добавление мини-корзины в `wp_footer`
 * - Подключение стилей и скриптов
 * - Обновление мини-корзины через AJAX (добавление, удаление, изменение количества)
 * - Генерация HTML мини-корзины
 * - Обработка AJAX-запросов для изменения количества товаров в корзине
 *
 * @package    WooCommerce
 * @subpackage Woo Side Mini Cart
 * @author     Your Name
 * @version    1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Класс Woo_Side_Mini_Cart_Manual
 *
 * Управляет боковой мини-корзиной WooCommerce.
 */
class Woo_Side_Mini_Cart_Manual {

    /**
     * Конструктор класса
     *
     * Здесь мы подключаем основные хуки для работы мини-корзины.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'wp_footer', [ $this, 'render_side_cart' ] );
        add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'add_to_cart_fragments' ] );
        add_action( 'wp_ajax_update_cart_item_qty', [ $this, 'update_cart_item_qty' ] );
        add_action( 'wp_ajax_nopriv_update_cart_item_qty', [ $this, 'update_cart_item_qty' ] );
    }

    /**
     * Подключает стили и скрипты плагина
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'woo-side-mini-cart-plugin-css',
            plugin_dir_url( __FILE__ ) . 'style.css',
            [],
            '1.3'
        );

        wp_enqueue_script(
            'woo-side-mini-cart-plugin-js',
            plugin_dir_url( __FILE__ ) . 'script.js',
            [ 'jquery' ],
            '1.3',
            true
        );

        // Передаем AJAX URL в JavaScript
        wp_localize_script('woo-side-mini-cart-plugin-js', 'ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    /**
     * Выводит HTML мини-корзины в футере
     */
    public function render_side_cart() {
        ?>
        <div id="woo-side-mini-cart" class="woo-side-mini-cart">
            <div class="woo-side-mini-cart__overlay"></div>
            <div class="woo-side-mini-cart__content">
                <button class="woo-side-mini-cart__close">&times;</button>
                <h2>Ваша корзина</h2>
                <div class="woo-side-mini-cart__items">
                    <?php echo $this->generate_cart_html(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Генерирует HTML-код мини-корзины
     *
     * @return string HTML-код мини-корзины
     */
    public function generate_cart_html() {
        if ( WC()->cart->is_empty() ) {
            return '<p>Ваша корзина пуста</p>';
        }

        ob_start();

        echo '<div class="woo-side-mini-cart__items-wrapper">';

        $items = WC()->cart->get_cart();

        foreach ( $items as $cart_item_key => $cart_item ) {

            $product = $cart_item['data'];
            if ( ! $product instanceof WC_Product ) {
                continue;
            }

            $product_name      = $product->get_name();
            $product_link      = $product->is_visible() ? $product->get_permalink( $cart_item ) : '';
            $product_price     = WC()->cart->get_product_price( $product );
            $product_quantity  = $cart_item['quantity'];
            $product_thumbnail = $product->get_image( 'thumbnail' );
            $remove_url        = wc_get_cart_remove_url( $cart_item_key );

            echo '<div class="woo-side-mini-cart__item">';

            echo '<div class="woo-side-mini-cart__item-thumb">';
            if ( $product_link ) {
                echo '<a href="' . esc_url( $product_link ) . '">' . $product_thumbnail . '</a>';
            } else {
                echo $product_thumbnail;
            }
            echo '</div>';

            echo '<div class="woo-side-mini-cart__item-details">';
            echo '<h4 class="woo-side-mini-cart__item-title">';
            if ( $product_link ) {
                echo '<a href="' . esc_url( $product_link ) . '">' . esc_html( $product_name ) . '</a>';
            } else {
                echo esc_html( $product_name );
            }
            echo '</h4>';

            // ✅ Поле ввода количества товара
            echo '<div class="woo-side-mini-cart__item-qty">';
            echo '<input type="number" min="1" step="1" class="cart-qty-input" data-cart_item_key="' . esc_attr( $cart_item_key ) . '" value="' . esc_attr( $product_quantity ) . '">';
            echo '</div>';

            echo '<p class="woo-side-mini-cart__item-price">' . $product_price . '</p>';

            echo '<a 
                    href="' . esc_url( $remove_url ) . '" 
                    class="woo-side-mini-cart__item-remove remove remove_from_cart_button" 
                    data-cart_item_key="' . esc_attr( $cart_item_key ) . '"
                >Удалить</a>';

            echo '</div>';

            echo '</div>';
        }

        echo '<div class="woo-side-mini-cart__subtotal">';
        echo '<strong>Сумма:</strong> ' . WC()->cart->get_cart_subtotal();
        echo '</div>';

        echo '<div class="woo-side-mini-cart__actions">';
        echo '<a class="button wc-forward" href="' . esc_url( wc_get_cart_url() ) . '">Перейти в корзину</a>';
        echo '</div>';

        echo '</div>';

        return ob_get_clean();
    }


    /**
     * Обновляет мини-корзину при AJAX
     *
     * @return array Обновленный HTML-код мини-корзины
     */
    public function add_to_cart_fragments( $fragments ) {
        ob_start();
        ?>
        <div class="woo-side-mini-cart__items">
            <?php echo $this->generate_cart_html(); ?>
        </div>
        <?php
        $fragments['div.woo-side-mini-cart__items'] = ob_get_clean();

        return $fragments;
    }


    /**
     * Обрабатывает AJAX-запрос на изменение количества товаров в корзине
     */
    public function update_cart_item_qty() {
        if ( ! isset($_POST['cart_item_key']) || ! isset($_POST['new_qty']) ) {
            wp_send_json_error(['message' => 'Ошибка: данные отсутствуют']);
        }

        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        $new_qty = intval($_POST['new_qty']);

        if ($new_qty < 1) {
            WC()->cart->remove_cart_item($cart_item_key);
        } else {
            WC()->cart->set_quantity($cart_item_key, $new_qty);
        }

        WC()->cart->calculate_totals();
        WC()->cart->maybe_set_cart_cookies();

        wp_send_json_success(['cart_html' => $this->generate_cart_html()]);
    }


}
