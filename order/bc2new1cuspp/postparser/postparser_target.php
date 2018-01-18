<?php

require_once 'postparser_lp.php';
require_once 'classes/fieldset.php';
require_once 'classes/messagetranslator.php';

//@TODO: Add the shipping fields to the order and the contact

class CCDPostParserTarget extends CCDPostParserLP{
    private static $contactFields = null;
    private static $billingFields = null;
    private static $shippingFields = null;
    private static $paymentFields = null;

    public static function redirectBackWithMessage($message, $translateError = false){
        //Keep a copy so we can log the pre and post-translation messages
        $original_message= $message;
        
        //See if we need to attempt a message translation. Only specific places in the code request a translation (like sources of messages from CC validation/charges)
        if($translateError){
            $message = CCDPostParserMessageTranslator::translate($message);
        }
        
        //First, log the message as a notice
        CCDLogger::logNotice('Redirecting back to '.(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'N/A'). ' with message: '.$message."\n<br/>\n<br/>Untranslated message: ".$original_message."\n<br/>\n<br/>".'$_SERVER vars: '.print_r($_SERVER,true));
        
        //Append the error message to the session.
        //@TODO: THIS IS MIGRATION CODE AS OF v1.0.1. WHEN DORU CONFIRMS THE NON-USE OF THE GET PARAMS, THEN REMOVE THE GET PARAM FROM THE CODE
        if(CCDPostParserLP::openSession()){
            $_SESSION['postparserData']['errorMessage'] = array();
            $_SESSION['postparserData']['errorMessage']['timestamp'] = time();
            $_SESSION['postparserData']['errorMessage']['message'] = $message;
        }

        //See if there's a referer value
        if(empty($_SERVER['HTTP_REFERER'])){
            CCDIPN::failIPN($message.' Also, could not redirect the user back due to a missing referrer', false);
        }

        $encoded_message = base64_encode($message);

        //Parser the referer into a url that we can augment
        if(!($referer_array = parse_url($_SERVER['HTTP_REFERER']))){
            CCDIPN::failIPN($message.' Also, could not redirect the user back due to a malformed referrer');
        }

        //Add our encoded message to the query bit
        $query_array = array();
        if(!empty($referer_array['query'])){
            parse_str($referer_array['query'], $query_array);
        }
        $query_array['errorMsg'] = $encoded_message;
        $referer_array['query'] = http_build_query($query_array); //implode('&', $query_array);    //We implode instead of http_build_query since the encoding rains on our base64 parade


        if(empty($referer_array['host'])){
            CCDIPN::failIPN($message.' Also, could not redirect the user back due to a malformed referrer: '.$_SERVER['HTTP_REFERER']);
        }

        //Build the url again and redirect the browser
        $url = !empty($referer_array['scheme']) ? $referer_array['scheme'].'://' : 'https://';    //Default the scheme to https
        $url .=  $referer_array['host'];
        if(!empty($referer_array['path'])){
            $url .= $referer_array['path'];
        }
        //query is here as we just added to it
        $url .= '?'.$referer_array['query'];
        if(!empty($referer_array['fragment'])){
            $url .= '#'.$referer_array['fragment'];
        }

        //Done, redirect and exit
        header('location: '.$url);
        exit;
    }

    public static function getContactFields(){
        //Check the cache
        if(self::$contactFields !== null){return self::$contactFields;}

        //Fetch the contact fields, cache and store in session
        self::$contactFields = new CCDPostParserContactFields();
        //self::storeFieldValuesInSession('oldContactFields', self::$contactFields);
        
        //Done
        return self::$contactFields;
    }

    public static function getBillingFields(){
        //Check the cache
        if(self::$billingFields !== null){return self::$billingFields;}

        //Fetch the billing fields, cache and store in session
        self::$billingFields = new CCDPostParserBillingFields();
        //self::storeFieldValuesInSession('oldBillingFields', self::$billingFields);
        
        //Done
        return self::$billingFields;
    }

    public static function getShippingFields(){
        //Check the cache
        if(self::$shippingFields !== null){return self::$shippingFields;}

        //Fetch the shipping fields, cache and store in session
        self::$shippingFields = new CCDPostParserShippingFields();
        //self::storeFieldValuesInSession('oldShippingFields', self::$shippingFields);
        
        //Done
        return self::$shippingFields;
    }

    public static function getPaymentFields(){
        //Check the cache
        if(self::$paymentFields !== null){return self::$paymentFields;}

        self::$paymentFields = new CCDPostParserPaymentFields();
        return self::$paymentFields;
    }
    
    /**
     * //@TODO: Remove this with the first opportunity. Especially if later than v1.1. This code was never used
     * 
     * Stores the given 
     * @param String $entryName
     * @param CCDPostParserFieldSet $fieldset
     
    protected static function storeFieldValuesInSession($entryName, CCDPostParserFieldSet $fieldset){
        if(CCDPostParserLP::openSession()){
            $_SESSION['postparserData'][$entryName] = $fieldset->getFieldValuesAsArray();
        }
    }
    */

    /**
     * Fetches the prices for the current product being sold
     * @return stdClass			As per the CCDPostParserLPConfig::calculatePrices()
     */
    public static function getPrices(){
        $billingFields = self::getBillingFields();
        $shippingFields = self::getShippingFields();

        //Figure out if we need to calculate tax based on shipping or billing fields
        $taxCountryField = $billingFields->getCountryField();
        $taxStateField = $billingFields->getStateField();    //Notice: This could be null
        if($shippingFields->getCountryField()){
            $taxCountryField = $shippingFields->getCountryField();
            $taxStateField = $shippingFields->getStateField();
        }

        //Return the calculated prices from the config
        $state = $taxStateField ? $taxStateField->value : '';
        $config = self::getPostParserConfig();
        return $config->calculatePrices('', $state, $taxCountryField->value);
    }


}