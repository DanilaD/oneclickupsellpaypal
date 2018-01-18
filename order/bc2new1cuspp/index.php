<?php 

	if(empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on")
	{

		header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);

		exit();

	}

	// Get the tracking ID from our cookie
	if ( isset($_COOKIE['ccdleadsource']) ) {
	
		$leadsource = $_COOKIE['ccdleadsource'];
		
	}
	
	else {
	
		$leadsource = 'healthplus50.com';
		
	}

	// Split tester
	require '/home/honestw/public_html/abtester/abtester.php';
	
	
	if ( $abTesterValues[0][0] == '0' ) {
		
		$leadsource .= '_ord_t7v1';
		$header = 'inc/header.php';
		
	}
	
	else {

		$leadsource .= '_ord_t7v2';
		$header = 'inc/header_prooftest.php';
		
	} 
	
	setcookie("ccdleadsource", "$leadsource", time()+(3600*24*7), "/", ".healthplus50.com");
	
	
require 'postparser/postparser_lp.php';
require 'configuration.php';
require 'inc/generate_fields.php';

$errorMsg              = CCDPostParserLP::getLastErrorMessage();
$postParserFields      = CCDPostParserLP::getPostParserConfig();
$contactFields         = $postParserFields->getContactFields();
$billingAddressFields  = $postParserFields->getBillingFields();
$shippingAddressFields = $postParserFields->getShippingFields();
$paymentFields         = $postParserFields->getPaymentFields();
$productName           = $postParserFields->getProductUIName();


/*
 * CHANGES BY GEORGIOS, START
 */

//1. We need to set the cookie that will allow the browser to get by the duplicate-sale protection we have on the upsell pages
//It's just a cookie value of the max time() that people are allowed to buy any upsells. Each upsell page checks against it and will reject the
//view if the value is in the past or expired

$_SESSION['max_upsell_timestamp'] = time()+21600;    //21600 secs is 6 hours


/*
 * CHANGES BY GEORGIOS, STOP
 */


/** OMITING THESE DUE TO ACTIVE SPLIT TEST
$leadsource = false;
if ( isset($_COOKIE['ccdleadsource']) ) {

	$leadsource = $_COOKIE['ccdleadsource'];
	
} else if ( isset($_GET['tid']) && $_GET['tid']) {
    //GN-CHANGE: keep logs clean in case this is not set
    $leadsource = $_GET['tid'];
}
**/

	$page_title = "HealthPlus50.com - Order";
	$custom_body_class = "order-page-template";
	include $header;
?>


<div id="primary" class="inner-page" style="max-width:650px;">
<?php if($errorMsg):?>
	<div class="alert alert-danger">
		<p><?php echo $errorMsg;?></p>
	</div>
<?php endif;?>

