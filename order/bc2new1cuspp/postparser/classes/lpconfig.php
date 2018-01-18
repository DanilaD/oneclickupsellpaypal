<?php
class CCDPostParserLPConfig{
    //Form fields
    protected $contactFields = null;
    protected $billingFields = null;
    protected $shippingFields = null;
    protected $paymentFields = null;

    //Pricing fields
    protected $productPrice = null;
    protected $usShippingCost = null;
    protected $nonUsShippingCost = null;
    protected $merchantId = null;

    //Tax fields
    protected $taxRate = null;
    protected $taxStateShortcode = null;

    //Product fields, since we do not query product info from IFS
    protected $ifsProductId = null;
    protected $productName_ui = null;
    protected $productName = null;

    //The Payment success/failure tags
    protected $paymentSuccessTagId = null;
    protected $paymentFailureTagId = null;

    //The broken-down thank-you url
    protected $thankyou_url_array = null;

    public function __construct($contactFields, $billingFields, $shippingFields, $paymentFields){
        //Check the contact fields
        foreach($contactFields AS $field){
            if(!is_a($field, 'CCDPostParserFormField')){
                throw new CCDPostParserException('CCDPostParserLPConfig::construct was given a non-CCDPostParserFormField contact field: '.json_encode($field), 1);
            }
        }
        $this->contactFields = $contactFields;

        //Check the billing fields
        foreach($billingFields AS $field){
            if(!is_a($field, 'CCDPostParserFormField')){
                throw new CCDPostParserException('CCDPostParserLPConfig::construct was given a non-CCDPostParserFormField billing field: '.json_encode($field), 2);
            }
        }
        $this->billingFields = $billingFields;

        //Check the shipping fields
        foreach($shippingFields AS $field){
            if(!is_a($field, 'CCDPostParserFormField')){
                throw new CCDPostParserException('CCDPostParserLPConfig::construct was given a non-CCDPostParserFormField shipping field: '.json_encode($field), 3);
            }
        }
        $this->shippingFields = $shippingFields;

        //Initialize the payment fields
        foreach($paymentFields AS $field){
            if(!is_a($field, 'CCDPostParserFormField')){
                throw new CCDPostParserException('CCDPostParserLPConfig::construct was given a non-CCDPostParserFormField payment field: '.json_encode($field), 3);
            }
        }
        $this->paymentFields = $paymentFields;

        //Initialize the pricing fields
        if(!defined('CCD_POSTPARSER_IFS_PRICE_USD') || (float)CCD_POSTPARSER_IFS_PRICE_USD <= 0){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct could not locate a valid product price config', 4);
        }
        $this->productPrice = (float)CCD_POSTPARSER_IFS_PRICE_USD;


        //Initialize the shipping costs
        if(!defined('CCD_POSTPARSER_SHIPPING_COST_US_CANADA_USD') || (float)CCD_POSTPARSER_SHIPPING_COST_US_CANADA_USD < 0){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct could not locate a valid US/Canada shipping cost', 5);
        }
        if(!defined('CCD_POSTPARSER_SHIPPING_COST_OUTSIDE_US_USD') || (float)CCD_POSTPARSER_SHIPPING_COST_OUTSIDE_US_USD < 0){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct could not locate a valid non-US/Canada shipping cost', 6);
        }
        $this->usShippingCost = (float)CCD_POSTPARSER_SHIPPING_COST_US_CANADA_USD;
        $this->nonUsShippingCost = (float)CCD_POSTPARSER_SHIPPING_COST_OUTSIDE_US_USD;

        //Initialize the tax costs
        if(!defined('CCD_POSTPARSER_TAX_PERCENTAGE') || (float)CCD_POSTPARSER_TAX_PERCENTAGE < 0 || (float)CCD_POSTPARSER_TAX_PERCENTAGE > 20){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct could not locate a valid tax percentage (must be between 0 and 20)', 7);
        }
        $this->taxRate = (float)CCD_POSTPARSER_TAX_PERCENTAGE;

        if(!defined('CCD_POSTPARSER_TAX_STATE_SHORTCODE')){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct could not locate a tax state configuration', 8);
        }
        $this->taxStateShortcode = CCD_POSTPARSER_TAX_STATE_SHORTCODE;

        //Initialize the merchant id
        if(!defined('CCD_POSTPARSER_MERCHANT_ID')){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct could not locate a merchant id configuration', 8);
        }
        $this->merchantId = CCD_POSTPARSER_MERCHANT_ID;

        //The product id
        if(!defined('CCD_POSTPARSER_IFS_PRODUCT_ID')){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct could not locate the product id', 9);
        }
        if(!($this->ifsProductId = (int)CCD_POSTPARSER_IFS_PRODUCT_ID)){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct received an invalid product id', 9);
        }

        //The product name for the UI
        if(!defined('CCD_POSTPARSER_IFS_PRODUCT_NAME_UI')){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct could not locate the product UI name', 10);
        }
        if(!($this->productName_ui = (String)CCD_POSTPARSER_IFS_PRODUCT_NAME_UI)){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct received an invalid product UI name', 10);
        }

        //The product name for the IFS orders
        if(!defined('CCD_POSTPARSER_IFS_PRODUCT_NAME')){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct could not locate the product name', 10);
        }
        if(!($this->productName = (String)CCD_POSTPARSER_IFS_PRODUCT_NAME)){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct received an invalid product name', 10);
        }

        //The thank-you page
        if(!defined('CCD_POSTPARSER_THANKYOU_URL')){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct could not locate the thank-you page URL', 10);
        }
        if(!($this->thankyou_url_array = parse_url((String)CCD_POSTPARSER_THANKYOU_URL))){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct received an invalid thank-you page URL', 10);
        }
        /*
         if(!($this->thankyou_url = (String)CCD_POSTPARSER_THANKYOU_URL)){
         throw new CCDPostParserException('CCDPostParserLPConfig::construct received an invalid thank-you page URL', 10);
         }
         */

        //The payment success tag
        if(!defined('CCD_POSTPARSER_PAYMENT_SUCCESS_TAG')){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct could not locate the payment-success tag id', 10);
        }
        if(!($this->paymentSuccessTagId = (int)CCD_POSTPARSER_PAYMENT_SUCCESS_TAG)){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct received an invalid payment-success tag id', 10);
        }

        //The payment failure tag
        if(!defined('CCD_POSTPARSER_PAYMENT_FAILURE_TAG')){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct could not locate the payment-failure tag id', 10);
        }
        if(!($this->paymentFailureTagId = (int)CCD_POSTPARSER_PAYMENT_FAILURE_TAG)){
            throw new CCDPostParserException('CCDPostParserLPConfig::construct received an invalid payment-failure tag id', 10);
        }

        //Done
    }

