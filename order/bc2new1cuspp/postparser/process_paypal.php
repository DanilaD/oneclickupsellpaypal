<?php
/*
 * IPN script based on https://gist.githubusercontent.com/xcommerce-gists/3440401/raw/1b01453cc3e3aa091f2d60cb95a1e6e36d094309/completeListener.php
 * Version: 0.6
 * Changelog:
 * 		v0.1 (Apr 24 2014): First version
 * 		v0.2 (Apr 29 2014):
 * 			* Added tx_id in all errors/warnings
 * 			* Dropped the 'found duplicate transaction id' from warnings
 * 		v0.3 (May 30 2014)
 * 			* No warning messages for payment_status = [Refunded|Pending|Reversed]
 * 			* Succeeding the IPN with no warning/errors for txn_type=new_case and case_type=[dispute|complaint]
 * 		v0.4 (July 3 2014)
 * 			* Adding the txn_type=new_case case_type='chargeback' to the success/ignore list.
 * 		v0.5 (Apr 14 2015)
 * 			* Adding the txn_type=new_case case_type='Canceled_Reversal' to the success/ignore list.
 * 		v0.6 (May 13 2015)
 * 			* The paypal address will no longer overwrite the order shipping address
 * 		v0.7 (July 14 2017)
 * 			* Adding support for item_numer1, option_selection1_1, option_selection2_1
 *		v0.7.1 (August 21 2017):
 * 			* Ignoring txn_type=adjustment cases
 *      v0.7.2 (January 15 2017):
 *          * For new logic PayPal add two new checking to option_selection1 - custom and option_selection2 - invoice
 * 
 */
/*
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode ('=', $keyval);
    if (count($keyval) == 2)
    $aaa[$keyval[0]] = urldecode($keyval[1]);
    
    $_POST[$keyval[0]] = urldecode($keyval[1]); 
}

*/

$_POST = $_REQUEST;
/************************
 * 
 * for testing log IPN write:
 * $save_to_file_log = TRUE;
 */
//$save_to_file_log = TRUE;
// **********************
  
/*
 * PRODUCT CONFIGURATION
 */
//error_log(json_encode($_REQUEST));
//Require our configuration script and common functions
require_once '../configuration.php';
require_once 'common_functions.php';

//Define an array of infusion_xid (the array key) to ifs tag_id to assign to the user (the default is 255)
$tag_id = 255;
$tag_overrides = array(
	'1add6574b7c07bdf3942c2302dbde6e2' => 245,
	'007d33890a9cf63c905c92da438f44bf' => 398,
	'0c5001b6550046cb8d71837cda10f254' => 514,
	'112e70cc6e82f7b15d9026a5a9f6c9cc' => 514,
	'This is another sample xid' => 'this is another tag id (minus the singlequotes and remember to comma-separate new lines like we do above)'
);


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

//The option_selection1 sometimes comes as option_selection1_1, so check for that
$infusion_id = isset($_POST['option_selection1']) ? (int)$_POST[ 'option_selection1' ] : 0;
if(!$infusion_id){
	$infusion_id = isset($_POST['option_selection1_1']) ? (int)$_POST[ 'option_selection1_1' ] : 0;
}
/*
 * IMORTANT
 * 01/14/2018
 * in PayPal changed variables and we must add new check for new logic 
 * 
 * START:
 */
if(!$infusion_id){
	$infusion_id = isset($_POST['custom']) ? (int)$_POST[ 'custom' ] : 0;
}
// END

//The option_selection1 sometimes comes as option_selection1_1, so check for that
$invoice_id = isset($_POST['option_selection2']) ? (int)$_POST[ 'option_selection2' ] : 0;
if(!$invoice_id){
	$invoice_id = isset($_POST['option_selection2_1']) ? (int)$_POST[ 'option_selection2_1' ] : 0;
}
/*
 * IMORTANT
 * 01/14/2018
 * in PayPal changed variables and we must add new check for new logic 
 * 
 * START:
 */
if(!$invoice_id){
	$invoice_id = isset($_POST['invoice']) ? (int)$_POST[ 'invoice' ] : 0;
}
// END

