<?php
/*
 *      Configuration class for use from the LP
 */

//@TODO: We should really have replaceable Shipping rulesets here. Ie 'class USCanadaAndOutside extends BaseShipping'

require_once 'common_functions.php';
require_once 'classes/lpconfig.php';
require_once 'classes/formfield.php';
require_once 'classes/exceptions.php';

//Initialize the session
//@TODO: Are we sure we want to always open a session?
CCDPostParserLP::openSession();

class CCDPostParserLP{
    protected static $postParserConfig = null;    /* @var $postParserConfig CCDPostParserLPConfig */

    //Vars used to temporarily hold the external configuration parameters for the form before the initialization
    protected static $externalPostParserContactConfig = null;
    protected static $externalPostParserShippingConfig = null;
    protected static $externalPostParserBillingConfig = null;
    
    protected static $sessionStarted = false;
    

    /**
     * Makes sure that the session is open
     * @return bool    True if we managed to open a properly configured session, false if otherwise
     */
    public static function openSession(){
        //See if we've opened the session before
        if(self::$sessionStarted){return true;}
        
        //Check the session status
        $session_status = session_status();
        
        switch($session_status){
            case PHP_SESSION_DISABLED:
                CCDLogger::logWarning('CCDPostParserLP:getSession() could not open a session because the sessions are disabled. Session disabled');
                return false;
                break;
            case PHP_SESSION_ACTIVE:
                CCDLogger::logWarning('CCDPostParserLP:getSession(): Session is already open. Proceeding with pre-existing session');
                self::$sessionStarted = true;
                return true;
                break;
            case PHP_SESSION_NONE:
                //nothing to do here, all is good
                break;
            default:
                CCDLogger::logWarning('CCDPostParserLP:getSession() encountered an unknown session status: '.$session_status.' Sessions disabled');
                return false;
                break;
        }
        
        //Make sure the headers haven't been sent
        if(headers_sent()){
            CCDLogger::logWarning('CCDPostParserLP:getSession() could not open a session because the headers have been sent. Session disabled');
            return false;
        }
        
        //Open the session
        //@TODO: We could configure session params here
        if(!session_start()){
            CCDLogger::logWarning('CCDPostParserLP:getSession() could not open a session. Sessions disabled');
            return false;
        }
        
        //Initialize the postparser base array element, if empty/not present (fresh session)
        if(empty($_SESSION['postparserData'])){
            $_SESSION['postparserData'] = array();
        }
        
        //Mark the session open and return
        self::$sessionStarted = true;
        return true;
    }
    
    /**
     * Fetches any error messages passed back from the post parser form target
     * @return string   The last error message, or an empty string if none found
     */
    public static function getLastErrorMessage(){
        if(CCDPostParserLP::openSession() && !empty($_SESSION['postparserData']['errorMessage'])){
            $messageArray = $_SESSION['postparserData']['errorMessage'];
            unset($_SESSION['postparserData']['errorMessage']); //Clean the value as it has been now 'displayed'
            
            //See if this error message has been generated in the past 10 minutes
            if(!empty($messageArray['timestamp']) && !empty($messageArray['message']) && $messageArray['timestamp'] > (time()-600) ){
                return $messageArray['message'];
            }
        }
        
        //Done, nothing found
        return '';
    }

    /**
     * Returns the configuration object required to build the LP page
     * @throws CCDPostParserException in case of an error while initializing the configuration
     * @return CCDPostParserLPConfig
     */
    public static function getPostParserConfig(){
        //If the value is not present, initialize
        if (self::$postParserConfig == null){
            self::initializeConfig();
        }

        return self::$postParserConfig;
    }

    public static function getPostParserCalculationJS(){
        $config = self::getPostParserConfig();

        return $config->getCalculatePricesJavascript();
    }
    
    /*
     * //@TODO: The following was never used. Remove if we go past v1.1
    protected static function appendOldFieldValues($sessionArrayName, array $formFields){
        //Make sure the session is open
        if(!CCDPostParserLP::openSession()){return;}
        
        //Go through each form field and check if it has a stored field value
        foreach($formFields AS $formField){
            //Make sure we were given an array for CCDPostParserFormField entries
            if(!is_a($formField, 'CCDPostParserFormField')){
                CCDLogger::logError('CCDPostParserLP::appendOldFieldValues received a non-CCDPostParserFormField field: '.json_encode($formField));
                return;
            }
            
            // @var $formField CCDPostParserFormField
            if(!empty($_SESSION['postparserData'][$sessionArrayName][$formField->name])){
                $formField->oldValue = $_SESSION['postparserData'][$sessionArrayName][$formField->name];
            }
        }
    }
    */