    public function getProductUIName(){return $this->productName_ui;}
    public function getProductName(){return $this->productName;}

    /**
     * Returns the pricing details of the product for the given shipping destination
     * @param String $city
     * @param String $state
     * @param String $country
     * @return stdClass				With member variables: price, tax, shipping. Sum up for the totals
     */
    public function calculatePrices($city, $state, $country){
        $result = new stdClass();

        //Setup the default no-tax, no-shipping prices
        $result->price = $this->productPrice;
        $result->shipping = 0;
        $result->tax = 0;

        //See if there's shipping involved (Hint: There always is right now)
        if($country=='Canada' || $country == 'United States'){
            $result->shipping = $this->usShippingCost;
        } else {
            $result->shipping = $this->nonUsShippingCost;
        }

        //See if there is tax involved
        if($country=='United States' && $state == $this->taxStateShortcode){
            $result->tax = round(($this->productPrice * $this->taxRate) /100, 2);
        }

        //Done
        return $result;
    }

    /**
     * Returns a javascript function named ccdCalculatePrices that calculates the prices given the shipping destination
     * It returns an object with the price, shipping, tax, totalPrice member variables
     * @return string
     */
    public function getCalculatePricesJavascript(){
        $returnArray = array('function ccdCalculatePrices(city, state, country){');

        //Compose the result object with the no-tax, no-shipping prices
        $returnArray[] = 'result = {};';
        $returnArray[] = 'result.price = '.json_encode($this->productPrice).';';

        //Shipping cost calculations
        $returnArray[] = 'if(country == "Canada" || country == "United States"){';
        $returnArray[] = 'result.shipping= '.json_encode($this->usShippingCost).';';
        $returnArray[] = '}else{';
        $returnArray[] = '	result.shipping = '.json_encode($this->nonUsShippingCost).';';
        $returnArray[] = '}';

        //Tax cost calculations. They are affected by shipping, so they come 2nd
        //@TODO: Possible rounding error here that creates a discrepancy of 0.01$. Here we round up, in PHP we probably round down
        $returnArray[] = 'if(country == "United States" && state == '.json_encode($this->taxStateShortcode).'){';
        $returnArray[] = '	result.tax = (Math.round(result.price * '.json_encode($this->taxRate).')/100) ;';
        $returnArray[] = '}else{';
        $returnArray[] = '	result.tax = 0;';
        $returnArray[] = '}';

        $returnArray[] = 'result.totalPrice = Math.round((result.price + result.shipping + result.tax)*100)/100;';





        /*
         //Add the has-tax conditional
         $returnArray[] = 'if(country == "US" && state == '.json_encode($this->taxStateShortcode).'){';
         $tax = round($this->taxRate * $this->productPrice/100, 2);
         $returnArray[] = '	result.tax = '.json_encode($tax).';';
         $taxed_price = $tax + $this->productPrice;
         $returnArray[] = '};';

         //Calculate the total price
         $returnArray[] = 'result.totalPrice = result.price + result.tax + result.shipping;';
         */

        //Done
        $returnArray[] = 'return result;';
        $returnArray[] = '}';
        return implode("\n", $returnArray);
    }

