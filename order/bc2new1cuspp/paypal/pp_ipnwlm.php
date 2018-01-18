<?php

/*
 * IPN script based on https://gist.githubusercontent.com/xcommerce-gists/3440401/raw/1b01453cc3e3aa091f2d60cb95a1e6e36d094309/completeListener.php
 * Version: 0.1
 * Changelog:
 * 		v0.1 (Apr 24 2014): First version
 * 		v0.2 (Apr 29 2014): 
 * 			* Added tx_id in all errors/warnings 
 * 			* Dropped the 'found duplicate transaction id' from warnings
 * 		v0.3 (May 30 2014)
 * 			* No warning messages for payment_status = [Refunded|Pending|Reversed]
 * 			* Succeeding the IPN with no warning/errors for txn_type=new_case and case_type=[dispute|complaint]
 * 		v0.5 (Apr 14 2015)
 * 			* Adding the txn_type=new_case case_type='Canceled_Reversal'/'chargeback' to the success/ignore list.
 * 		v0.6 (June 15 2015)
 * 			* Better eror messages and handling for 'no WP user found' and 'paypal email not found' cases
 * 		v0.7 (July 14 2017)
 * 			* Adding support for item_numer1
 * 		v0.7.1 (August 21 2017):
 * 			* Ignoring txn_type=adjustment cases
 */

/*
 * PRODUCT CONFIGURATION
 */


// save all requests
$myfile = fopen("file.txt", "a") or die("Unable to open file!");
$txt = json_encode($_REQUEST);
$_POST = $_REQUEST;
fwrite($myfile, $txt. "\n");
fclose($myfile);


//Require our configuration script and common functions
require_once '../configuration.php';
require_once '/home/healthplus50/public_html/order/bc2new1cuspp/postparser/common_functions.php';

//To enable easy debugging, we copy the GET params to POST
if(defined('CCD_CONF_DEBUG') && CCD_CONF_DEBUG){
    $_POST = $_GET;
}

//Extract the basic variables from the IPN post data
$item_name = isset($_POST['item_name'])?$_POST['item_name']:'';

//The item number sometime comes in the item_numberX format (). So check for that too.
$item_number = isset($_POST['item_number'])?$_POST['item_number']:'';
if(!$item_number){
	$item_number = isset($_POST['item_number1'])?$_POST['item_number1']:'';
}

$payment_status = isset($_POST['payment_status'])?$_POST['payment_status']:'';
$payment_amount = isset($_POST['mc_gross'])?$_POST['mc_gross']:'';
$payment_currency = isset($_POST['mc_currency'])?$_POST['mc_currency']:'';
$txn_id = isset($_POST['txn_id'])?$_POST['txn_id']:'';
$receiver_email = isset($_POST['receiver_email'])?$_POST['receiver_email']:'';
$payer_email = isset($_POST['payer_email'])?$_POST['payer_email']:'';

//txn_types we silently ignore
$ignore_txn_types = array('adjustment');
if(isset($_POST['txn_type']) && in_array($_POST['txn_type'], $ignore_txn_types) ){
	return;
}

//We silently ignore txn_type = new_case && case_type=complaint
if(isset($_POST['txn_type']) && isset($_POST['case_type']) && $_POST['txn_type']=='new_case'){
    switch($_POST['case_type']){
        case 'dispute':
        case 'complaint':
        case 'chargeback':
        case 'Canceled_Reversal':
            return;    //disputes and complaints are silently ignored
            break;
        default:
            CCDIPN::failIPN(__FILE__.' received an unknown case_type for a txn_type=new_case: '.http_build_query($_POST));
    }
}

//Verify the required fields

print_r($item_number .'+|+'. $payment_status .'+|+'. $payment_amount .'+|+'. $payment_currency .'+|+'. $txn_id .'+|+'. $receiver_email .'+|+'. $payer_email);


if(!$item_number || !$payment_status || !$payment_amount || !$payment_currency || !$txn_id || !$receiver_email || !$payer_email){
    CCDIPN::failIPN(__FILE__.' received incorrect/missing IPN post data: '.http_build_query($_POST));
}

