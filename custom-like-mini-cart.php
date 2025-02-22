<?php
/**
 * Plugin Name: Woo Side Mini Cart Plugin
 * Description: Боковая мини-корзина с AJAX-добавлением и AJAX-удалением товаров без редиректа.
 * Version:     1.2
 * Author:      Your Name
 * Text Domain: woo-side-mini-cart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/woo-side-mini-cart-manual.php';

new Woo_Side_Mini_Cart_Manual();