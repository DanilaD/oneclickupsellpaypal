<?PHP

class paypal {
  /*
   * variable for redirect user to error page 
   * which setup in configuration file paypal_configuration.php
   * define ERRORPAGE
   */
  var $redirect = TRUE;
  
  public function __construct() {
    
    // the main configuration file
    include_once 'paypal_configuration.php';
    
    /*
     * autoswitch to sandbox or life
     * this is can be setup in paypal_configuration.php
     * define SANDBOX (TRUE|FALSE)
     */
    ( SANDBOX != TRUE ) ? $this->life_url() : $this->sandbox_url();
    
  }
  
  /* setup URLs for life PayPAl
   * this is can be setup in paypal_configuration.php
   */
  private function life_url() {
    
      $this->clientid = LIFE_PAYPAL_CLIENTID;
      $this->secret = LIFE_PAYPAL_SECRET;
      $this->user = LIFE_PAYPAL_USER;
      $this->pwd = LIFE_PAYPAL_PWD;
      $this->signature = LIFE_PAYPAL_SIGNATURE;
      $this->url_ipn = LIFE_PAYPAL_IPN_URL;
      $this->url_api = LIFE_PAYPAL_URL_API;
      $this->url_redirect_to_paypal = LIFE_PAYPAL_REDIRECT_TO_PAYPAL;
      $this->product_file = LIFE_PRODUCT_FILE;
  }
  
  /* setup URLs for sandbox PayPal
   * this is can be setup in paypal_configuration.php
   */
  private function sandbox_url() {
    
      $this->clientid = SANDBOX_PAYPAL_CLIENTID;
      $this->secret = SANDBOX_PAYPAL_SECRET;
      $this->user = SANDBOX_PAYPAL_USER;
      $this->pwd = SANDBOX_PAYPAL_PWD;
      $this->signature = SANDBOX_PAYPAL_SIGNATURE;
      $this->url_ipn = SANDBOX_PAYPAL_IPN_URL;
      $this->url_api = SANDBOX_PAYPAL_URL_API;
      $this->url_redirect_to_paypal = SANDBOX_PAYPAL_REDIRECT_TO_PAYPAL;
      $this->product_file = SANDBOX_PRODUCT_FILE;
  }
  
  // get product's information from array (products.php)
  private function getProductInformation($product_id) {
    
    // include file with products
    include_once $this->product_file;

    // check the existence of the product id in the array
    // variable $products is in file product.php
    if ( array_key_exists($product_id, $products) ) {

      return $products[$product_id];
      
    } else {
      
      /* 
       * show error page | save log | send e-mail
       * the message below can be changed
       */
      $this->errorpage("don't much product " . $product_id .", must check product.php");
      
    }
    
  }
  
  /*  prepare data for getting token, this is the first step
   *  use NVP API PayPal
   *  more information https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
   */
  private function prepareDataforGetBA($token) {

      $post = [
          'USER' => $this->user,
          'PWD' => $this->pwd,
          'SIGNATURE' => $this->signature,
          'METHOD' => 'CreateBillingAgreement',
          'VERSION' => '204',
          'TOKEN' => $token
          ];
   
      return $post;
  }
  
  /*
   *  prepare data for sending to PayPal and redirect user to PayPal
   *  use NVP API PayPal
   *  more information https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
   */
  private function prepareDataforGetToken($params) {
     
      $price = $params['amount'] + $params['tax'] + $params['shipping'];
      
      $post = [
              'USER' => $this->user,
              'PWD' => $this->pwd,
              'SIGNATURE' => $this->signature,
              'METHOD' => 'SetExpressCheckout',
              'VERSION' => '204',
              'PAYMENTREQUEST_0_PAYMENTACTION' => 'AUTHORIZATION',
              'L_BILLINGTYPE0' => 'MerchantInitiatedBillingSingleAgreement',
              'L_BILLINGAGREEMENTDESCRIPTION0' => BILLINGAGREEMENTDESCRIPTION,        
              'RETURNURL' => FIRST_PURCHASE, // redirect to the complete first purchase. $params['return'],
              'CANCELURL' => CANCEL_URL,  
              'LOGOIMG' => LOGOIMG,
              'BRANDNAME' => BRANDNAME, 
              'NOTETOBUYER' => NOTETOBUYER,        
              'PAYMENTREQUEST_0_AMT' => $price,
              'PAYMENTREQUEST_0_CURRENCYCODE' => $params['currency_code'],
              'PAYMENTREQUEST_0_ITEMAMT' => $params['amount'],
              'PAYMENTREQUEST_0_SHIPPINGAMT' => $params['shipping'],
              'PAYMENTREQUEST_0_TAXAMT' => $params['tax'],
              'PAYMENTREQUEST_0_DESC' => $params['item_name'],
              // important INVOICE for ISF
              'PAYMENTREQUEST_0_INVNUM' => $params['os1'], // see file postparser_paypal.php - $params
              // importnt CONTACT for ISF
              'PAYMENTREQUEST_0_CUSTOM' => $params['os0'], // see file postparser_paypal.php - $params
              //'BUYERID' => $params['os0'],
              // not use, we don't make purchase
              //'PAYMENTREQUEST_0_NOTIFYURL' => $this->url_ipn,
              'L_PAYMENTREQUEST_0_NAME0' => $params['item_name'],
              //'L_PAYMENTREQUEST_0_DESC0' => '',
              'L_PAYMENTREQUEST_0_NUMBER0' => $params['item_number'],
              'L_PAYMENTREQUEST_0_AMT0' => $params['amount'],
              'L_PAYMENTREQUEST_0_QTY0' => '1',                 
      ];
      
      return $post;
  }
  