<?php if (isset($_GET['duplicate'])) { ?>
	<div class="alert alert-danger">
		<p>It's possible that this order to be  a duplicate order because there's another order made for this product <?php echo $_GET['duplicate'];?> hours ago.</p>
		<p>If you think that this is an error and actually you want to order please click on the "Confirm Order" button and re-submit the form.</p>
		<br/>
		<button type="button"  class="btn btn-success confirm-not-duplicate">Confirm Order</button>
	</div>
<?php } ?>
	
	<h1>Here's A Recap Of What You Receive With The Cruise Control Diet:</h1>
		
	<img src="https://www.healthplus50.com/images/ccdcorewcaption.jpg"  border="0" class="pull-left top-order-image" />
	<div class="hide-on-mobile">
		 <p> 
			<strong>The Cruise Control Diet</strong> - a simple plan that shows you how to achieve a healthy weight by eating natural, whole foods (while avoiding those that cause weight gain)...
			<br /><br />
			<strong>The Cook Book</strong> - with more than 70 delicious recipes to help you reach your fitness goals while keeping things fun and tasty...
			<br /><br />
			<strong>The Jumpstart Guide</strong> - which walks you through your first week of grocery shopping and helps you quickly implement the program...
			<br /><br />
			Finally, you're fully protected by a 60-day, Iron Clad, 100% Money Back Guarantee.
			<br /><br /><br />
		 </p>
	</div>
	<div class="display-on-mobile">
		<p>
			A simple plan that shows you how to achieve a healthy weight by eating natural, whole foods (while avoiding those that cause weight gain)...
			<br /><br />
			Backed by a 60-day Iron Clad, 100% Money Back Guarantee. 
			<br /><br />
		</p>
	</div>
	<p><img class="img-responsive" src="https://www.healthplus50.com/images/checkmarks.png" width="600" height="50" border="0" /></p>
	<form id="orderForm" class="order-form" method="POST" action="postparser/postparser.php" data-toggle="validator" >
		<input type="hidden" name="Leadsource" value="<?=$leadsource?>">
		<div class="contact-information-section">
			<h4 class="order-form-black-title">Contact Information</h4>
			<?php generateFields($contactFields);?>	
		</div>
		<!-- /.contact-information-section -->
		<div class="billing-address-section">
			<h4 class="order-form-black-title">Billing Address <span>(This is the address where you receive your credit card statements)</span></h4>
			<?php generateFields($billingAddressFields);?>
			<div class="checkbox">
				<label><input id="shipping_check" type="checkbox" checked="checked" autocomplete="off">
				<strong>Shipping address is the same as billing</strong></label>
			</div>
		</div>
		<!-- /.billing-address-section -->
		<div class="shipping-address-section">
			<h4 class="order-form-black-title">Shipping Address</h4>
			<?php generateFields($shippingAddressFields);?>
		</div>
		<!-- /.shipping-address-section -->
		<div class="change-payment-method-section">
			<h4 class="order-form-black-title">Payment Methods</h4>	
			<div class="payment-methods-fields">
				<?php generateFields($paymentFields);?>
			</div>
		</div>
		<!-- /.change-payment-method-section -->
		<div class="product-purchase-plan">
			<h4 class="order-form-black-title">Product Purchase Plan</h4>	
			<div class="form-group">
				<div class="col-md-10 col-sm-9 col-xs-9">
					<strong><?php echo $productName; ?> </strong>
				</div>
				<div class="col-md-2 col-sm-3 col-xs-3">
					<span>Amt</span>
				</div>
			</div>
			<div class="form-group">	
				<div class="col-md-10 col-sm-9 col-xs-9">
					<div class="radio disabled">
						<label><input type="radio" disabled checked>1 Payment of <span class="product-price-value"></span></label>
					</div>
				</div>
				<div class="col-md-2 col-sm-3 col-xs-3">
					<strong class="product-price-value"></strong>
				</div>
			</div>
		</div>
		<!-- /.product-purchase-plan -->
		<div class="total-amount-you-pay-section">
			<h4 class="order-form-black-title">Total Amount You Pay Right Now</h4>	
			<div class="form-group">	
				<div class="col-md-10 col-sm-9 col-xs-9">
					<span>The Cruise Control Diet Core Program (X1)</span>
				</div>
				<div class="col-md-2 col-sm-3 col-xs-3">
					<span class="product-price-value"></span>
				</div>
			</div>
			<div class="form-group">	
				<div class="col-md-10 col-sm-9 col-xs-9">
					<span>Shipping</span>
				</div>
				<div class="col-md-2 col-sm-3 col-xs-3">
					<span class="shipping-fee-value"></span>
				</div>
			</div>
			<div class="form-group">	
				<div class="col-md-10 col-sm-9 col-xs-9">
					<span>Tax</span>
				</div>
				<div class="col-md-2 col-sm-3 col-xs-3">
					<span class="tax-fee-value"></span>
				</div>
			</div>
			<hr>
			<div class="form-group">	
				<div class="col-md-10 col-sm-9 col-xs-9">
					<span>Total</span>
				</div>
				<div class="col-md-2 col-sm-3 col-xs-3">
					<span class="total-price-value"></span>
				</div>
			</div>
		</div>
		<!-- /.total-amount-you-pay-section -->
		<div class="process-section text-center">
			<h4 class="order-form-black-title">Process</h4>	
			<button type="submit" name="Order" class="btn submit-order" ><image class="img-responsive" src="images/rushshipcopy.jpg"/></button>
		</div>
		<!-- /.process-section -->
	</form>
	<br />
	<div  style="font-size: 10pt;">
		<strong>Terms of sale:</strong>
		<br /><br />
		<ul class="indented-list-style-disc">
			<li>Your purchase will appear on your bank statement under the name 'CRUISECDIET6176742008'.</li>
			<li>Your purchase today is only 1 easy payment of <span class="product-price-value"></span> + S&amp;H ($9.99 for US &amp; Canada; $14.99 for international shipments. <strong>There are no monthly or recurring charges</strong>).</li>
			<li>Your package will arrive within 3-5 business days.</li>
			<li>You will also receive instant access to the digital version as soon as your order is complete.</li>
			<li>Your purchase is covered by a 60-day 100% money back guarantee (less S&amp;H) as per our <a href="https://healthplus50.com/returns.php" target="_new">return policy</a>.</li>
		</ul>  
	</div>
</div>	
<div style="display:none !important;">
	<span id="cvc-popup" style="font-size: 10pt;"><a href="#CVC" onclick="javascript:window.open('https://www.healthplus50.com/cvc.htm', '', 'location=0, status=0, scrollbars=1, width=600, height=470, titlebar=yes, top=100, left=200');">What is this?</a></span>
</div>
<script>
    var current_page="order-menu-item";
</script>	
		
<script>
	<?php echo CCDPostParserLP::getPostParserCalculationJS();?>
</script>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<!-- <script src="js/bootstrap.min.js"></script> -->
<!-- Include our JS -->
<script src="js/order-form-xv.js"></script>
<?php include 'inc/footer-xv-test.php'; ?>		

<!-- Auto populate email value if is set $_COOKIE["hp50_email_captured"] -->
<?php if(isset($_COOKIE["hp50_email_captured"])){ ?>
	
	<script>
		var inf_field_Email = "<?php echo $_COOKIE["hp50_email_captured"]; ?>";
		$('#orderForm input[type="email"]').val(inf_field_Email);
	</script>
	
<?php } ?>