<?php

// get Id product for purchase (click on button)

// get Id product from button
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);

// get token and payerid from PayPal for the first Purchase in order to get BAID
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
$payerid = filter_input(INPUT_GET, 'PayerID', FILTER_SANITIZE_STRING);

include 'paypal_class.php';
$paypal_new = NEW paypal;

// first purchase to get BAID
if ( $product_id ) {
      
    // don't redirect
    $paypal_new->redirect = FALSE;
  
    // make purchase    
    $redirect = $paypal_new->MakeOtherPurchase($product_id);
    
    echo $redirect;
    
} elseif ( $token && $payerid ) {

   /*
    * FOR NEW LOGIC PAYPAL
    * make first purchase
    * it's important
    * this is a first page after redirect from PayPal
    * 
    * if success than user will redirect to another product 
    * 
    */
    $paypal_new->MakeFirstPurchase($token, $payerid);

} else {
  
    // error
    $paypal_new->errorpage('purchase.php doesn\'t have parameters.');
  
}

exit;
