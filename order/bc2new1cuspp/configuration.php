<?php
/*
 *
 * CONFIGURATION FILE FOR THE NEW POST PARSER
 *
 */

//@TODO-GN: Do we need to error out? This is a pretty hard-fail case.
//class_exists('CCDPostParserLP') or die('Post parser must be included before the configuration');

/*
 * 
 * REQUIRED FIELDS
 * 
 */

//DEBUGGING OPTIONS
define('CCD_CONF_DEBUG', false);    //Set to true when testing. WARNING: enabling this may break the user experience and/or IPN with debugging messages
define('CCD_CONF_SANDBOX', false);    //Whether to use the normal paypal/IFS accounts or their respective sandbox (each defined separately below)

//PRODUCT PRICE FIELDS
define('CCD_POSTPARSER_IFS_PRODUCT_ID', '4');    //The Infusionsoft product id being sold
define('CCD_POSTPARSER_IFS_PRODUCT_NAME', 'The Cruise Control Diet Core Program (X1)');    //The Infusionsoft product name being sold.
define('CCD_POSTPARSER_IFS_PRODUCT_NAME_UI', 'The Cruise Control Diet Core Program (X1): Physical Package + Instant Digital Access  / 60-Day 100% Money-Back Guarantee.');    //The product name displayed on the LP.
define('CCD_POSTPARSER_IFS_PRICE_USD','39.99');       //The product price
define('CCD_POSTPARSER_SHIPPING_COST_US_CANADA_USD', 9.99);    //The shipping cost (in USD) to US/Canada
define('CCD_POSTPARSER_SHIPPING_COST_OUTSIDE_US_USD', 14.99);    //The shipping cost (in USD) to outside US/Canada
define('CCD_POSTPARSER_TAX_PERCENTAGE', 6.25);         //The sales tax IN PERCENTAGE! 6.25 == 6.25% tax
define('CCD_POSTPARSER_TAX_STATE_SHORTCODE','MA');    //When the country==US and state==MA, then we apply the state tax
define('CCD_POSTPARSER_MERCHANT_ID',6); // LIFE = 6 , FOR SANDBOX = 7   //The IFS Merchant id to use for billing
define('CCD_POSTPARSER_PAYMENT_SUCCESS_TAG',255);    //The tag to attach to contacts when a payment has gone through
define('CCD_POSTPARSER_PAYMENT_FAILURE_TAG',360);    //The tag to attach to contacts when a payment has failed to go through

//UPSELL/DOWNSELL FLOW
define('CCD_POSTPARSER_THANKYOU_URL','https://www.healthplus50.com/specialoffers/bc2new1cuspp/bc99.php');    //Where to redirect the user after a succesfuly sale

//PRIMARY INFUSIONSOFT CONFIGURATION
define('CCD_CONF_IFS_APPNAME','yc147');
define('CCD_CONF_IFS_API_KEY','83b397e3f92e91283a5dd1372adc0327');
define('CCD_CONF_IFS_SANDBOX_APPNAME','oh419');
define('CCD_CONF_IFS_SANDBOX_API_KEY','e7a90fea1cb020cf948794a4f915066a');

//INFUSIONSOFT CONFIGURATION
define('CCD_CONF_PAYPAL_ACCOUNT_EMAIL','paypal@fisicoinc.com');
define('CCD_CONF_PAYPAL_SANDBOX_ACCOUNT_EMAIL','danila-facilitator@autosport.by');
define('CCD_CONF_PAYPAL_IPN_URL','http://www.healthplus50.com/order/bc2new1cuspp/postparser/process_paypal.php');
define('CCD_CONF_PAYPAL_SANDBOX_IPN_URL','http://www.healthplus50.com/order/bc2new1cuspp/postparser/process_paypal.php');

//ERROR REPORTING CONFIGURATION
define('CCD_CONF_EMAIL_ERRORS', false);
define('CCD_CONF_EMAIL_WARNINGS', false);
define('CCD_CONF_EMAIL_RECIPIENTS', 'danila@autosport.by');    //The comma-separated recipient of any error/warning emails
define('CCD_CONF_EMAIL_SENDER','API@cruisecontroldiet.com');  //The email sender
define('CCD_CONF_LOG_WARNINGS', true);                        //Select whether you want to log warnings. Errors are always logged
define('CCD_CONF_LOG_LOCATION',false);                        //The file location of the custom error/warning logfile or false if the default PHP error_log is to be used
define('CCD_CONF_ERROR_EMAIL_SUBJECT','A post parser error has occured');    //The subject of the error emails
define('CCD_CONF_WARNING_EMAIL_SUBJECT','A post parser warning has occured');    //The subject of the warning emails
/*
 * SECONDARY INFUSIONSOFT CONFIGURATION (unlikely to change)
 */
