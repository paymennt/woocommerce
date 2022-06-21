<?php
 
class Paymennt_Utils extends Paymennt_Card_Parent
{

    private $pmntConfig;

    public function __construct()
    {
        parent::__construct();
        $this->pmntOrder = new Paymennt_Order();
        $this->pmntConfig = Paymennt_Config::getInstance();
    }

    public function log($messages)
    {
        $logger = wc_get_logger();
        $logger->error($messages, 'paymennt_card');
    }

    public function getApiBaseUrl()
    {
        if ($this->pmntConfig->isLiveMode()) {
            return 'https://api.paymennt.com/mer/v2.0/';
        } elseif ($this->pmntConfig->isStagingMode()) {
            return 'https://api.staging.paymennt.com/mer/v2.0/';
        } else {
            return 'https://api.test.paymennt.com/mer/v2.0/';
        }
    }

    public function getAdminUrl()
    {
        if ($this->pmntConfig->isLiveMode()) {
            return 'https://admin.paymennt.com';
        } elseif ($this->pmntConfig->isStagingMode()) {
            return 'https://admin.staging.paymennt.com';
        } else {
            return 'https://admin.test.paymennt.com';
        }
    }
}