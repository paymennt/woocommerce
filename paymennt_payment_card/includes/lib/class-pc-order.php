<?php

/*Copyright 2022 Paymennt */

/*This file is part of Paymennt Card Payment.
 * Paymennt Card Payment is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * Paymennt Card Payment is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with Paymennt Card Payment. If not, see <https://www.gnu.org/licenses/>.
 */

class Paymennt_Card_Order extends Paymennt_Card_Parent
{

    private $order = array();
    private $orderId;

    public function loadOrder($orderId)
    {
        $this->orderId = $orderId;
        $this->order   = $this->getOrderById($orderId);
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getSessionOrderId()
    {
        return WC()->session->get('order_awaiting_payment');
    }

    public function clearSessionCurrentOrder()
    {
        return WC()->session->__unset('order_awaiting_payment');
    }


    public function getOrderId()
    {
        return $this->order->id;
    }

    public function getOrderById($orderId)
    {
        return wc_get_order($orderId);
    }

    public function getLoadedOrder()
    {
        return $this->order;
    }

    public function getEmail()
    {
        return $this->order->billing_email;
    }

    public function getCustomerName()
    {
        $firstName = $this->order->billing_first_name;
        $lastName  = $this->order->billing_last_name;

        return trim($firstName . ' ' . $lastName);
    }

    public function getCurrencyCode()
    {
        return $this->order->get_currency();
    }

    public function getCurrencyValue()
    {
        return 1;
    }

    public function getTotal()
    {
        return $this->order->get_total();
    }

    public function getTaxAmount(){
        return $this->order->get_total_tax();
    }

    public function getShippingAmount(){
        return $this->order->get_shipping_total();
    }

    public function getSubtotal(){
        return $this->order->get_subtotal();
    }

    public function getDiscountAmount(){
        return $this->order->get_discount_total();
    }
    public function getPaymentMethod()
    {
        return $this->order->payment_method;
    }

    public function getStatusId()
    {
        return $this->order->get_status();
    }

}