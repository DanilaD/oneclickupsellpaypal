<?php

abstract class CCDPostParserFieldSet{
    //Where to find the field values
    const GET_VARS = 1;
    const POST_VARS = 2;
    const REQUEST_VARS = 3;

    protected $dataSource = null;
    protected $fieldValues = null;
    
    //Whether to store the old field values in the session or not. CC fields should never do this, so we default to no
    protected $storeOldFieldValuesInSession = false;

    //Values acceptable in the CreditCard IFS Table. Used by methods extracting data in a format applicable for the IFS dsAdd in the CreditCard table
    protected $creditcardTableValues = array('BillAddress1', 'BillAddress2', 'BillCity', 'BillCountry', 'PhoneNumber');

    abstract protected function getFields();

    public function __construct($method = null){
        //If no data-source defined, default to $_REQUEST if we're on debug, $_POST if not
        if($method === null){
            $method = defined('CCD_CONF_DEBUG') && CCD_CONF_DEBUG ? self::REQUEST_VARS : self::POST_VARS;
        }

        //See where we have to get the values from
        $dataSource = null;
        switch($method){
            case self::GET_VARS:
                $dataSource = $_GET;
                break;
            case self::POST_VARS:
                $dataSource = $_POST;
                break;
            case self::REQUEST_VARS:
                $dataSource = $_REQUEST;
                break;
            default:
                throw new CCDPostParserException('CCDPostParserFieldSet::__construct() received an unsupported data source: '.$method, 1);
                break;
        }

        $this->dataSource = $dataSource;

        $this->setGroupValues();
    }

    public function getFieldValues(){return $this->fieldValues;}

    public function getFieldValuesAsArray(){
        $returnArray = array();

        foreach($this->fieldValues AS $fieldVal){
            /* @var $fieldVal CCDPostParserFormFieldData */
            $returnArray[$fieldVal->name] = $fieldVal->value;
        }

        return $returnArray;
    }

    protected function setGroupValues(){
        //Get the configuration a-la LP
        $configuration = CCDPostParserLP::getPostParserConfig(); /* @var $configuration CCDPostParserLPConfig */

        //Fetch the fields defined for this 'group'
        $fieldArray = $this->getFields();
        $returnArray = array();

        //Retrieve/verify the related fields
        foreach($fieldArray AS $field){
            /* @var $contactField CCDPostParserFormField */
            if(!$this->validateFieldValue($field));

            //If no value present, do not proceed
            if(!isset($this->dataSource[$field->name])){continue;}

            //Add the field/value pair to the result array
            $value = $this->dataSource[$field->name];
            $returnArray[] = CCDPostParserFormFieldData::buildFromFormField($value, $field);
            
            //Store the value in the session, if requested/possible
            if($this->storeOldFieldValuesInSession && CCDPostParserLP::openSession()){
                $_SESSION['postparserData']['oldData'][$field->name] = $value;
            }
        }

        //Done
        $this->fieldValues = $returnArray;
    }

    protected function validateFieldValue(CCDPostParserFormField $field){
        //Make sure that required fields are present
        if($field->required && empty($this->dataSource[$field->name])){
            throw new CCDPostParserFormFieldException('No value for required field: '.$field->name, 2);
        }

        //Extract the value
        $value = isset($this->dataSource[$field->name]) ? $this->dataSource[$field->name] : null;

        //Select-like elements need to be checked to make sure they fall in line with the available choices
        if($field->type == CCDPostParserFormField::FIELD_TYPE_SELECT || $field->type == CCDPostParserFormField::FIELD_TYPE_COUNTRY){
            if(!array_key_exists($value, $field->options)){
                throw new CCDPostParserFormFieldException('Invalid value for '.$field->name.': '.$value, 2);
            }
        }
    }

}

/**
 * Fieldsets that contain billing information
 */
class CCDPostParserPaymentFields extends CCDPostParserFieldSet{
    //Values stored in the session for later usage
    const PAYMENT_TYPE_PAYPAL = 'paypal';
    const PAYMENT_TYPE_CREDITCARD = 'creditcard';
    
