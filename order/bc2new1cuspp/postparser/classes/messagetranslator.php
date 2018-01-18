<?php
/**
 *  Translation of system-sourced messages (like the CC payment gateway) to more user-friendly messages
 *
 */
class CCDPostParserMessageTranslator{
    protected static $translationList = array(
		'part of the old message' => 'The entire new message to show.',
        'indicate unknown card' => 'The Credit Card Number you have provided is incorrect.',
		'Number failed checksum test' => 'You have entered an invalid credit card number. Please correct the error and re-submit.',
		'Number is missing 2 digit(s) ' => 'You have entered an invalid credit card number. Please correct the error and re-submit.',
		'We could not charge your credit card. Declined: Do Not Honor - Response code(201)' => 'We could not charge your card. Please call the 800-number on the back of your card to authorize this purchase.',
		'We could not charge your credit card. Declined: Declined for CVV failure - Response code(225)' => 'We could not charge your card because you entered an incorrect CVV code (3-digit number on the back of your card; 4-digit number on the front if paying with AMEX). Please correct the error and re-submit.',
		'No value for required field: Address2Street1' => 'We could not charge your card due to an incomplete shipping address. Please correct this error and re-submit.',
		'Number is missing 1 digit(s)' => 'You have entered an invalid credit card number. Please correct the error and re-submit.',
		'Number has 1 too many digit(s)' => 'You have entered an invalid credit card number. Please correct the error and re-submit.',
		'We could not charge your credit card. Declined: Insufficient Funds - Response code(202)' => 'We could not charge your card due to insufficient funds. Please use a different card and try again.',
		'We could not charge your credit card. Declined: DECLINE - Response code(200)' => 'We could not charge your card. Please call the 800-number on the back of your card to authorize this purchase.',
		'We could not charge your credit card. Error: Duplicate transaction REFID' => 'You have already placed an order for this product. Your last charge attempt was blocked to prevent a duplicate transaction. Please check your Email for the original order receipt.',
		'We could not charge your credit card. Declined: AVS REJECTED - Response code(300)' => 'We could not charge your card. Please make sure the billing address you entered matches exactly what your credit card company has on file (i.e. where you receive your paper statements).',
		'No value for required field: FirstName' => 'Please enter your correct First Name to proceed.',
		'State value required for the US' => 'You must choose a State if shipping within the US. Please correct the error and re-submit.',
		'Number is missing 3 digit(s)' => 'You have entered an invalid credit card number. Please correct the error and re-submit.',
		'First four digits, 1234, indicate unknown card type' => 'You have entered an invalid credit card number. Please correct the error and re-submit.'	
    );
    
    
    /**
     * Attempts to translate the given string to a better human-readable format
     * @param String $message   The (possibly) translated message
     * @return String
     */
    public static function translate($message){
        foreach(self::$translationList AS $searchString=>$translatedMessage){
            if(strpos($message, $searchString) !== false){
               return $translatedMessage;
            }
        }
        
        //Nothing found, return the message as it was given to us
        return $message;
    }
    
    
}