$Address2Street1 = isset($_POST['address_street']) && !empty($_POST['address_street']) ? (string)$_POST['address_street'] : '';
$City2 = isset($_POST['address_city']) && !empty($_POST['address_city']) ? (string)$_POST['address_city'] : '';
$State2 = isset($_POST['address_state']) && !empty($_POST['address_state']) ? (string)$_POST['address_state'] : '';
$PostalCode2 = isset($_POST['address_zip']) && !empty($_POST['address_zip']) ? (string)$_POST['address_zip'] : '';
$Country2 = isset($_POST['address_country']) && !empty($_POST['address_country']) ? (string)$_POST['address_country'] : '';

//Check to see if there's a tag-id override for this product xid (passed to us as the item_number due to the way index.php redirects to paypal)
if(isset($tag_overrides[$item_number])){
	$tag_id = $tag_overrides[$item_number];
}

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
if(!$item_number || !$payment_status || !$payment_amount || !$payment_currency || !$txn_id || !$receiver_email || !$payer_email || !$infusion_id || !$invoice_id){
    CCDIPN::failIPN(__FILE__.' received incorrect/missing IPN post data: '.http_build_query($_POST) . 'check:' .
        '$item_number:' . $item_number . '+|+$payment_status:' . $payment_status . '+|+$payment_amount:' . $payment_amount . '+|+$payment_currency:' . $payment_currency . '+|+$txn_id:' . $txn_id . '+|+$receiver_email:' . $receiver_email . '+|+$payer_email:' . $payer_email  . '+|+$infusion_id:' . $infusion_id  . '+|+$invoice_id:' . $invoice_id
        );
}

//Initialize the IFS iSDK
require_once 'isdk.php';
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

//Verify the payment_status
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
        return;   //We silently accept 'Cancelled/Reversed' payments
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
        return;
    }
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an IFS API error while checking for pre-existing txn-id: '.$e->getMessage().' for txn id: '.$txn_id);
}

/* Dropped as of v0.6
//Fetch the order id so we can update the shipment fields
$orderId = false;
try{
    $result = $app->dsQuery('Invoice', 1, 0, array('Id'=>(int)$invoice_id), array('JobId'));
    if(!isset($result[0]) || !isset($result[0]['JobId'])){
        CCDIPN::failIPN(__FILE__.' could not find order id for invoice id:'.$invoice_id.' (txn id: '.$txn_id.')');
    }
    $orderId = $result[0]['JobId'];
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an error while trying to fetch the order id for txn id '.$txn_id.': '.$e->getMessage());
}
*/


//Make the manual payment the Infusionsoft Data
try{
    $oDate = $app->infuDate(date("d-m-Y"));
    $result = $app->manualPmt((int)$invoice_id, (float)$payment_amount, $oDate, "PayPal", "Processed via API for paypal transaction id: ".$txn_id, FALSE);
}catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an error while trying to mark an invoice(id: '.$invoice_id.') paid:  '.$e->getMessage().' for txn id: '.$txn_id);
}

//Compose the contact update array
$contact = array('_paypalemail'=>$payer_email);

/* Dropped as of v0.6
$orderShippingInfo = array();
if($Address2Street1){
    $contact['Address2Street1'] = $Address2Street1;
    $orderShippingInfo['ShipStreet1'] = $Address2Street1;
}
if($City2){
    $contact['City2'] = $City2;
    $orderShippingInfo['ShipCity'] = $City2;
}
if($State2){
    $contact['State2'] = $State2;
    $orderShippingInfo['ShipState'] = $State2;
}
if($PostalCode2){
    $contact['PostalCode2'] = $PostalCode2;
    $orderShippingInfo['ShipZip'] = $PostalCode2;
}
if($Country2){
    $contact['Country2'] = $Country2;
    $orderShippingInfo['ShipCountry'] = $Country2;
}

//Update the order
try{
    $app->dsUpdate('Job', (int)$orderId, $orderShippingInfo);
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an error while trying to set shipping info for order with id '.$infusion_id.':  '.$e->getMessage().'(txn id: '.$txn_id.')');
}
*/

//Update the contact
try{
    $app->updateCon($infusion_id, $contact);
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an error while trying to update contact with id '.$infusion_id.':  '.$e->getMessage().'(txn id: '.$txn_id.')');
}

//Tag the contact, and if it fails, just log a warning
try{
    $app->grpAssign( $infusion_id, $tag_id );
}catch(Exception $e){
    CCDLogger::logWarning(__FILE__.' failed to assign tag id '.$tag_id.' to contact id  '.$infusion_id.': '.$e->getMessage().'(txn id: '.$txn_id.')');
}

//DONE