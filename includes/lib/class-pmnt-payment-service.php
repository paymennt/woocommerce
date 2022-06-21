<?php
 
define('PMNT_EXT_VERSION', 'WooCommerce-Paymennt-3.0.2');
require_once __DIR__ . '/../sdk/vendor/autoload.php';

class Paymennt_Gateway_Payment extends Paymennt_Gateway_Parent
{

    private static $instance;
    private $pmntOrder;
    private $pmntConfig;
    private $pmntUtils;

    public function __construct()
    {
        parent::__construct();
        $this->pmntOrder = new Paymennt_Order();
        $this->pmntConfig = Paymennt_Config::getInstance();
        $this->pmntUtils = new Paymennt_Utils();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Paymennt_Gateway_Payment();
        }
        return self::$instance;
    }


    public function getCheckoutRequestParams()
    {
        $request = new \Paymennt\checkout\WebCheckoutRequest();

        $orderId = $this->pmntOrder->getSessionOrderId();
        $order = new WC_order($orderId);
        $this->pmntOrder->loadOrder($orderId);
        $order->update_status($this->pmntConfig->getNewOrderStatus());

        // ORDER INFO
        $request->requestId  =$orderId;
        $request->orderId =  $orderId;
        
        $request->allowedPaymentMethods = ['CARD'];
        $request->returnUrl= get_site_url() . '?wc-api=wc_gateway_paymennt_process_response';

        // CURRENCY AND AMOUNT
        $request->currency = $this->pmntOrder->getCurrencyCode(); // 3 letter ISO currency code. eg, AED
        $request->amount = $this->pmntOrder->getTotal(); // transaction amount
        
        // TOTALS
        // order total = subtotal + tax + shipping + handling - discount
        $request->totals = new \Paymennt\model\Totals(); // optional
        $request->totals->subtotal = $this->pmntOrder->getSubtotal(); // item subtotal exclusive of VAT/Tax in order currency
        $request->totals->tax =  $this->pmntOrder->getTaxAmount(); // VAT/Tax for this purchase in order currency
        $request->totals->shipping = $this->pmntOrder->getShippingAmount(); // shipping cost in order currency
        $request->totals->handling = "0"; // handling fees in order currency
        $request->totals->discount = $this->pmntOrder->getDiscountAmount(); // discount applied ()
        if(!$request->totals->subtotal ||  $request->totals->subtotal <= 0) {
            $request->totals->subtotal =  $this->pmntOrder->getTotal();
        }

        try{
            $request->extVersion = PMNT_EXT_VERSION;
            $request->ecommerce = 'WordPress ' . $this->get_wp_version() . ', WooCommerce ' . $this->wpbo_get_woo_version_number();
        } catch (\Throwable $e) {
            // NOTHING TO DO 
        }
        
        // CUSTOMER
        $request->customer = new \Paymennt\model\Customer(); // required
        $request->customer->firstName =  $order->get_billing_first_name(); // customer first name
        $request->customer->lastName =  $order->get_billing_last_name();// customer last name
        $request->customer->email =  $order->get_billing_email(); // customer email address
        $request->customer->phone =  $order->get_billing_phone(); // customer email address
        
        $request->billingAddress = new \Paymennt\model\Address(); // required
        $request->billingAddress->name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();// name of person at billing address
        $request->billingAddress->address1 = $order->get_billing_address_1(); // billing address 1
        $request->billingAddress->address2 = $order->get_billing_address_2(); // billing address 2
        $request->billingAddress->city = $order->get_billing_city(); // city
        $request->billingAddress->state = $order->get_billing_state(); // state (if applicable)
        $request->billingAddress->zip = $order->get_billing_postcode(); // zip/postal code
        $request->billingAddress->country = $order->get_billing_country(); // 3-letter country code
        
        if(!empty($order->get_shipping_country()) && !empty($order->get_shipping_first_name())) {
            $request->deliveryAddress = new \Paymennt\model\Address(); // required if shipping is required
            $request->deliveryAddress->name = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(); // name of person at billing address
            $request->deliveryAddress->address1 =  $order->get_shipping_address_1(); // billing address 1
            $request->deliveryAddress->address2 =  $order->get_shipping_address_2(); // billing address 2
            $request->deliveryAddress->city = $order->get_shipping_city(); // city
            $request->deliveryAddress->state = $order->get_shipping_state(); // state (if applicable)
            $request->deliveryAddress->zip =  $order->get_shipping_postcode(); // zip/postal code
            $request->deliveryAddress->country = $order->get_shipping_country();// 3-letter country code
        }
        $cartItems = $order->get_items();
        $items = array();
        $i = 0;
        foreach ($cartItems as $item_id => $item_data) {
            $product = $item_data->get_product();
            $item =  new \Paymennt\model\Item(); // optional
            $item->name = $product->get_name(); 
            $item->sku = $product->get_sku(); 
            $item->unitprice = $item_data->get_total(); 
            $item->quantity = $item_data->get_quantity(); 
            $item->linetotal = $item_data->get_total(); 
            
            //in case of bundles the bundle group item total is set to zero here to prevent conflict in totals
            if ($product->get_type() == 'bundle') {
                $item->total = 0;
            }
            $items[$i++] = $item;
        }
        $request->items = array_values($items);

        return $request;
    }


    public function getPaymentRequestParams($token)
    {
        $checkoutDeatils = $this->getCheckoutRequestParams();
        $request = new \Paymennt\payment\CreatePaymentRequest("TOKEN",$token,"", $checkoutDeatils);

        return $request;
    }

    /**
     * submit checkout details to paymennt
     */
    public function postOrderToPaymennt()
    {
        if (!$this->pmntConfig->isEnabled()) {
            return null;
        }
        $checkoutRequestParams = $this->getCheckoutRequestParams();
        $response = $this->postCheckout($checkoutRequestParams);

        $this->pmntOrder->clearSessionCurrentOrder();
        return $response;
    }

    public function postCheckout($checkoutRequestParams)
    {
        $client = new \Paymennt\PaymenntClient(
            $this->pmntConfig->getApiKey(), //
            $this->pmntConfig->getApiSecret() //
          );
        $client->useTestEnvironment(!$this->pmntConfig->isLiveMode());

        return $client->createWebCheckout($checkoutRequestParams);
    }

    public function postTokenOrderToPaymennt($token)
    {
        if (!$this->pmntConfig->isEnabled()) {
            return null;
        }
        $paymentRequestParams = $this->getPaymentRequestParams($token);
        $response = $this->postTokenPayment($paymentRequestParams);

        $this->pmntOrder->clearSessionCurrentOrder();
        return $response;
    }

    public function postTokenPayment($paymentRequestParams)
    {
        $client = new \Paymennt\PaymenntClient(
            $this->pmntConfig->getApiKey(), //
            $this->pmntConfig->getApiSecret() //
          );
        $client->useTestEnvironment(!$this->pmntConfig->isLiveMode());

        return $client->createPayment($paymentRequestParams);
    }


    public function getCheckout()
    {
        WC()->session->set('pointCheckoutCurrentOrderId', $_REQUEST['reference']);
        
        $client = new \Paymennt\PaymenntClient(
            $this->pmntConfig->getApiKey(), //
            $this->pmntConfig->getApiSecret() //
          );
        $client->useTestEnvironment(!$this->pmntConfig->isLiveMode());
        
        $request = new \Paymennt\checkout\GetCheckoutRequest();
        $request->checkoutId = $_REQUEST['checkout']; // checkoutId of checkout to be fetched
        $checkout = $client->getCheckoutRequest($request);

        return $checkout;
    }

    public function captureAuthorizedPayment($paymentId)
    {
        $order = new WC_Order($_REQUEST['reference']);
        
        $client = new \Paymennt\PaymenntClient(
            $this->pmntConfig->getApiKey(), //
            $this->pmntConfig->getApiSecret() //
          );
        $client->useTestEnvironment(!$this->pmntConfig->isLiveMode());
        
        $request = new \Paymennt\payment\CaptureAuthPaymentRequest();
        $request->paymentId = $paymentId; // paymentId of authorized payment to be captured
        $payment = $client->captureAuthorizedPayment($request);

        return $payment;
    }

    public function checkPaymentStatus()
    {
       
        $order = new WC_Order($_REQUEST['reference']);

        if (!empty($order)) {
            try {
                $result = $this->getCheckout();

                if ( $result->status == 'PAID') {

                    $note = $this->getOrderHistoryMessage($result->id, $result->cash, $result->status, $result->currency);
                    // Add the note
                    $order->add_order_note($note);
        
                    // Save the data
                    $order->save();
                    return array(
                        'success' => true,
                        'transactionId' => $result->requestId
                    );
                }
                else {
                    $note = __($this->getOrderHistoryMessage($result->id, 0, $result->status, $result->currency));
                    // Add the note

                    $order->update_status('cancelled', $note);
                    
                    // Save the data
                    $order->save();
                    return array(
                        'success' => false,
                        'transactionId' => $result->requestId
                    );
                }
            }
            catch(Exception $e) {
                $errorMsg = isset($e) ? $e : 'connecting to Paymennt failed';
                $note = __('[ERROR] order canceled  :' . $errorMsg);

                $note = __($this->getOrderHistoryMessage($result->id, 0, $result->status, $result->currency));
                    // Add the note

                $order->update_status('cancelled', $note);
               
                // Add the note
                $order->add_order_note($note);
                // Save the data
                $order->save();
                $this->pmntUtils->log('ERROR ' . $errorMsg);
                return array(
                    'success' => false,
                    'transactionId' => ''
                );
            }
        }
    }


    public function getOrderHistoryMessage($checkout, $codAmount, $orderStatus, $currency)
    {
        switch ($orderStatus) {
            case 'PAID':
                $color = 'style="color:green;"';
                break;
            case 'PENDING':
                $color = 'style="color:blue;"';
                break;
            default:
                $color = 'style="color:red;"';
        }
        $message = 'Paymennt Status: <b ' . $color . '>' . $orderStatus . '</b><br/>Paymennt Transaction ID: <a href="' . $this->pmntUtils->getAdminUrl() . '/merchant/transactions/' . $checkout . '/read " target="_blank"><b>' . $checkout . '</b></a>' . '\n';
        if ($codAmount > 0) {
            $message .= '<b style="color:red;">[NOTICE] </b><i>COD Amount: <b>' . $codAmount . ' ' . $this->session->data['currency'] . '</b></i>' . '\n';
        }
        return $message;
    }

    function wpbo_get_woo_version_number() {
        // If get_plugins() isn't available, require it
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        
        // Create the plugins folder and file variables
        $plugin_folder = get_plugins( '/' . 'woocommerce' );
        $plugin_file = 'woocommerce.php';
        
        // If the plugin version number is set, return it 
        if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
            return $plugin_folder[$plugin_file]['Version'];

        } else {
        // Otherwise return null
            return NULL;
        }
    }

    function get_wp_version() {
        return get_bloginfo('version');
    }
}