    /**
     * Fetches the Contact field configuration
     * @return Array[CCDPostParserFormField]
     */
    public function getContactFields(){return $this->contactFields;}

    /**
     * Fetches the Billing field configuration
     * @return Array[CCDPostParserFormField]
     */
    public function getBillingFields(){return $this->billingFields;}

    /**
     * Fetches the Shipping field configuration
     * @return Array[CCDPostParserFormField]
     */
    public function getShippingFields(){return $this->shippingFields;}

    /**
     * Fetches the Payment field configuration
     * @return Array[CCDPostParserFormField]
     */
    public function getPaymentFields(){return $this->paymentFields;}

    /**
     * Fetches the configured product id
     * @return String			The IFS Product Id
     */
    public function getProductId(){return $this->ifsProductId;}

    /**
     * Fetches the configured product id
     * @return String			The IFS Product Id
     */
    public function getMerchantId(){return $this->merchantId;}
    
    /**
     * Fetches the configured success tag id
     * @return Int			The IFS tag id
     */
    public function getPaymentSuccessTagId(){return (int)$this->paymentSuccessTagId;}
    
    /**
     * Fetches the configured failure tag id
     * @return Int			The IFS tag id
     */
    public function getPaymentFailureTagId(){return (int)$this->paymentFailureTagId;}
    

    public function getThankyouUrl($contact_id, $order_id, $contactArray, stdClass $prices){
        //return $this->thankyou_url;
        $urlArray = $this->thankyou_url_array;

        //1. Extract any possibly pre-existing GET params in the thank-you url as an array
        $query_array = array();
        if(!empty($urlArray['query'])){
            parse_str($urlArray['query'], $query_array);
        }

        //2. Append the contact data to the GET params array (that may or may not be empty)
        //$query_array['paypal_switch'] = 'credit_card';
        //$query_array['infusion_name'] = $this->productName;
        //$query_array['Order'] = 'Order';
        //$query_array['infusion_xid'] = 'c8266cf23d1710ea62912b6b45941ae6';
        //$query_array['infusion_type'] = 'CustomFormSale';
        /*
        if(!empty($contactArray['LeadSource'])){
        $query['LeadSource'] = $contactArray['LeadSource'];
        }
        */
        $query_array['contactId'] = (int)$contact_id;
        $query_array['ProductPrice'] = ($prices->price + $prices->tax + $prices->shipping);
        $query_array['PurchaseType'] = 'A';
        $query_array['ProductId'] = $this->ifsProductId;
        /* Removed personally-identifying fields as per the Google requirements
        $query_array['Contact0Email'] = $contactArray['Email'];
        $query_array['Contact0FirstName'] = $contactArray['FirstName'];
        $query_array['Contact0LastName'] = $contactArray['LastName'];
         */
        $query_array['orderId'] = (int)$order_id;

        $urlArray['query'] = http_build_query($query_array);

        //3. Transform the array back to a URL
        if(empty($urlArray['host'])){
            CCDIPN::failIPN('getThankyouUrl() Could not find a host part for the thankyou URL ');
        }
        $url = !empty($urlArray['scheme']) ? $urlArray['scheme'].'://' : 'https://';    //Default the scheme to https if none found
        $url .=  $urlArray['host'];
        if(!empty($urlArray['path'])){
            $url .= $urlArray['path'];
        }
        //query is here as we just added to it
        $url .= '?'.$urlArray['query'];
        if(!empty($urlArray['fragment'])){
            $url .= '#'.$urlArray['fragment'];
        }

        //Done
        return $url;
    }
}