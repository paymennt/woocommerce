<?php

/*Copyright 2022 Paymennt */

/*This file is part of Paymennt Card Payment.
 * Paymennt Card Payment is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * Paymennt Card Payment is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with Paymennt Card Payment. If not, see <https://www.gnu.org/licenses/>.
 */

class Paymennt_Card_Utils extends Paymennt_Card_Parent
{

    private $pcConfig;

    public function __construct()
    {
        parent::__construct();
        $this->pcOrder = new Paymennt_Card_Order();
        $this->pcConfig = Paymennt_Card_Config::getInstance();
    }

    public function log($messages)
    {
        $logger = wc_get_logger();
        $logger->error($messages, 'paymennt_card');
    }

    public function getApiBaseUrl()
    {
        if ($this->pcConfig->isLiveMode()) {
            return 'https://api.paymennt.com/mer/v2.0/';
        } elseif ($this->pcConfig->isStagingMode()) {
            return 'https://api.staging.paymennt.com/mer/v2.0/';
        } else {
            return 'https://api.test.paymennt.com/mer/v2.0/';
        }
    }

    public function getAdminUrl()
    {
        if ($this->pcConfig->isLiveMode()) {
            return 'https://admin.paymennt.com';
        } elseif ($this->pcConfig->isStagingMode()) {
            return 'https://admin.staging.paymennt.com';
        } else {
            return 'https://admin.test.paymennt.com';
        }
    }
}