    /**
     * Builds the post parser config and stores it for use by the getPostParserConfig()
     * @throws CCDPostParserException		In case of an error
     * @return NULL
     */
    protected static function initializeConfig(){
        //Simulating an error case by checking the request params. DEV-TESTS ONLY
        if(isset($_REQUEST['simulateError'])){
            throw new CCDPostParserException('This is a sample error message', 1);
        }

        //See if we have the config cached
        if(self::$postParserConfig!== NULL){return self::$postParserConfig;}

        //Initialize the contact fields and see if we have any custom configuration for them
        $contactFields = !empty(self::$externalPostParserContactConfig) ? self::$externalPostParserContactConfig : array();
        if(empty($contactFields)){
            $contactFields[] = new CCDPostParserFormField('FirstName', 'First Name', CCDPostParserFormField::FIELD_TYPE_STRING, true);
            $contactFields[] = new CCDPostParserFormField('LastName', 'Last Name', CCDPostParserFormField::FIELD_TYPE_STRING, true);
            $contactFields[] = new CCDPostParserFormField('Email', 'Email', CCDPostParserFormField::FIELD_TYPE_EMAIL, true);
            $contactFields[] = new CCDPostParserFormField('Phone1', 'Phone',CCDPostParserFormField::FIELD_TYPE_STRING, true);
            $contactFields[] = new CCDPostParserFormField('Leadsource', 'Leadsource', CCDPostParserFormField::FIELD_TYPE_HIDDEN, false);
        }

        //Initialize the billing fields and see if we have any custom configuration for them
        $billingFields = !empty(self::$externalPostParserBillingConfig) ? self::$externalPostParserBillingConfig : array();
        if(empty($billingFields)){
            $billingFields[] = new CCDPostParserFormField('StreetAddress1', 'Address 1', CCDPostParserFormField::FIELD_TYPE_STRING, true);
            $billingFields[] = new CCDPostParserFormField('StreetAddress2', 'Address 2', CCDPostParserFormField::FIELD_TYPE_STRING, false);
            $billingFields[] = new CCDPostParserFormField('City', 'City',CCDPostParserFormField::FIELD_TYPE_STRING, true);
            $billingFields[] = new CCDPostParserFormField('PostalCode', 'Zip', CCDPostParserFormField::FIELD_TYPE_STRING, true);
            $billingFields[] = new CCDPostParserFormField('State', 'State',CCDPostParserFormField::FIELD_TYPE_STATE, false);
            $billingFields[] = new CCDPostParserFormField('Country', 'Country',CCDPostParserFormField::FIELD_TYPE_COUNTRY, true);
        }

        //Initialize the shipping fields and see if we have any custom configuration for them
        $shippingFields = !empty(self::$externalPostParserShippingConfig) ? self::$externalPostParserShippingConfig : array();
        if(empty($shippingFields)){
            $shippingFields[] = new CCDPostParserFormField('Address2Street1', 'Address1', CCDPostParserFormField::FIELD_TYPE_STRING, true);
            $shippingFields[] = new CCDPostParserFormField('Address2Street2', 'Address2', CCDPostParserFormField::FIELD_TYPE_STRING, false);
            $shippingFields[] = new CCDPostParserFormField('City2', 'City', CCDPostParserFormField::FIELD_TYPE_STRING, true);
            $shippingFields[] = new CCDPostParserFormField('PostalCode2', 'Zip', CCDPostParserFormField::FIELD_TYPE_STRING, true);
            $shippingFields[] = new CCDPostParserFormField('State2', 'State', CCDPostParserFormField::FIELD_TYPE_STATE, false);
            $shippingFields[] = new CCDPostParserFormField('Country2', 'Country', CCDPostParserFormField::FIELD_TYPE_COUNTRY, true);
        }
        
        //Initialize the payment fields. Everything is hardcoded here
        //@TODO: We need a new 'promise' here: If type = CC, then we require the CC fields
        $paymentFields = array();
        //WARNING: 'usePaypal' is an input name searched for by CCDPostParserPaymentFields::validateFieldValue()
        $paymentFields[] = new CCDPostParserFormField('usePaypal', 'Use Paypal?', CCDPostParserFormField::FIELD_TYPE_BOOLEAN, true);
        $paymentFields[] = new CCDPostParserFormField('CardType', 'Card Type', CCDPostParserFormField::FIELD_TYPE_SELECT, true, array(''=>'Please Select a Card Type *','American Express'=>'American Express','Discover'=>'Discover','MasterCard'=>'MasterCard','Visa'=>'Visa'));
        $paymentFields[] = new CCDPostParserFormField('CardNumber', 'Card Number', CCDPostParserFormField::FIELD_TYPE_CREDITCARD_NUMBER, true);
        $paymentFields[] = new CCDPostParserFormField('ExpirationMonth', 'Expiration Month', CCDPostParserFormField::FIELD_TYPE_SELECT, true, array('01'=>'01','02'=>'02','03'=>'03','04'=>'04','05'=>'05','06'=>'06','07'=>'07','08'=>'08','09'=>'09','10'=>'10','11'=>'11','12'=>'12'));
        $yearList = array();
        for($i = 0; $i < 15; $i++){
            $year = date('Y',strtotime('+ '.$i.' years'));
            $yearList[$year] = $year; 
        }
        $paymentFields[] = new CCDPostParserFormField('ExpirationYear', 'Expiration Year', CCDPostParserFormField::FIELD_TYPE_SELECT, true, $yearList);
        $paymentFields[] = new CCDPostParserFormField('SecurityCode', 'CVC',CCDPostParserFormField::FIELD_TYPE_STRING, true);

        self::$postParserConfig = new CCDPostParserLPConfig($contactFields, $billingFields, $shippingFields, $paymentFields);

        //Done
        return;
    }

    /**
     * Override the default contact field configuration via the configuration.php file
     * @param Array[CCDPostParserFormField] $contactFields		The contact fields
     */
    public static function configureContactFields(array $contactFields){self::$externalPostParserContactConfig = $contactFields;}

    /**
     * Override the default billing field configuration via the configuration.php file
     * @param Array[CCDPostParserFormField] $billingFields		The billing fields
     */
    public static function configureBillingFields(array $billingFields){self::$externalPostParserBillingConfig = $billingFields;}

    /**
     * Override the default shipping field configuration via the configuration.php file
     * @param Array[CCDPostParserFormField] $shippingFields		The shipping fields
     */
    public static function configureShippingFields(array $shippingFields){self::$externalPostParserShippingConfig = $shippingFields;}

}
