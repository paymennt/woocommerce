<?php
 
class Paymennt_Utils extends Paymennt_Card_Parent
{

    private $pmntConfig;
    private $pmntOrder;

    public function __construct()
    {
        parent::__construct();
        $this->pmntOrder = new Paymennt_Order();
        $this->pmntConfig = Paymennt_Config::getInstance();
    }

    public function log($messages)
    {
        $logger = wc_get_logger();
        $messageStr = is_string($messages) ? $messages : var_export($messages, true);
        $logger->error($messageStr, ['source' => 'paymennt']);
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

    function extractOrderId($orderReference) {
        // Check if the orderReference string contains any hyphens
        if (strpos($orderReference, '-') === false) {
            return $orderReference; // Return the orderReference string as is if no hyphens are found
        }

        // Split the orderReference string into an array using "-" as the delimiter
        $parts = explode("-", $orderReference);

        // Check if the last part consists of 12 digits
        $lastPart = end($parts); // Get the last part of the parts array
        if (!is_numeric($lastPart) || strlen($lastPart) != 12) {
            return $orderReference; // Return the orderReference string as is if the last part does not consist of 12 digits
        }

        // Remove the last element from the array
        $partsWithoutLast = array_slice($parts, 0, -1);

        // Combine the array back into a string using "-" as the separator
        $result = implode("-", $partsWithoutLast);

        return $result;
    }
}