//Initialize the IFS iSDK
require_once '/home/healthplus50/public_html/order/bc2new1cuspp/postparser/isdk.php';
$app = new iSDK;
$app_name = (defined('CCD_CONF_SANDBOX') && CCD_CONF_SANDBOX) ? CCD_CONF_IFS_SANDBOX_APPNAME : CCD_CONF_IFS_APPNAME;
$api_key = (defined('CCD_CONF_SANDBOX') && CCD_CONF_SANDBOX) ? CCD_CONF_IFS_SANDBOX_API_KEY : CCD_CONF_IFS_API_KEY;
$api_mode = (defined('CCD_CONF_SANDBOX') && CCD_CONF_SANDBOX) ? CCD_CONF_IFS_SANDBOX_API_MODE : CCD_CONF_IFS_API_MODE;
try{
    $app->cfgCon($app_name, $api_key, $api_mode);
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an error while initializing the iSDK: '.$e->getMessage().' for txn id: '.$txn_id);
}


// STEP 1: read POST data

// Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
// Instead, read raw POST data from the input stream.
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode ('=', $keyval);
    if (count($keyval) == 2)
    $myPost[$keyval[0]] = urldecode($keyval[1]);
}
// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) {
    $get_magic_quotes_exists = true;
}
foreach ($myPost as $key => $value) {
    if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
        $value = urlencode(stripslashes($value));
    } else {
        $value = urlencode($value);
    }
    $req .= "&$key=$value";
}


// STEP 2: POST IPN data back to PayPal to validate

//webscr verification target is based on whether we're in sandbox mode or not
$webscr_location = (defined('CCD_CONF_SANDBOX') && CCD_CONF_SANDBOX) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
$ch = curl_init($webscr_location);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

// In wamp-like environments that do not come bundled with root authority certificates,
// please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set
// the directory path of the certificate as shown below:
// curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
if( !($res = curl_exec($ch)) ) {
    CCDIPN::failIPN(__FILE__.' encountered an error while responding to the IPN: '.curl_error($ch).' for txn id: '.$txn_id);
}
curl_close($ch);


// STEP 3: Inspect IPN validation result and act accordingly unles we're in debug mode
if (defined('CCD_CONF_DEBUG') && CCD_CONF_DEBUG && strcmp ($res, "VERIFIED") === false){
    CCDIPN::failIPN(__FILE__.' received a non-VERIFIED response from the IPN verifier: '.$res.' for txn id: '.$txn_id);
}

//@TODO-GN: we should really move these to the paypal 'custom' fields. Maybe serialize them both
$product_id = 7; //The product sold by this upsell
$group_id = 258;    //The tag id to apply to the contact upon a succesful upsell
switch($item_number){
  
    // Plus Package Physical
    case '25':
        $product_id = 25;
        $group_id = 251;
        break;
      
    // Plus Package Physical
    case '11':
        $product_id = 11;
        $group_id = 251;
        break;
      
    // Plus Package Physical
    case '6':
        $product_id = 6;
        $group_id = 251;
        break;
        
    // Plus Package Physical - $24.99
    case '94':
        $product_id = 94;
        $group_id = 478;
        break;
        
    // Plus Package Digital
    case '56':
        $product_id = 56;
        $group_id = 251;
        break;
        
    // Boot Camp Physical
    case '7':
        $product_id = 7;
        $group_id = 258;
        break;
        
    // Boot Camp Physical - $49.99
    case '78':
        $product_id = 78;
        $group_id = 476;
        break;
        
    // Boot Camp Digital
    case '54':
        $product_id = 54;
        $group_id = 258;
        break;
        
    // Functional Fitness Physical
    case '9':
        $product_id = 9;
        $group_id = 283;
        break;
        
    // Functional Fitness Physical - $9.99
    case '96':
        $product_id = 96;
        $group_id = 480;
        break;

    // Functional Fitness Digital
    case '58':
        $product_id = 58;
        $group_id = 283;
        break;
        
    // Carb Defender 1 Bottle
    case '13':
		$product_id = 13;
        $group_id = 572;
        break;
        
    // Carb Defender 3 Bottles
    case '15':
		$product_id = 15;
        $group_id = 592;
        break;
        
    // Carb Defender 6 Bottles
    case '17':
		$product_id = 17;
        $group_id = 594;
        break;
    
    // Carb Defender 3 Bottle special
    case '19':
		$product_id = 19;
        $group_id = 578;
        break;
     
    // Carb Defender 6 Bottle special
    case '21':
		$product_id = 21;
        $group_id = 576;
        break;
		
	// Crave Erase 1 Bottle
	case '130' :
		$product_id = 130;
		$group_id = 598;
		break;
		
	// Crave Erase 3 Bottles
	case '132':
		$product_id = 132;
		$group_id = 600;
		break;
		
	// Crave Erase 6 Bottles
	case '134':
		$product_id = 134;
		$group_id = 602;
		break;
	
	// Crave Erase 3 Bottle special
	case '138' :
		$product_id = 138;
		$group_id = 608;
		break;
	
	// Crave Erase 6 Bottle special
	case '136' :
		$product_id = 136;
		$group_id = 606;
		break;
        
    default:
        CCDIPN::failIPN(__FILE__.' encountered an unsupported item number:: '.$item_number.' for transaction id: '.$txn_id);
}