//What to do when an API error is encountered. 
//Possible values: on,off,kill,throw. Current setting throw an exception 
//WARNING: You MUST catch the exceptions when using 'throw' or the script will die on an API error (with a full erro_log entry though)
DEFINE('CCD_CONF_IFS_API_MODE','throw');
DEFINE('CCD_CONF_IFS_SANDBOX_API_MODE','throw');

/*
 * 
 * OPTIONAL FIELDS 
 * 
 */


/*
 * (OPTIONAL) CONTACT FIELDS. 
 * Skip/comment-out this section for the following defaults:
 * [Field Name] : Type : Non/Required
 *  FirstName:String, required
 *  LastName: String, required
 *  Email: Email, required
 *  Phone: String, required
 
CCDPostParserLP::configureContactFields(array(
CCDPostParserFormField('FirstName', 'First Name', CCDPostParserFormField::FIELD_TYPE_STRING, true),
CCDPostParserFormField('LastName', 'Last Name', CCDPostParserFormField::FIELD_TYPE_STRING, true),
CCDPostParserFormField('Email', 'Email', CCDPostParserFormField::FIELD_TYPE_EMAIL, true),
new CCDPostParserFormField('Phone1', 'Phone',CCDPostParserFormField::FIELD_TYPE_STRING, true),
new CCDPostParserFormField('LeadSource', 'LeadSource', CCDPostParserFormField::FIELD_TYPE_HIDDEN, false)
));
*/

/*
 * (OPTIONAL) BILLING FIELDS. 
 * Skip/comment-out this section for the following defaults:
 * {Field Name} : {Label} : {Type} : {Non/Required}
 *  
 *  State1/Country1, as defined in the example array below, are select elements with a default list of entries. 
 *  If you want to override the options, provide a third parameter with the array of options in a ('shot_id'=>'human-radable-text',...) array.
 *  For example: 
 *  new CCDPostParserFormField('State1', CCDPostParserFormField::FIELD_TYPE_STATE, false, array('outside'=>'Non-US state', 'inside'=>'US State (any)') )
 
CCDPostParserLP::configureBillingFields(array(
CCDPostParserFormField('StreetAddress1', 'Address 1', CCDPostParserFormField::FIELD_TYPE_STRING, true),
CCDPostParserFormField('StreetAddress2', 'Address 2', CCDPostParserFormField::FIELD_TYPE_STRING, false),
CCDPostParserFormField('City', 'City',CCDPostParserFormField::FIELD_TYPE_STRING, true),
CCDPostParserFormField('PostalCode', 'Zip', CCDPostParserFormField::FIELD_TYPE_STRING, true),
CCDPostParserFormField('State', 'State',CCDPostParserFormField::FIELD_TYPE_STATE, false),
CCDPostParserFormField('Country', 'Country',CCDPostParserFormField::FIELD_TYPE_COUNTRY, true)
));
*/ 

/*
 * (OPTIONAL) SHIPPING FIELDS. 
 * Skip/comment-out this section for the following defaults:
 * {Field Name} : {Label} : {Type} : {Non/Required} *  Address2StreetAddress1:String, not required
 *  
 *  State2/Country2, as defined in the example array below, are select elements with a default list of entries. 
 *  If you want to override the options, provide a third parameter with the array of options in a ('shot_id'=>'human-radable-text',...) array.
 *  For example: 
 *  new CCDPostParserFormField('State2', CCDPostParserFormField::FIELD_TYPE_STATE, false, array('outside'=>'Non-US state', 'inside'=>'US State (any)') )
 
CCDPostParserLP::configureBillingFields(array(
CCDPostParserFormField('Address2StreetAddress1', 'Address1', CCDPostParserFormField::FIELD_TYPE_STRING, true),
CCDPostParserFormField('Address2StreetAddress2', 'Address2', CCDPostParserFormField::FIELD_TYPE_STRING, false),
CCDPostParserFormField('City2', 'City', CCDPostParserFormField::FIELD_TYPE_STRING, true),
CCDPostParserFormField('PostalCode2', 'Zip', CCDPostParserFormField::FIELD_TYPE_STRING, true),
CCDPostParserFormField('State2', 'State', CCDPostParserFormField::FIELD_TYPE_STATE, false),
CCDPostParserFormField('Country2', 'Country', CCDPostParserFormField::FIELD_TYPE_COUNTRY, true)
));
*/ 
