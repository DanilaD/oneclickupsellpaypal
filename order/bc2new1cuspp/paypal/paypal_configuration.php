<?php

/* 
 * * * * * * * * * * * * * * *
 * author: Danila Dolmatov
 * e-mail: danila@autosport.by
 * data: 01/12/2018
 * * * * * * * * * * * * * * *
 * 
 */


/*
 * PAYPAL API
 */

/*
 * 'false' - for life
 * 'true'  - for sandbox
 * 
 * IMPORTANT!
 * if turn on TRUE we must change PRODUCT_ID in files products.php and pp_ipnwlm.php
 * because database in life and sandbox is different  
 * 
 */

DEFINE('SANDBOX', false); 

/* 
 * INFORMATION FOR AUTORITHASION WINDOW IN PAYPAL
 */
DEFINE('BRANDNAME', 'www.healthplus50.com'); 
DEFINE('NOTETOBUYER', ''); //!!! INFORMATION FROM US - NOTE TO BUYER !!!
// description of billing agreement 
DEFINE('BILLINGAGREEMENTDESCRIPTION', '');
// LOGOIMG FOR AUTORITHASION WINDOW IN PAYPAL
DEFINE('LOGOIMG', ''); 


// SANDBOX
DEFINE('SANDBOX_PAYPAL_CLIENTID', 'Ac4M4krmvHXFAG0EeXgKFD1KCu3kR4ZPIgz2sStRQx3oWrTWQXmkdF6arVabFfAh4ranuilLsFibIJUr');
DEFINE('SANDBOX_PAYPAL_SECRET', 'EIuVkEoufmen7lRp0U2zH0rVXWpdSv8m55J9THFK6aXggHR90ysJAVp4Te-yoEswpff6zuZj-8iyZNQF');
DEFINE('SANDBOX_PAYPAL_USER', 'danila-facilitator_api1.autosport.by');
DEFINE('SANDBOX_PAYPAL_PWD', 'E49JK6Q4C3HUUHYP');
DEFINE('SANDBOX_PAYPAL_SIGNATURE', 'ACeaBOZNZMrv6xME.0sql2hoocllABhW.mOgma-Wz-izrYXJmaXazst6');
DEFINE('SANDBOX_PAYPAL_IPN_URL', 'http://www.healthplus50.com/order/bc2new1cuspp/postparser/process_paypal.php');
DEFINE('SANDBOX_PAYPAL_URL_API', 'https://api-3t.sandbox.paypal.com/nvp');
DEFINE('SANDBOX_PAYPAL_REDIRECT_TO_PAYPAL', 'https://sandbox.paypal.com/cgi-bin/webscr?%s');       

// LIFE
DEFINE('LIFE_PAYPAL_CLIENTID', '');
DEFINE('LIFE_PAYPAL_SECRET', '');
DEFINE('LIFE_PAYPAL_USER', 'paypal_api1.fisicoinc.com');
DEFINE('LIFE_PAYPAL_PWD', 'NZLMH7DD4WYW9976');
DEFINE('LIFE_PAYPAL_SIGNATURE', 'AvJz7RQuCV1zYC95UW.Bdm1sUY3xAwl5FzWWbNaza9-LdKzbnOMdosGB');
DEFINE('LIFE_PAYPAL_IPN_URL', 'http://www.healthplus50.com/order/bc2new1cuspp/postparser/process_paypal.php');
DEFINE('LIFE_PAYPAL_URL_API', 'https://api-3t.paypal.com/nvp');
DEFINE('LIFE_PAYPAL_REDIRECT_TO_PAYPAL', 'https://www.paypal.com/cgi-bin/webscr?%s');


/* 
 * ERROR PAGE FOR USER (link on page) 
 */
DEFINE('ERRORPAGE', 'https://www.healthplus50.com/specialoffers/bc2new1cuspp/1cuserror.php');

/* 
 * SENDING MAIL WITH ERROR FROM PAYPAL 
 */
DEFINE('SENDMESSAGE', 'danila@autosport.by'); //nonickch@gmail.com,errorlogs@cruisecontroldiet.com

/*
 * FIRST PURCHASE
 * redirect from PayPal to complete action
 */
DEFINE('FIRST_PURCHASE','https://www.healthplus50.com/order/bc2new1cuspp/paypal/purchase.php');
// redirect to page, if user cansel in PayPal
DEFINE('CANCEL_URL', 'https://www.healthplus50.com/order/bc2new1cuspp/');


/* 
 * LOG FOR PAYPAL 
 */
DEFINE('LOGPAYPAL', 'error_paypal'); // log_paypal.txt

/*
 * necessary file with information about all products
 * type: array
 */
DEFINE('LIFE_PRODUCT_FILE', 'products.php');
DEFINE('SANDBOX_PRODUCT_FILE', 'products_sandbox.php');