//Make sure that the payment_status is verified
/*
if($payment_status !== 'Completed'){
    CCDLogger::logWarning(__FILE__.' received a non-Completed payment status: '.$payment_status.' for txn id: '.$txn_id);
    return;
}
*/
switch($payment_status){
    case 'Completed':
        break;    //This is the normal response and we keep executing
    case 'Refunded':
        return;    //We silently accept Refunded payment IPN's
        break;
    case 'Pending':
        return;    //We silently accept Pending payments. When the payment is 'Complete', then we will catch the new IPN
        break;
    case 'Reversed':
    case 'Canceled_Reversal':
        return;   //We silently accept 'Canceled/Reversed' payments
        break;
    default:
        CCDLogger::logWarning(__FILE__.' received an unsupported payment status: '.$payment_status.' for txn id: '.$txn_id);
        break;
}

//Make sure that we're receiving a payment for the correct account.
$expected_receiver_email = (defined('CCD_CONF_SANDBOX') && CCD_CONF_SANDBOX) ? CCD_CONF_PAYPAL_SANDBOX_ACCOUNT_EMAIL : CCD_CONF_PAYPAL_ACCOUNT_EMAIL;
if($receiver_email != $expected_receiver_email){
    CCDIPN::failIPN(__FILE__.' received an invalid receiver email: '.$receiver_email.' (expected email was: '.$expected_receiver_email.')');
}

//Check that this transaction id has never been processed before. If it has, we WILL VERIFY the IPN by exiting and not failing
try{
    $found_payments = $app->dsQuery('Payment', 1, 0, array('PayNote'=>'%'.$txn_id.'%'), array('InvoiceId','ContactId'));
    if(count($found_payments)){
        //CCDLogger::logWarning(__FILE__.' found a pre-existing invoice (invoiceId: '.$found_payments[0]['InvoiceId'].' on contactId: '.$found_payments[0]['ContactId'].') payment with the same paypal transaction id: '.$txn_id);
        return;
    }
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an IFS API error while checking for pre-existing txn-id: '.$e->getMessage().' for txn id: '.$txn_id);
}

//Locate the contact based on the paypal email
try{
    $found_contacts = $app->dsQueryOrderBy('Contact', 2, 0, array('_paypalemail'=>(string)$payer_email), array('Id','Email'),'Id',false);
    //Make sure that we found at least one contact
    if(!count($found_contacts)){
        CCDLogger::logError(__FILE__.' could not locate a contact by the payer email: '.$payer_email.' for txn id: '.$txn_id."\n This means that the buyer most likely used a different paypal accounts to buy the 1st sale and the upsell. \nACTIONS REQUIRED: \n1. Try to find the Infusionsoft account by searching for the paypal email and/or extrapolating a name for the email (".$payer_email."). Then Please manually add his upsell. \n2)If DISK has already received a report with the original sale, do contact them so the upsell can be packaged along with the original order");
        return;
        //CCDIPN::failIPN(__FILE__.' could not locate a contact by the payer email: '.$payer_email.' for txn id: '.$txn_id);
    }
    //Check and see if there are more than one contacts with the same payer email. If so, just log a warning
    if(count($found_contacts) > 1){
        CCDLogger::logWarning(__FILE__.' found more than one contacts with the same payer email: '.$payer_email.'. Using the contact with the greatest Id  (txn id: '.$txn_id.')');
    }
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an error while trying to locate the contact by the payer email('.$payer_email.'):  '.$e->getMessage().' for txn id: '.$txn_id);
}

//Extrapolate the contactId and Email
$contactId = $found_contacts[0]['Id'];
$contactEmail = isset($found_contacts[0]['Email'])?$found_contacts[0]['Email']:'';    //If no Email is set, the array key is not present in IFS replies

//Make sure that there is a contact email set
if(empty($contactEmail)){
    CCDIPN::failIPN(__FILE__.' located a contact(id: '.$contactId.'):  with an empty Email'.' for txn id: '.$txn_id);
}