  /*  prepare data for the first purchase and getting billing agreement Id
   *  use NVP API PayPal
   *  more information https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
   */
  private function prepareDataforFirstPurchase($json, $token, $insert_payer_id, $baid, $method) {
    
      $price = $json->amount + $json->tax + $json->shipping;

      $data = [
            'USER' => $this->user,
            'PWD' => $this->pwd,
            'SIGNATURE' => $this->signature,
            'VERSION' => '204',
            'METHOD' => $method,
            'TOKEN' => $token,
            'PAYERID' => $insert_payer_id,
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'SALE',

            'PAYMENTREQUEST_0_AMT' => $price,
            'PAYMENTREQUEST_0_CURRENCYCODE' => $json->currency_code,
            'PAYMENTREQUEST_0_ITEMAMT' => $json->amount,
            'PAYMENTREQUEST_0_SHIPPINGAMT' => $json->shipping,        
            'PAYMENTREQUEST_0_TAXAMT' => $json->tax,

            'PAYMENTREQUEST_0_NOTIFYURL' => $json->notify_url,         

            'L_PAYMENTREQUEST_0_NAME0' => $json->item_name,
            'L_PAYMENTREQUEST_0_NUMBER0' => $json->item_number,
            'L_PAYMENTREQUEST_0_AMT0' => $json->amount,
            'L_PAYMENTREQUEST_0_QTY0' => '1',

            // for IFS see file postparser_paypal.php - $params
            'PAYMENTREQUEST_0_MERCHANTDATAKEY0' => $json->on0,
            'PAYMENTREQUEST_0_MERCHANTDATAVALUE0' => $json->os0,
            'PAYMENTREQUEST_0_MERCHANTDATAKEY1' => $json->on1,
            'PAYMENTREQUEST_0_MERCHANTDATAVALUE1' => $json->os1,

            // for IFS see file postparser_paypal.php - $params
            //'PAYMENTREQUEST_0_CUSTOM' => '&option_selection1=' . $json->os0 . '&option_selection2=' . $json->os1,
            'PAYMENTREQUEST_0_CUSTOM' => $json->os0,
      ];
    
      return $data;
    
  }
  
  /*  prepare data for the purchase, when we have billing agreement Id
   *  use NVP API PayPal
   *  more information https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
   */
  private function prepareDataforOtherPurchase($params, $token, $insert_payer_id, $baid, $method) {

      $data = [
              'USER' => $this->user,
              'PWD' => $this->pwd,
              'SIGNATURE' => $this->signature,
              'METHOD' => $method,
              'VERSION' => '204',
              'TOKEN' => $token,
              'PAYMENTACTION' => 'SALE',
              'PAYERID' => $insert_payer_id,
              'REFERENCEID' => $baid,
        
              'NOTIFYURL' => $params['notify_url'],
              'RETURNURL' => $params['return'],
              //'CANCELURL' => $params['cancel'],        
              'NUMBER' => $params['id'],
              
              'L_NAME0' => $params['ProductName'],
              'L_NUMBER0' => $params['id'],
              'L_QTY0' => '1',
              'L_AMT0' => $params['ProductPrice'],
              'AMT' => $params['ProductPrice'],
              'CURRENCYCODE' => $params['CurrencyCode'],
              //'ITEMAMT' => $params['ProductPrice'] - $params['tax'],
              //'TAXAMT' => $params['tax'],
              //'CUSTOM' => '',item_number
      ];
      
      return $data;
  }
  
