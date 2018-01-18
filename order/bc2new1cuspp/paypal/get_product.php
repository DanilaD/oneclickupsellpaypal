<?php

// CONFIGURATION
include 'paypal_configuration.php';

//IFS iSDK
include '../postparser/isdk.php';

//Instantiate the iSDK
$app = new iSDK;
//$app_name = (defined('CCD_CONF_SANDBOX') && CCD_CONF_SANDBOX) ? CCD_CONF_IFS_SANDBOX_APPNAME : CCD_CONF_IFS_APPNAME;
//$api_key = (defined('CCD_CONF_SANDBOX') && CCD_CONF_SANDBOX) ? CCD_CONF_IFS_SANDBOX_API_KEY : CCD_CONF_IFS_API_KEY;

define('CCD_CONF_IFS_APPNAME','yc147');
define('CCD_CONF_IFS_API_KEY','83b397e3f92e91283a5dd1372adc0327');

$app_name = CCD_CONF_IFS_APPNAME;
$api_key = CCD_CONF_IFS_API_KEY;

try{
    $app->cfgCon($app_name, $api_key, 'throw');
} catch(Exception $e){
    CCDIPN::failIPN(__FILE__.' encountered an error while initializing the iSDK: '.$e->getMessage());
}

/*
 * 
 * This's array of PRODUCT_ID, which we take from IFS. 
 * EXAMPLE array(4,24,112) 
 * 
 */
$ar = array(4,24,112,114,116,118,120,122,29);

$products_array = $app->dsQuery('Product', 1000, 0, array('Id'=> $ar), array('id', 'ProductName', 'ProductPrice', 'ShortDescription', 'Sku'));

// save data to file
$content = "<?\n";
$content .= arr2str($products_array, '$products', '');
// the name of file from paypal_configuration.php
$path = ( SANDBOX != TRUE ) ? LIFE_PRODUCT_FILE : SANDBOX_PRODUCT_FILE ;
$handle = fopen($path, 'w');
fwrite($handle, $content);
fclose($handle);


// arr - array of items
// bf - the name of variable for array
// cont - for name of array in array, can be blank
// prepare data from array to save it in file
function arr2str($arr, $bf, $cont = '')
{
  foreach ($arr as $k => $v)
  {
    $cbf = $bf."['".$k."']";
    if (is_array($v))
    {
      $cont = arr2str($v, $cbf, $cont);
    }
    else
    {
      $cont .= $cbf." = '".$v."';\n";
    }
  }
  return $cont;
}
