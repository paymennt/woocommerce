<?php

/* Plugin Name: Paymennt
 * Description: Have your customers pay with credit or debit cards via Paymennt
 * Version:     3.0.2
 * Author:      Paymennt
 * Author URI:  https://www.paymennt.com
 */
 


$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
if (in_array('woocommerce/woocommerce.php', $active_plugins)) {

    add_filter('woocommerce_payment_gateways', 'add_paymennt_card');

    function add_paymennt_card($gateways)
    {
        $gateways[] = 'WC_Card_Paymennt';

        return $gateways;
    }

    add_action('plugins_loaded', 'init_paymennt_card');

    function init_paymennt_card()
    {
        require 'includes/class-woocommerce-paymennt.php';
        add_filter('woocommerce_get_sections_checkout', function ($sections) {
            return $sections;
        }, 500);
    }

    add_action('plugins_loaded', 'paymennt_load_plugin_textdomain');

    function paymennt_load_plugin_textdomain()
    {
        load_plugin_textdomain('woocommerce-other-payment-gateway', FALSE, basename(dirname(__FILE__)) . '/languages/');
    }

    function woocommerce_paymennt_actions()
    {
        if (isset($_GET['wc-api']) && !empty($_GET['wc-api'])) {
            WC()->payment_gateways();
            if ($_GET['wc-api'] == 'wc_card_paymennt_process_response') {
                do_action('woocommerce_wc_card_paymennt_process_response');
            }
        }
    }

    add_action('init', 'woocommerce_paymennt_actions', 500);
}