//Place the order
$order_result = array();
try{
    $order_result = $app->placeOrder((int)$contactId, FALSE, 0, array((int)$product_id), array(), FALSE, array());
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an error while trying to place an order:  '.$e->getMessage().' for txn id: '.$txn_id);
}

//Extrapolate the invoice id from the result of the order placement
$invoice_id = isset($order_result['InvoiceId']) ? $order_result['InvoiceId'] : false;
if(!$invoice_id){
    $msg_array = is_array($order_result) ? $order_result : array($order_result);
    CCDIPN::failIPN(__FILE__.' failed to create an order (no invoice id returned). Order result was:  '.implode(',', $msg_array).' for txn id: '.$txn_id);
}

//Make the payment
try{
    $oDate = $app->infuDate(date("d-m-Y"));
    $result = $app->manualPmt((int)$invoice_id, (float)$payment_amount, $oDate, "PayPal", "Processed via API for paypal transaction id: ".$txn_id, FALSE);
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an error while trying to mark an invoice(id: '.$invoice_id.') paid:  '.$e->getMessage().' for txn id: '.$txn_id);
}

//Assign the group/tag, but ignore if it failed
try{
    $app->grpAssign( $contactId, $group_id );
} catch(Exception $e){
    CCDLogger::logWarning(__FILE__.' encountered an error while trying to assign the tag/group id '.$group_id.' to the contact id '.$contactId.' :  '.$e->getMessage().' for txn id: '.$txn_id);
}

/*
 * WORDPRESS CODE
 * WARNING: From here on, nothing gets stored in the logfiles because some of the following scripts gags all logging


//Include the WP files
require_once('/home/honestw/public_html/wp-blog-header.php');
//require_once('../wordpress/wp-blog-header.php');
require_once('/home/honestw/public_html/wp-content/plugins/wishlist-member/core/api-helper/wlmapiclass.php');
//require_once('../wordpress/wp-content/plugins/wishlist-member/core/api-helper/wlmapiclass.php');

//Initialize the API
$api = new wlmapiclass('http://www.cruisecontroldiet.com/', '9a68fe5e41c683dbdddb66b662ff7065');
$api->return_format = 'php'; // <- value can also be xml or json

// Find the user by their Email
$user = get_user_by('email', $contactEmail);
if(!$user){
    CCDLogger::logWarning(__FILE__.' failed to locate a WP user with the email:  '.$contactEmail.' for txn id: '.$txn_id."\nThis means that the 1st sale either did not go through at the time of this IPN (upsale IPN coming in before sale IPN? No 1st sale?) or the WP account creation silently failed in the sale IPN.\nACTIONS REQUIRED:\n1) reate a WP user and elevate his permissions");
    return;
    //CCDIPN::failIPN(__FILE__.' failed to locate a WP user with the email:  '.$contactEmail.' for txn id: '.$txn_id);
}

//Compose the WP API query
$member_id_array = array ($user->ID);
$data = array ('Users' => array($user->ID));

//Assign the user to the correct group according to the item number
switch ($item_number) {
    // AWLP
    case '8':
    case '15':
        $response = $api->post('/levels/1352060139/members', $data);
        $response = $api->post('/levels/1352060183/members', $data);
        break;
    // Plus Package
    case '6':
    case '17':
	case '56':
	case '94':
        $response = $api->post('/levels/1352060139/members', $data);
        break;
        // Boot Camp
    case '24':
	case '54':
	case '78':
        // Add the customer to the Boot Camp
        $response = $api->post('/levels/1387155078/members', $data);
        // Remove customer from the Core Program so their member-dashboard page looks correct
        $response = $api->delete('/levels/1365084119/members/'.$user->ID);
        break;
    // Functional Fitness
    case '29':
    case '58':
	case '96':
        // Add the user to the membership level, Functional Fitness
        $response = $api->post('/levels/1392951632/members', $data);
        break;
    // Carb Defender, Crave Erase
    case '120':
    case '122':
    case '130':
    case '136':
    case '138':
    	// Do nothing
    	break;
    default:
        CCDLogger::logWarning(__FILE__.' received an unsupported item number ('.$item_number.') and therefore has NOT assigned any priviledges to the contact id: '.$contactId.' for txn id: '.$txn_id);
        break;

}

//DONE

//@TODO-GN: check that payment_amount/payment_currency are correct

*/

?>