    /**
     * Override the constructor so we store the payment type as well
     * @param unknown $method
     */
    public function __construct($method = null){
        parent::__construct($method);
        
        //Store the value so the upsells know which kind of button to show (CC-upsell or paypal-upsell)
        if(empty($_SESSION['postparserData'])){
            $_SESSION['postparserData'] = array();
        }
        if($this->isPaypal()){
            $_SESSION['postparserData']['payment_type'] = self::PAYMENT_TYPE_PAYPAL;
        } else {
            $_SESSION['postparserData']['payment_type'] = self::PAYMENT_TYPE_CREDITCARD;
        }
    }
    
    protected function getFields(){
        return CCDPostParserLP::getPostParserConfig()->getPaymentFields();
    }

    public function getFieldValuesAsArray(){
        $returnArray = array();

        foreach($this->fieldValues AS $fieldVal){
            /* @var $fieldVal CCDPostParserFormFieldData */
            if($fieldVal->name != 'usePaypal'){
                $returnArray[$fieldVal->name] = $fieldVal->value;
            }
        }

        return $returnArray;
    }

    /*
     * Override to avoid validating field values when we're using paypal
     *
     * (non-PHPdoc)
     * @see CCDPostParserFieldSet::validateFieldValue()
     */
    protected function validateFieldValue(CCDPostParserFormField $field){
        //Make sure that required fields are present, but only if paypal is NOT being used (i.e: CC fields are not required when using paypal)
        //NOTICE: Also we use isset instead of empty() since empty('0') == true
        if(!$this->isPaypal() && $field->name != 'usePaypal'){
            parent::validateFieldValue($field);
        }
    }

    public function isPaypal(){
        return !empty($this->dataSource['usePaypal']) && $this->dataSource['usePaypal'];;
    }

    /**
     * Extract the field values applicable for creating the IFS CreditCard table row.
     * We simply replace 'SecurityCode' with CVV2
     * @return Array[mixed]
     */
    public function getCreditcardTableValues(){
        $returnArray = array();
        
        $normalValues = $this->getFieldValuesAsArray();

        foreach($normalValues AS $key=>$val){
            /* @var $fieldVal CCDPostParserFormFieldData */
            if($key == 'SecurityCode'){
                $returnArray['CVV2'] = $val;
            } else {
                $returnArray[$key] = $val;
            }
        }

        return $returnArray;
    }
    
}

/**
 * Fieldsets that contain address fields have a non-configurable and and agreed-upon rule with the front-end:
 * If the Country = US, then we require the state. If not, then we do not require a state value.
 * This class enforces these checks by extending the validateFieldValue method
 */
abstract class CCDPostParserAddressFieldSet extends CCDPostParserFieldSet{
    protected $stateFieldNames = array('State','State1','State2');
    protected $countryFieldNames = array('Country','Country1','Country2');

    protected $stateFieldName = null;
    protected $countryFieldName = null;

    public function __construct($method = null){
        //Construct parent, which calls validateFieldValue() on all fields, so we now we should have values for $this->fieldValues
        parent::__construct($method);

        //Locate any Country/state fields in the list of fields
        $countryField = null;    /* @var $countryField CCDPostParserFormFieldData */
        $stateField = null;    /* @var $stateField CCDPostParserFormFieldData */
        foreach($this->fieldValues AS $fieldVal){
            /* @var $fieldVal CCDPostParserFormFieldData */
            if($fieldVal->name === $this->countryFieldName){$countryField = $fieldVal;}
            if($fieldVal->name === $this->stateFieldName){$stateField = $fieldVal;}
        }

        //We always need the country field
        if(empty($countryField)){
            throw new CCDPostParserException('No country field found', 1);
        }

        //When country == US, then the state field value is required
        if($countryField->value == 'United States'){
            if((empty($stateField) || empty($stateField->value))){
                throw new CCDPostParserFormFieldException('State value required for the US', 2);
            }

            //Select-like elements need to be checked to make sure they fall in line with the available choices
            if(!array_key_exists($stateField->value, $stateField->options)){
                throw new CCDPostParserFormFieldException('Invalid value for '.$stateField->name.': '.$stateField->value, 2);
            }
        }

    }

