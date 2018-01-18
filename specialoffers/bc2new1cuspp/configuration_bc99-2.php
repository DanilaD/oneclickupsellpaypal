<?php
/*
 * CONFIGURATION OPTIONS
 */

define('CCD_CONF_DEBUG', false);    //Set to true when testing. WARNING: enabling this may break the user experience and/or IPN with debugging messages
define('CCD_CONF_SANDBOX', false);    //Whether to use the normal paypal/IFS accounts or their respective sandbox (each defined separately below)

//PRODUCT PRICE FIELDS
define('CCD_DUPLICATE_CHECK_IFS_PRODUCT_ID', 112);    //The Infusionsoft product id being sold. Test live product: 48
define('CCD_DUPLICATE_CHECK_DAYS_TO_CHECK_IN_THE_PAST',90);

/*
 * PRIMARY PAYPAL CONFIGURATION
 */
define('CCD_CONF_PAYPAL_ACCOUNT_EMAIL','paypal@fisicoinc.com');
define('CCD_CONF_PAYPAL_SANDBOX_ACCOUNT_EMAIL','developer-facilitator@fisicoinc.com');

/*
 * PRIMARY INFUSIONSOFT CONFIGURATION
 * Notice that we do NOT need the conn.cfg.php file any more, provided that the iSDK initialization is properly done
 */
define('CCD_CONF_IFS_APPNAME','yc147');
define('CCD_CONF_IFS_API_KEY','83b397e3f92e91283a5dd1372adc0327');
define('CCD_CONF_IFS_SANDBOX_APPNAME','cf171');
define('CCD_CONF_IFS_SANDBOX_API_KEY','7638482ea55d0aa320b45531cdd7cb67');

/*
 * ERROR REPORTING CONFIGURATION
 */
//Select whether you want to email the errors&notices (they will be logged anyway) 
define('CCD_CONF_EMAIL_ERRORS', true);
define('CCD_CONF_EMAIL_WARNINGS', true);
define('CCD_CONF_EMAIL_RECIPIENTS', 'nonickch@gmail.com,errorlogs@cruisecontroldiet.com,gk@fisicoinc.com');    //The comma-separated recipient of any error/warning emails
define('CCD_CONF_EMAIL_SENDER','API@cruisecontroldiet.com');  //The email sender
define('CCD_CONF_LOG_WARNINGS', true);                        //Select whether you want to log warnings. Errors are always logged
define('CCD_CONF_LOG_LOCATION',false);                        //The file location of the custom error/warning logfile or false if the default PHP error_log is to be used
define('CCD_CONF_ERROR_EMAIL_SUBJECT','An error has occured');    //The subject of the error emails
define('CCD_CONF_WARNING_EMAIL_SUBJECT','A warning has occured');    //The subject of the warning emails

/*
 * SECONDARY INFUSIONSOFT CONFIGURATION (unlikely to change)
 */
//What to do when an API error is encountered. 
//Possible values: on,off,kill,throw. Current setting throw an exception 
//WARNING: You MUST catch the exceptions when using 'throw' or the script will die on an API error (with a full erro_log entry though)
DEFINE('CCD_CONF_IFS_API_MODE','throw');
DEFINE('CCD_CONF_IFS_SANDBOX_API_MODE','throw');
