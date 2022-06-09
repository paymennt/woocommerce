<?php

/* Plugin Name: Paymennt Card Payment
 * Description: Have your customers pay with credit or debit cards via Paymennt
 * Version:     3.0.0
 * Author:      Paymennt
 * Author URI:  https://docs.paymennt.com/docs/payment/ecomm/woocommerce
 */
$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
if (in_array('woocommerce/woocommerce.php', $active_plugins)) {

    add_filter('woocommerce_payment_gateways', 'add_paymennt_card_gateway');

    function add_paymennt_card_gateway($gateways)
    {
        $gateways[] = 'WC_Gateway_Paymennt_Card';

        return $gateways;
    }

    add_action('plugins_loaded', 'init_paymennt_card_gateway');

    function init_paymennt_card_gateway()
    {
        require 'includes/class-woocommerce-paymennt-card.php';
        add_filter('woocommerce_get_sections_checkout', function ($sections) {
            return $sections;
        }, 500);
    }

    add_action('plugins_loaded', 'paymennt_card_load_plugin_textdomain');

    function paymennt_card_load_plugin_textdomain()
    {
        load_plugin_textdomain('woocommerce-other-payment-gateway', FALSE, basename(dirname(__FILE__)) . '/languages/');
    }

    function woocommerce_paymennt_card_actions()
    {
        if (isset($_GET['wc-api']) && !empty($_GET['wc-api'])) {
            WC()->payment_gateways();
            if ($_GET['wc-api'] == 'wc_gateway_paymennt_card_process_response') {
                do_action('woocommerce_wc_gateway_paymennt_card_process_response');
            }
        }
    }

    add_action('init', 'woocommerce_paymennt_card_actions', 500);
}