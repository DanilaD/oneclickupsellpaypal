<?php

/*
 * 
 * Postparser: The form target for the new order form system.
 * 
 * Handles all aspects of the FIRST sale:
 * 		* Validates all fields/data according to the configuration
 * 		* Creates/updates the contact
 * 		* Creates the order
 * 		* (Optionally, if CC selected) Charges the given Credit Card through infusionsoft
 * 		* (Optionally, if PP selected) redirects to PayPal
 * 		* Assigns the related tags (succesful/failed charge) to the contacts
 * 
 * Please notice that the PayPal IPN script is required for the order to be completed through PayPal
 * 
 * 
 * CHANGELOG:
 * 	v1.0: April 22 2016: First version
 *  v1.1: June  02 2016: 
 *                  * Adding Sessions
 *                  * Adding php-based field oldval memory
 *                  * Adding error message translations for the CC charge attempts 
 *                  * The submission errors are now forwarded back to the LP code via an internal mechanism (not the GET params)
 *  v1.2: July 11 2016:
 *  				* Fixing bad field name from 'LeadSource' to 'Leadsource' in postparser_lp
 *  				* Avoiding overwrites/deletions of the contact.Leadsource field
 *  v1.2.1: July 13 2016:
 *  				* Adding TEMPORARY support for the _TrackingID order customfield
 *  v1.2.2: Aug 26 2016:
 *                  * Adding the $_SESSION['postparserdata']['payment_type'] session var for usage from the upsell pages
 *  v1.3.0: Sep 16 2016:
 *                  * Adding the untranslated message and $_SERVER var data to the redirect-back email messages 
 *  v1.3.1: Oct 24 2016:
 *                  * Moving the IPN location to the configuration file (was hardcoded)
 *  v1.3.2: Dec 19 2016:
 *                  * Now appending the error tag when the CC charge fails (and is reported as such by the API),
 *                      and not when the API for the charge fails
 *  v1.3.3: March 9 2016:
 *                  * The thank-you URLs now have the 'contactId' parameter appended first in their GET parameters
 *                      as a band-aid fix to the paypal 'return-to urls sometime loose all but their first GET params'
 *  v1.3.4: March 21 2016:
 *  				* The thank-you ULs now have the ProductPrice parameter that is the total price paid (tax/shipping included)
 *  				* The redirectBackWithMessage() function now logs a notice instead of a warning (no email)
 *  				* Merged the most up-to-date translation file used on live
 *  v1.4:   August 21 2017:
 *  				* Removed the f/l-name and email fields from the thankyou urls as per the Google requirements
 *  				* The missing required fields email is no longer being emitted
 */


//@TODO: It might be a good idea to accept the productId and totals as hidden params and see if we disagree
//with the locally generated values. This way we can centrally host this file and double-check everything with a single mechanism.
//People COULD end up posting to the wrong url which would make people buy random things


//Required libraries
require_once 'postparser_target.php';
require_once '../configuration.php';
require_once 'common_functions.php';

//IFS iSDK
require('isdk.php');

//@TODO: Are we sure about invoice/order id's?

//Load the post parser config
$config = null;
try{
    $contactFields = CCDPostParserTarget::getContactFields();
    $billingFields = CCDPostParserTarget::getBillingFields();
    $shippingFields = CCDPostParserTarget::getShippingFields();
    $paymentFields = CCDPostParserTarget::getPaymentFields();
    $config = CCDPostParserTarget::getPostParserConfig();
}catch(CCDPostParserFormFieldException $e){
    CCDPostParserTarget::redirectBackWithMessage($e->getMessage());
}catch(Exception $e){
    CCDIPN::failIPN($e->getMessage());
}

//Instantiate the iSDK
$app = new iSDK;
$app_name = (defined('CCD_CONF_SANDBOX') && CCD_CONF_SANDBOX) ? CCD_CONF_IFS_SANDBOX_APPNAME : CCD_CONF_IFS_APPNAME;
$api_key = (defined('CCD_CONF_SANDBOX') && CCD_CONF_SANDBOX) ? CCD_CONF_IFS_SANDBOX_API_KEY : CCD_CONF_IFS_API_KEY;
try{
    $app->cfgCon($app_name, $api_key, 'throw');
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an error while initializing the iSDK: '.$e->getMessage());
}

//1. Add/update the contact
$contact_id = null;
try{
    $contactArray = array_merge($contactFields->getFieldValuesAsArray(), $billingFields->getFieldValuesAsArray(), $shippingFields->getFieldValuesAsArray());
    
    //1.1: Never overwrite the leadsource, if there's one present
    if(isset($contactArray['Leadsource'])){
    	$pre_existing_contacts = $app->dsQuery('Contact', 1, 0, array('Email'=>$contactArray['Email']), array('Id','Leadsource'));
    	if(!empty($pre_existing_contacts) && !empty($pre_existing_contacts[0]['Leadsource'])){
    		unset($contactArray['Leadsource']);
    	}
    }
    
    $contact_id = $app->addWithDupCheck( $contactArray, 'Email' );
} catch (Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an error while adding a contact: '.$e->getMessage());
}

