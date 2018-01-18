Switch on life PayPal and IFS

have to:

EDIT FILE: file /public_html/order/bc2new1cuspp/configuration.php

	1. switch on Life PAyPal and IFS 

		define('CCD_CONF_SANDBOX', FALSE);
		setup FALSE

		
	2. Setup First Product Id from IFS
	
		define('CCD_POSTPARSER_IFS_PRODUCT_ID', '4');

		
	3. Verify the correctness of the data API IFS

		define('CCD_CONF_IFS_APPNAME','yc147');
		define('CCD_CONF_IFS_API_KEY','83b397e3f92e91283a5dd1372adc0327');
		define('CCD_CONF_PAYPAL_ACCOUNT_EMAIL','paypal@fisicoinc.com');

		
	4. Verify that the mail recipients are correct
	
		define('CCD_CONF_EMAIL_RECIPIENTS', 'nonickch@gmail.com,errorlogs@cruisecontroldiet.com'); 

    5. Change merchant id for credit card
        LIFE = 6
        SANDBOX = 7

        define('CCD_POSTPARSER_MERCHANT_ID',6);    

    6. setup url for the thankyou page 
        
          for credit card - old variable in configuration file

          //UPSELL/DOWNSELL FLOW
          define('CCD_POSTPARSER_THANKYOU_URL','http://sandbox.cruisecontroldiet.com/order/new/bc2cdff-pp/bc99.php');    //Where to redirect the user after a succesfuly sale

          for paypal

          in product.php and product_sandbox.php
          return link in the first product

EDIT FILE: file /public_html/order/bc2new1cuspp/configuration.php


	1. switch on Life PAyPal
	
		DEFINE('SANDBOX', FALSE); 
	
	2. enter your credential for life account PayPal
	
			instruction - https://developer.paypal.com/docs/classic/api/apiCredentials/
	
		DEFINE('LIFE_PAYPAL_CLIENTID', '');  // The API ClientID
		DEFINE('LIFE_PAYPAL_SECRET', '');    // The API Secret
		DEFINE('LIFE_PAYPAL_USER', ''); 	 // The API User name credential
		DEFINE('LIFE_PAYPAL_PWD', ''); 		 // The API Password credential
		DEFINE('LIFE_PAYPAL_SIGNATURE', ''); // The Signature credential