  /*  send all cURL to NVP API PAYPAL
   *  use NVP API PayPal
   *  more information https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
   */
  private function sendCurl($data) {
      $post = http_build_query($data);
      $ch = curl_init();
      curl_setopt ($ch, CURLOPT_URL, $this->url_api);
      curl_setopt ($ch, CURLOPT_HEADER, false);
      curl_setopt ($ch, CURLOPT_POST, 1);
      curl_setopt ($ch, CURLOPT_POSTFIELDS, $post); 
      curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true); 
      curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 3); // 3 seconds to connect
      curl_setopt ($ch, CURLOPT_TIMEOUT, 10); // 10 seconds to complete
      $response = curl_exec($ch);
      curl_close($ch);
      return $response; 
  }
  

  /* process a response from paypal
   *  use NVP API PayPal
   *  more information https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
   */
  
  private function convertFromUrlToArray($response) {
    
    if (preg_match_all('/(?<name>[^\=]+)\=(?<value>[^&]+)&?/', $response, $matches)) {
        
        foreach ($matches['name'] as $offset => $name) {
          
            $nvp[$name] = urldecode($matches['value'][$offset]);   
            
        }
        
        return $nvp;
        
    } else {
      
      /* 
       * show error page | save log | send e-mail
       * the message below can be changed
       */
        $this->errorpage($response);
      
    }
    
  }
  

  // first step to get token (open autorization window PaPal)
  public function GetToken($params) {
    
    $json = json_encode($params);
    
    // save data about product in cookie ans session
    setcookie('first_product', $json, time()+86400, '/');
    $_SESSION['first_product'] = $json;
    
    // get data for cURl
    $data = $this->prepareDataforGetToken($params);
    
    // send request to PayPal
    $response = $this->sendCurl($data);
      
    // get result
    $nvp = $this->convertFromUrlToArray($response);   
    
    // check response
    if (isset($nvp['ACK']) && $nvp['ACK'] == 'Success') {
        
        $query = [
                    'cmd'         => '_express-checkout',
                    'token'       => $nvp['TOKEN'],
                    'useraction'  => 'commit'
                  ];
        
        $result = [
                    'link' => sprintf($this->url_redirect_to_paypal, http_build_query($query)),
                    'token' => $nvp['TOKEN'],
                    ];
           
        // redirect to PayPal for Login and Get Billing Agreement
        header('Location: ' . $result['link']);

        return $result;
     
    } else {
      
      /* 
       * show error page | save log | send e-mail
       * the message below can be changed
       */
        $this->errorpage($response);
      
    } 

  }

  // get billing areement Id from PayPal
  public function GetBillingAgreement($token) {
     
    // prepare data to send to PayPal
    $data = $this->prepareDataforGetBA($token);

    // send request to PayPal
    $response = $this->sendCurl($data);
    
    // get result
    $nvp = $this->convertFromUrlToArray($response);
    
    // check data
    if (isset($nvp['ACK']) && $nvp['ACK'] == 'Success') {
        $result = [
          'baid' => $nvp['BILLINGAGREEMENTID']
          ];
        
        // return only billing agreement id
        return $result['baid'];
        
    } else {
      
      /* 
       * show error page | save log | send e-mail
       * the message below can be changed
       */
        $this->errorpage($response);
        
    }
  
}

// make purchase
public function Purchase($data) {

    // send request to PayPal
    $response = $this->sendCurl($data);

    // get response
    $nvp = $this->convertFromUrlToArray($response);
    
    // check response
    if (isset($nvp['ACK']) && $nvp['ACK'] == 'Success') {
        
        // get payment status
        $paymentinfo = $this->checkPaymentStatus($nvp);
         
        // check paymentstatus
        if ( isset($paymentinfo) ) {

          $result = [
            'paymentinfo' => $paymentinfo
            ];
        
          return $result;
          
        } else {
          
          /* 
           * show error page | save log | send e-mail
           * the message below can be changed
           */
            $this->errorpage($response);    
            
        }
        
        
    } else {
      
      /* 
       * show error page | save log | send e-mail
       * the message below can be changed
       */
        $this->errorpage($response);
      
    }
  
}

// check payment status from paypal, when user made perchase
private function checkPaymentStatus($nvp) {
    
    $paymentinfo = '';
    
  // for one method
    if ( isset($nvp['PAYMENTINFO_0_PAYMENTSTATUS']) ) {

        $paymentinfo = $nvp['PAYMENTINFO_0_PAYMENTSTATUS'];

  // for second method    
    } elseif ( isset($nvp['PAYMENTSTATUS']) ) {

        $paymentinfo = $nvp['PAYMENTSTATUS'];

    }
        
  return $paymentinfo;
  
}