//2. Opt-in the contact
try{
    $app->optIn($contactArray['Email'],'API Opt-In');
} catch(Exception $e){
    //Nothing to do. Failed opt-ins are semi-expected
}

//3. Add a Creditcard, if payment is NOT paypal
$creditcard_id = null;
if(!$paymentFields->isPaypal()){
    try{
        //Compose the CC fields so it gets stored directly to the contactid
        $ccFields = $paymentFields->getFieldValuesAsArray();
        $ccFields['ContactId'] = $contact_id;

        //Validate that the CreditCard looks OK-ish
        $validationResult = $app->validateCard($ccFields);
        if(empty($validationResult) || !isset($validationResult['Valid'])){
            CCDIPN::failIPN(__FILE__.' encountered an unknown error while verifying a CreditCard. The response as:  '. json_encode($validationResult));
        }
        if($validationResult['Valid'] == 'false'){
            throw new CCDPostParserFormFieldException($validationResult['Message'], 1);
        }

        //Add the CreditCard to the contact
        $creditcardArray = array_merge($contactFields->getCreditcardTableValues(), $billingFields->getCreditcardTableValues(), $paymentFields->getCreditcardTableValues() );
        $creditcardArray['ContactId'] = $contact_id;
        $creditcard_id = $app->dsAdd('CreditCard', $creditcardArray);

    }catch(CCDPostParserFormFieldException $e){
        CCDPostParserTarget::redirectBackWithMessage($e->getMessage(), true);
    }catch(Exception $e){
        CCDIPN::failIPN(__FILE__.' encountered an error while adding a credit card: '.$e->getMessage());
    }
}

//4. Extrapolate the prices
$prices = null;
try{
    $prices = CCDPostParserTarget::getPrices();
}catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an error while calculating the prices: '.$e->getMessage());
}

//5. Create a blank order
$oDate = $app->infuDate(date("d-m-Y"));
$invoice_id = null;
try{
    //@TODO: When testing order/invoice ids. This is 1st point
    $invoice_id = $app->blankOrder($contact_id, $config->getProductName().': API LP sale' , $oDate, 0, 0);
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' failed to create invoice for contact id:'.$contact_id. ': '.$e->getMessage());
}

//6. Fetch the Job Id from the Invoice id
$order_id = null;
try{
    $job_result = $app->dsLoad('Invoice', (int)$invoice_id, array('JobId'));
    if(empty($job_result['JobId'])){
        throw new Exception('No Job found for invoice '.$invoice_id, 404);
    }
    $order_id = (int)($job_result['JobId']);
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' failed to update the shipping fields for invoice id:'.$invoice_id. ': '.$e->getMessage());
}


//7. Append the Job Custo Fields (if any)
//@TODO: This is TEMPORARY. We need a new set of order/job field configs likewe have with contact
//Re-fetch the contact fields as we do unset one of them (leadsource) in the
$tmp_cfields = $contactFields->getFieldValuesAsArray();
if(!empty($tmp_cfields['Leadsource'])){
	try{
		$app->dsUpdate('Job', $order_id, array('_TrackingID' => $tmp_cfields['Leadsource']));
	}catch(Exception $e){
		CCDLogger::logWarning('Error while setting the '. CCD_POSTPARSER_ORDER_LEADSOURCE_FIELD. ' custom field for order id '.$order_id.' (invoice: '.$invoice_id.'). Reason: '.$e->getMessage());
	}
}


//7. Add the product order item
try{
    //@TODO: When testing order/invoice ids. This is 2nd point
    $app->addOrderItem($invoice_id, $config->getProductId(), 4, $prices->price, 1, $config->getProductName(), 'Product added from the API-based LP parser on '.date("F j, Y, g:i a"));
}catch (Exception $e){
    CCDIPN::failIPN(__FILE__.' failed to add orderitem '.$config->getProductName().' ('.$config->getProductId().') for contact id:'.$contact_id. ' and invoice id: '.$invoice_id.':'.$e->getMessage());
}

//8. Add shipping (if any)
if($prices->shipping){
    try{
        //@TODO: When testing order/invoice ids. This is 3rd point
        $app->addOrderItem($invoice_id, 0, 1, $prices->shipping, 1, 'Shipping', 'Shipping added from the API-based LP parser for the product '.$config->getProductName());
    }catch (Exception $e){
        CCDIPN::failIPN(__FILE__.' failed to add shipping for '.$config->getProductName().' ('.$config->getProductId().') for contact id:'.$contact_id. ' and invoice id: '.$invoice_id.':'.$e->getMessage());
    }
}

