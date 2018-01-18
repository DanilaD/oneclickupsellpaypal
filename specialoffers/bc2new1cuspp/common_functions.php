<?php
/*
 * Common functions/classes
 * CHANGELOG: 
 * V2 (Feb 13th 2015): Added logDebug/logNotice
 * V2.1 (Feb 16th 2015): Fixing minor typo
 * V2.2 (Feb 18th 2015): Adding default timezone as EST (PHP on live apparently is set to UTC)
 */

 date_default_timezone_set("EST");

/**
 * Handles the error/warning logging.
 * Uses the values set from configuration.php, but it will also fallback to some defaults if it's values are not found
 * @author Georgios Ntampitzias (nonickch@gmail.com)
 *
 */
class CCDLogger{
    const CCD_EVENT_WARNING = 1;
    const CCD_EVENT_ERROR = 2;
    const CCD_EVENT_NOTICE = 3;
    const CCD_EVENT_DEBUG = 4;

    /**
     * Parse a critical error message
     * @param String $message	The error description
     */
    public static function logError($message){
        self::handleEvent(self::CCD_EVENT_ERROR, $message);
    }

    /**
     * Parse a non-critical error message				()
     * @param String $message	The error description
     */
    public static function logWarning($message){
        self::handleEvent(self::CCD_EVENT_WARNING, $message);
    }

    /**
     * Parse a debug-level error message (will only appear in stdout/logs only)
     * @param String $message	The error description
     */
    public static function logDebug($message){
        self::handleEvent(self::CCD_EVENT_DEBUG, $message);
    }

    /**
     * Parse a debug-level error message (will only appear in stdout/logs IF debug is enabled)
     * @param String $message	The error description
     */
    public static function logNotice($message){
        self::handleEvent(self::CCD_EVENT_NOTICE, $message);
    }

    /**
     * Handles an error or warning event according to the configuration
     * @param Integer $type				The event type as defined by the local const values
     * @param String $message			The message to log
     */
    protected static function handleEvent($type, $message){
        //See if we need to print to stdout
        if((defined('CCD_CONF_DEBUG') && CCD_CONF_DEBUG)){
            echo $message."<br/>\n";
        }

        //Check if we need to email
        if($type == self::CCD_EVENT_WARNING && self::warnings_emailed()){
            mail(CCD_CONF_EMAIL_RECIPIENTS, self::getWarningEmailTitle(), $message, "From: ".CCD_CONF_EMAIL_SENDER."\n");
        }

        if($type == self::CCD_EVENT_ERROR && self::errors_emailed()){
            mail(CCD_CONF_EMAIL_RECIPIENTS, self::getErrorEmailTitle(), $message, "From: ".CCD_CONF_EMAIL_SENDER."\n");
        }
        
        $title = 'UNKNOWN';
        switch($type){
            case self::CCD_EVENT_DEBUG :
                $title = 'DEBUG';
                break;
            case self::CCD_EVENT_NOTICE :
                $title = 'NOTICE';
                break;
            case self::CCD_EVENT_WARNING :
                $title = 'WARNING';
                break;
            case self::CCD_EVENT_ERROR :
                $title = 'ERROR';
                break;
        }

        //Check if we need to log this message
        if((
            $type == self::CCD_EVENT_WARNING && self::log_warnings()) || 
            $type == self::CCD_EVENT_ERROR ||
            $type == self::CCD_EVENT_NOTICE ||
            ($type == self::CCD_EVENT_DEBUG && defined('CCD_CONF_DEBUG') && CCD_CONF_DEBUG )
            ){
            if(self::getCusomLogLocation()){
                //Log to custom log file
                error_log($title.' '.$message."\n", 3, self::getCusomLogLocation());
            } else {
                //Log to the default PHP error_log
                error_log($title.' '.$message);
            }
        }
    }

    /*
     * Simple configuration-fetching functions with fallbacks in case the configuration file has not been loaded (configuration.php)
     */
    protected static function log_warnings(){return (defined('CCD_CONF_LOG_WARNINGS')&& CCD_CONF_LOG_WARNINGS);}
    protected static function warnings_emailed(){
        return (
        defined('CCD_CONF_EMAIL_ERRORS') &&
        defined('CCD_CONF_EMAIL_RECIPIENTS') &&
        defined('CCD_CONF_EMAIL_SENDER') &&
        CCD_CONF_EMAIL_ERRORS &&
        CCD_CONF_EMAIL_RECIPIENTS &&
        CCD_CONF_EMAIL_SENDER );
    }
    protected static function errors_emailed(){
        return (
        defined('CCD_CONF_EMAIL_WARNINGS') &&
        defined('CCD_CONF_EMAIL_RECIPIENTS') &&
        defined('CCD_CONF_EMAIL_SENDER') &&
        CCD_CONF_EMAIL_WARNINGS &&
        CCD_CONF_EMAIL_RECIPIENTS &&
        CCD_CONF_EMAIL_SENDER);
    }
    protected static function getCusomLogLocation(){return (defined('CCD_CONF_LOG_LOCATION')&& CCD_CONF_LOG_LOCATION) ? CCD_CONF_LOG_LOCATION : false ;}
    protected static function getErrorEmailTitle(){return (defined('CCD_CONF_ERROR_EMAIL_SUBJECT') && CCD_CONF_ERROR_EMAIL_SUBJECT) ? CCD_CONF_ERROR_EMAIL_SUBJECT : 'An error has occured: ';}
    protected static function getWarningEmailTitle(){return (defined('CCD_CONF_WARNING_EMAIL_SUBJECT') && CCD_CONF_WARNING_EMAIL_SUBJECT) ? CCD_CONF_WARNING_EMAIL_SUBJECT : 'A warning has occured: ';}

}
/*
 * Helper class for IPN functionality
 */
class CCDIPN{

    /**
     * Fails the IPN call by
     * 1) returning a 500 status
     * 2) calling CCDLogger::logError()
     * 3) exit()'ing the script
     * @param String $message	The message to log with CCDLogger::logError()
     */
    public static function failIPN($message, $logMessage = true){
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        if($logMessage){
            CCDLogger::logError($message);
        }
        exit;
    }
}