    /**
     * Returns the state field
     * @return CCDPostParserFormFieldData|NULL		The state field, or NULL if none found
     */
    public function getStateField(){
        foreach($this->fieldValues AS $fieldVal){
            /* @var $fieldVal CCDPostParserFormFieldData */
            if($fieldVal->name === $this->stateFieldName){return $fieldVal;}
        }

        return null;
    }

    /**
     * Returns the country field
     * @throws CCDPostParserException 			In case no country field has been found
     * @return CCDPostParserFormFieldData		The country field
     */
    public function getCountryField(){
        foreach($this->fieldValues AS $fieldVal){
            /* @var $fieldVal CCDPostParserFormFieldData */
            if($fieldVal->name === $this->countryFieldName){return $fieldVal;}
        }

        throw new CCDPostParserException('No country field found', 1);
    }

    protected function validateFieldValue(CCDPostParserFormField $field){
        //See if this is one of the known country fields
        if(in_array($field->name, $this->countryFieldNames)){
            //Make sure that we don't have duplicate fields
            if(!empty($this->countryFieldName)){throw new CCDPostParserFormFieldException('More than one country fields found: '.$this->countryFieldName.'/'.$field->name,500);}

            //Store the field
            $this->countryFieldName = $field->name;
            return;
        }

        //See if this is one of the known state fields
        if(in_array($field->name, $this->stateFieldNames)){
            //Make sure that we don't have duplicate fields
            if(!empty($this->stateFieldName)){throw new CCDPostParserException('More than one state fields found: '.$this->stateFieldName.'/'.$field->name,500);}

            //Store the field
            $this->stateFieldName = $field->name;
            //NOTICE: We do NOT return(); This is because we want to let the State field continue validation as usual
        }

        //Normal field, just validate as usual
        parent::validateFieldValue($field);
    }

}



class CCDPostParserContactFields extends CCDPostParserFieldSet{
    protected $storeOldFieldValuesInSession = true; //We should store posted contact field values in the session
    
    protected function getFields(){return CCDPostParserLP::getPostParserConfig()->getContactFields();}

    /**
     * Extract the field values applicable for creating the IFS CreditCard table row
     * @return Array[mixed]
     */
    public function getCreditcardTableValues(){
        $returnArray = array();

        //Fetch the contact array
        $contactArray = $this->getFieldValuesAsArray();

        //See if we can extrapolate a nameOnCard
        $nameOnCard = !empty($contactArray['FirstName']) ? $contactArray['FirstName'] : '';
        if(!empty($contactArray['LastName'])){
            $nameOnCard = !empty($nameOnCard) ? $nameOnCard .' '.$contactArray['LastName'] : $contactArray['LastName'];
        }
        if($nameOnCard){$returnArray['NameOnCard'] = $nameOnCard;}

        //See if we can find a phone
        if(!empty($contactArray['Phone1'])){$returnArray['PhoneNumber']=$contactArray['Phone1'];}

        //See if we can find an email
        if(!empty($contactArray['Email'])){$returnArray['Email']=$contactArray['Email'];}

        //Done
        return $returnArray;
    }

}

class CCDPostParserBillingFields extends CCDPostParserAddressFieldSet{
    protected $storeOldFieldValuesInSession = true; //We should store posted billing fields values in the session
    
    protected function getFields(){return CCDPostParserLP::getPostParserConfig()->getBillingFields();}

    /**
     * Extract the field values applicable for creating the IFS CreditCard table row
     * @return Array[mixed]
     */
    public function getCreditcardTableValues(){
        $returnArray = array();

        foreach($this->fieldValues AS $fieldVal){
            /* @var $fieldVal CCDPostParserFormFieldData */
            $billName = 'Billing'.$fieldVal->name;

            if(in_array($billName, $this->creditcardTableValues)){
                $returnArray[$billName] = $fieldVal->value;
            }
        }

        //Done
        return $returnArray;
    }

}

class CCDPostParserShippingFields extends CCDPostParserAddressFieldSet{
    protected $storeOldFieldValuesInSession = true; //We should store posted shipping field values in the session
    
    protected function getFields(){return CCDPostParserLP::getPostParserConfig()->getShippingFields();}
}