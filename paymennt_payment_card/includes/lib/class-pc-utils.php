<?php

class Paymennt_Card_Utils extends Paymennt_Card_Parent
{

    private $pcConfig;

    public function __construct()
    {
        parent::__construct();
        $this->pcOrder = new Paymennt_Card_Order();
        $this->pcConfig = Paymennt_Card_Config::getInstance();
    }

    public function apiCall($url, $body)
    {
        try {
            $headers = array(
                'Content-Type: application/json',
                'X-PointCheckout-Api-Key:' . $this->pcConfig->getApiKey(),
                'X-PointCheckout-Api-Secret:' . $this->pcConfig->getApiSecret()
            );

            $_BASE_URL = $this->getApiBaseUrl() . $url;

            $ch = curl_init($_BASE_URL);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            if (!is_null($body)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }

            $response = curl_exec($ch);
        } catch (Exception $e) {
            $this->log('Failed to connect call PointChckout API: ' . $e->getMessage());
            throw $e;
        }

        return json_decode($response);
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