// make first purchase
public function MakeFirstPurchase() {
  
    // get token and payerid from PayPal for the first Purchase in order to get BAID
    $get_param = $this->getTokenPayerIdfromUrl();

    // get BAID from PayPal
    $baid = $this->GetBillingAgreement($get_param['token']);
    
    // save to session and in cookie
    $_SESSION['token'] = $get_param['token'];
    setcookie('token', $get_param['token'], time()+86400, '/');
    $_SESSION['payerid'] = $get_param['payerid'];
    setcookie('payerid', $get_param['payerid'], time()+86400, '/');
    $_SESSION['baid'] = $baid;
    setcookie('baid', $baid, time()+86400, '/');       

    // get information about first product
    $json = filter_input(INPUT_COOKIE, 'first_product');
    
    if ( !empty($json) ) {

        $params = json_decode($json);

        // prepare data for transferring
        $data = $this->prepareDataforFirstPurchase($params, $get_param['token'], $get_param['payerid'], $baid, 'DoExpressCheckoutPayment');

        // make purchase
        $this->Purchase($data);

        // get information about product
        $product = $this->getProductInformation($params->item_number); 
        
        // redirect to next product
        header('location:' . $product['return']);
        exit;

    } else {

      /* 
       * show error page | save log | send e-mail
       * the message below can be changed
       */
        $this->errorpage('empty data about product');

    }
                
}

// get token and payerid from PayPal for the first Purchase in order to get BAID
private function getTokenPayerIdfromUrl() {
  
    $result['token'] = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
    $result['payerid'] = filter_input(INPUT_GET, 'PayerID', FILTER_SANITIZE_STRING);
    
    if ( empty($result['token']) || empty($result['payerid']) ) {

      /* 
       * show error page | save log | send e-mail
       * the message below can be changed
       */
        $this->errorpage('empty: token and payerid');

    }
    
    return $result;
  
}

// get data from cookie or session
public function GetVariablesFromSession() {
  
    // nessasary variables
    $cookie_variable = [ 'token', 'payerid', 'baid' ];

    // get and check data
    foreach ($cookie_variable as $value) {

        // get from cookie 
        $variable[$value] = filter_input(INPUT_COOKIE, $value, FILTER_SANITIZE_STRING);

        // check, if empty, get from session
        if ( empty($variable[$value]) ) {

            $variable[$value] = filter_input(INPUT_SESSION, $value, FILTER_SANITIZE_STRING);
            
        }
        // if empty, error page
        if ( empty($variable[$value]) ) {

           /* 
            * show error page | save log | send e-mail
            * the message below can be changed
            */
            $this->errorpage('Empty data from session: ' . implode(', ', $cookie_variable));
            exit;
        }
    }
    
    return $variable;
  
}
  
// make purchase , when user has billing agreement ID
  public function MakeOtherPurchase($product_id) {
    
      // don't redirect to error page
      $this->redirect = FALSE;
    
      // get information from cookie or session (token, payerid, baid)
      $variable = $this->GetVariablesFromSession();
      
      // get data of product
      $params = $this->getProductInformation($product_id);    

      // prepare data for transferring
      $data = $this->prepareDataforOtherPurchase($params, $variable['token'], $variable['payerid'], $variable['baid'], 'DoReferenceTransaction');
      
      // make purchase
      $this->Purchase($data);
      
      // return link for redirect
      return $params['return'];

  }

// save responce from PayPal
  private function saveMessagePayPal($response){
    // the name of file in paypal_configuration.php
    $handle = fopen(LOGPAYPAL, "a");
    fwrite($handle, $response . "\n");
    fclose($handle);

  }

// display errorpage and save response to log
  public function errorpage($response) {
    
    // check 
    if ( empty ($response) ) {
        
          $response = ' empty response from paypal'; 
            
    }
    
    // save response to log
    $this->saveMessagePayPal($response);
    
    // send message
    $this->SendMessageByMail($response);
    
    // display error's page
    $this->displayError();
    
  }
  
  // redirect user to error page
  private function displayError () {

    // for jQuery need id to switch off 
    if ( $this->redirect == TRUE) {
      
      header('Location: ' . ERRORPAGE);
      
      exit;
      
    }
    
  }

  // send message with Error to administrators 
  private function SendMessageByMail($response) {
    
      $json = json_encode($response);
      // emails configurate in paypal_configuration.php
      mail(SENDMESSAGE, 'Error', $json, ''); 
    
  }
  
}