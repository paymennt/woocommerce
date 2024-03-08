<?php 

class Paymennt_Config extends Paymennt_Card_Parent
{

    private static $instance;
    private $api_secret;
    private $api_key;
    private $Mode;
    private $orderPlacement;
    private $allowSpecific;
    private $specific_countries;
    private $allowUserSpecific;
    private $specific_uesr_roles;
    private $new_order_status;
    private $payment_type;
    private $public_key;
    public $description;
    public $title;
    public $language;
    public $command;



    public function __construct()
    {
        parent::__construct();
        $this->language                              = $this->_getShoppingCartConfig('language');
        $this->enabled                               = $this->_getShoppingCartConfig('enabled');
        $this->api_key                               = $this->_getShoppingCartConfig('api_key');
        $this->command                               = $this->_getShoppingCartConfig('command');
        $this->api_secret                            = $this->_getShoppingCartConfig('api_secret');
        $this->Mode                                  = $this->_getShoppingCartConfig('mode');
        $this->orderPlacement                        = $this->_getShoppingCartConfig('order_placement');
        $this->allowSpecific                         = $this->_getShoppingCartConfig('allow_specific');
        $this->specific_countries                    = $this->_getShoppingCartConfig('specific_countries');
        $this->allowUserSpecific                     = $this->_getShoppingCartConfig('allow_user_specific');
        $this->specific_uesr_roles                   = $this->_getShoppingCartConfig('specific_user_roles');
        $this->new_order_status                      = $this->_getShoppingCartConfig('new_order_status');
        $this->description                           = $this->_getShoppingCartConfig('description');
        $this->title                                 = $this->_getShoppingCartConfig('title');
        $this->payment_type                          = $this->_getShoppingCartConfig('payment_type');
        $this->public_key                            = $this->_getShoppingCartConfig('public_key');
    }

    /**
     * @return Config
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Paymennt_Config();
        }
        return self::$instance;
    }

    private function _getShoppingCartConfig($key)
    {
        return $this->get_option($key);
    }

    public function getLanguage()
    {
        return 'en';
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function getMode()
    {
        return $this->Mode;
    }

    public function getSuccessOrderStatusId()
    {
        return $this->successOrderStatusId;
    }

    public function isActive()
    {
        if ($this->active) {
            return true;
        }
        return false;
    }

    public function isSpecificCountries()
    {
        return $this->allowSpecific == 1 ? true : false;
    }

    public function getSpecificCountries()
    {
        return $this->specific_countries;
    }

    public function isSpecificUserRoles()
    {
        return $this->allowUserSpecific == 1 ? true : false;
    }

    public function getSpecificUserRoles()
    {
        return $this->specific_uesr_roles;
    }


    public function getNewOrderStatus()
    {
        return $this->new_order_status;
    }



    public function getOrderPlacement()
    {
        return $this->orderPlacement;
    }

    public function orderPlacementIsAll()
    {
        if (empty($this->orderPlacement) || $this->orderPlacement == 'all') {
            return true;
        }
        return false;
    }

    public function orderPlacementIsOnSuccess()
    {
        if ($this->orderPlacement == 'success') {
            return true;
        }
        return false;
    }

    public function isEnabled()
    {
        return $this->enabled === 'yes';
    }

    public function getApiKey()
    {
        return $this->api_key;
    }

    public function getApiSecret()
    {
        return $this->api_secret;
    }

    public function isLiveMode()
    {
        return $this->Mode == 1 ? true : false;
    }

    public function isStagingMode()
    {
        return $this->Mode == 2 ? true : false;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getTitle() {
        if ( empty($this->title)) {
            return "Card";
        }
        return $this->title;
    }

    public function getPublicKey()
    {
        return $this->public_key;
    }

    public function isFramePayment()
    {
        return $this->payment_type == 1 ? true : false;
    }
}