//9. Add tax (if any)
if($prices->tax){
    try{
        //@TODO: When testing order/invoice ids. This is 4th point
        $app->addOrderItem($invoice_id, 0, 2, $prices->tax, 1, 'Tax', 'Tax added from the API-based LP parser for the product '.$config->getProductName());
    }catch (Exception $e){
        CCDIPN::failIPN(__FILE__.' failed to tax shipping for '.$config->getProductName().' ('.$config->getProductId().') for contact id:'.$contact_id. ' and invoice id: '.$invoice_id.':'.$e->getMessage());
    }
}

//10. Either process the payment with a creditcard or forward the user to PayPal
//@TODO: When testing order/invoice ids. This is 5th point (2 places here, also the destination page reads orderId=invoce_id)
if($paymentFields->isPayPal()){
    //Build the paypal params
    //@TODO: Do we need these return URL get params?
    $params = array(
	'on0' 			=> 'id',
	'os0' 			=> $contact_id,
	'on1' 			=> 'id2',
	'os1' 			=> $invoice_id,
	'business' 		=> ((defined('CCD_CONF_SANDBOX') && CCD_CONF_SANDBOX) ? CCD_CONF_PAYPAL_SANDBOX_ACCOUNT_EMAIL : CCD_CONF_PAYPAL_ACCOUNT_EMAIL),
	'cmd' 			=> '_xclick',
	'currency_code' => 'USD',
	'amount' 		=> $prices->price,
    'tax'			=> $prices->tax,
    'shipping'		=> $prices->shipping,
	'item_name' 	=> $config->getProductName(),
	'item_number' 	=> $config->getProductId(),
	'notify_url'	=> ((defined('CCD_CONF_SANDBOX') && CCD_CONF_SANDBOX) ? CCD_CONF_PAYPAL_SANDBOX_IPN_URL : CCD_CONF_PAYPAL_IPN_URL),
	'return'		=> $config->getThankyouUrl($contact_id, $invoice_id, $contactArray, $prices) . '&paypal=yes'
    );
    
    /* New logic for PayPal
     * include class for PayPal
     * get token and then redirect to PayPal for LogIn and Billing Agreement
     * START:
     */
      require '../paypal/paypal_class.php'; 
      $paypal_new = NEW paypal();
      $paypal_new->GetToken($params);						
      exit;
    /* I don't change old logic, 
     * that's why, if necessary simple delete this block (form START to END) and old logic will start to work 
     * END.
     */

    $req = '';
    if(function_exists('get_magic_quotes_gpc')) {
        $get_magic_quotes_exists = true;
    }
    foreach ($params as $key => $value) {
        if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
            $value = urlencode(stripslashes($value));
        } else {
            $value = urlencode($value);
        }
        $req .= "&$key=$value";
    }

    //Forward to the paypal order page and exit
    $location = (defined('CCD_CONF_SANDBOX') && CCD_CONF_SANDBOX) ? 'https://www.sandbox.paypal.com' : 'https://www.paypal.com';
    header('location: '.$location.'/cgi-bin/webscr?' . $req);
    exit;    //For paypal payments, this is the end of the script
}else{
    //Run a Creditcard charge
    try{
        $charge_result = $app->chargeInvoice($invoice_id, 'Automatic CC payment from the API-based LP parser', $creditcard_id, $config->getMerchantId(), false);
        if(empty($charge_result['Successful'])){
            $message = !empty($charge_result['Message']) ? $charge_result['Message'] : 'N/A';
            $refnum = !empty($charge_result['RefNum']) ? $charge_result['RefNum'] : 'N/A';
            $code = !empty($charge_result['Code']) ? $charge_result['Code'] : 'N/A';
            CCDLogger::logDebug('Failed to charge CC id '.$creditcard_id.'. Message: '.$message.'. RefNum: '.$refnum.'. Code:'.$code);
            
            //Mark the user as having failed the payment process
            try{
                //Payment failed, try to set the failure tag
                $app->grpAssign($contact_id, $config->getPaymentFailureTagId());
            }catch(Exception $e){
                CCDLogger::logWarning('Failed to add the payment failure tag to contact id '.$contact_id.'. Reason: '.$e->getMessage());
            }
            
            CCDPostParserTarget::redirectBackWithMessage('We could not charge your credit card. '.$code.': '.$message, true);
        }
    }catch(Exception $e){
        CCDIPN::failIPN(__FILE__.' failed to charge invoice (id: '.$invoice_id.') with a creditcard (id: '.$creditcard_id.'): '.$message);
    }
}

//10. Since this is creditcard-only flow, also add the payment-success tag (for paypal, the tag is set from the IPN script)
try{
    $app->grpAssign($contact_id, $config->getPaymentSuccessTagId());
}catch(Exception $e){
    CCDLogger::logWarning('Failed to assign success tag id ('.$config->getPaymentSuccessTagId().') to contact id '.$contact_id);
}

//11. Done. Forward the user to the thank-you page
header('location: '.$config->getThankyouUrl($contact_id, $invoice_id, $contactArray, $prices));
exit;
