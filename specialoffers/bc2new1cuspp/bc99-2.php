<?php

	/*
	require_once 'configuration.php';
	require_once 'common_functions.php';
	
	//Upsell Duplicate Check
	require_once '/home/healthplus50/public_html/specialoffers/bc2cewlm/configuration_bc99-2.php';
	require_once '/home/honestw/public_html/upsell_duplicate_check/isdk.php';
	require_once '/home/honestw/public_html/upsell_duplicate_check/upsell_duplicate_check.php';
	
	$isUpsellDuplicate = CCDUpsellDuplicateCheck::isUpsellDuplicate();
	*/

	$video = '<script charset="ISO-8859-1" src="//fast.wistia.com/assets/external/E-v1.js" async></script><div class="wistia_embed wistia_async_p2xgnjrf2f videoFoam=true playerColor=ff0000" >&nbsp;</div>';
	$timer_start = '180';
	$timer_finish = '268';
	$buy_link = 'http://singleclicksale.com/yes/?ocus=mXZ7B37tvxPF';
	$decline_link = 'http://singleclicksale.com/no/?ocus=mXZ7B37tvxPF';
	$decline_text = 'No thanks. I will pass on this offer and give up my savings of 60% off. If I choose to purchase this package at a later date I will pay the retail price of $238.97 in full';
	$button 	 = "<img src='https://www.healthplus50.com/images/addtoorder.gif' width='420' height='117' border='0' />";	
	
	$current_url = trim($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], '/');
	
	if(
	(isset($_GET['paypal']) && $_GET['paypal']=='yes') ||
	(count($_GET)==2 && isset($_GET['cid']) )		/*This is a band-aid fix for the paypal loping off all but the first GET param. Please note that 1clickupsell translates the contactId param into a cid param */
	 ) {
	
		$payment_type = 'paypal';
	
	} else {
		
		$payment_type = 'creditcard';
	}
	
	$page_title = "HealthPlus50.com - Order Step 2b";
	$custom_body_class = "no-mobile-logo order-second-step-page";
	include '/home/healthplus50/public_html/inc/no-menu-header.php';
	
?>

<div id="primary" class="inner-page responsive" style="max-width:650px;">
	<div class="text-center">
		<h1 class="order-page-red-title">Wait! Your Order is Not Complete...</h1>
		<h2 class="order-page-second-title">(Order Step 2b)</h2>
		<p>Don't click the back button or else you won't see this page again</p>
		<div class="center-block text-center max-width-600px">
			<div>
				<?php echo $video; ?>
			</div>
			<br/>
			(<strong>Watch the video above</strong> for a very important message about your order)
		</div>
		<br />
		<span id="countdown" class="red-countdown"></span>
		<div id="purchase-links" class="display-none-element center-block max-width-600px">
			<?php 
			//The payment_type is not set, then do nothing
			if(!isset($payment_type)) {
				
				
			//The payment type is Credit Card
			} else if ( $payment_type == 'creditcard' ) { ?>
				<div id="terms-of-service-container">
					<p><strong>Terms of service</strong>: By clicking on the 'Add to Order' button below you will be billed the low price of $50.00 for this offer. You will be billed the remainder of the balance ($50.00) in 30 days. There are no further charges. Furthermore, this product is covered by a 60-day, 100% money-back-guarantee. If you're not happy (for any reason whatsoever) simply send the product back within 60 days of purchase and we'll issue you a prompt and courteous refund - no questions asked.</p>
					<div class="checkbox">
					  <label><input type="checkbox" id="terms-of-service-checkbox" value="">I agree to the terms of service</label>
					</div>
				</div>
				<a id="add-to-order-button" href='<?=$buy_link?>'><?php echo $button; ?></a>
				
			<?php 
			//The payment type is PayPal
			} else if ($payment_type == 'paypal') { ?>
				
				<form class="paypal-order-form" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="HB5S3TP75SK9J">
					<input type="hidden" name="on0" value="plan">
					<input type="hidden" name="os0" value ="option_0">
					<!--
					<h3>The Cruise Control Diet Boot Camp</h3>
					<p>Number of payments 2</p>
					<table style="width:100%;text-align:left;margin-bottom:20px;">
						<tr>
							<th>No.</th>
							<th>Due*</th>
							<th style="text-align:right;">Amount</th>
						</tr>
						<tr>
							<td>1</td>
							<td>At checkout</td>
							<td align="right">$50.00 USD</td>
						</tr>
						<tr>
							<td>2</td>
							<td>after 1 month</td>
							<td align="right">$50.00 USD</td>
						</tr>
						<tr>
							<td COLSPAN="3" ALIGN="right">Total   $100.00 USD</td>
						</tr>
					</table>
					<p><i>* We calculate payments from the date of checkout.</i></p>
					-->
					<input type="image" src="https://www.healthplus50.com/images/addtoorder.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"></td></tr></table>
					<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
			
			<?php
			//The payment type has an unexpected value
			} else if ( ($payment_type != 'paypal') &&  ($payment_type != 'creditcard') ) {
				
				CCDLogger::logError("Payment Type Error: The payment_type has an unexpected value:  " . $payment_type . " on " . $current_url);
				
				
			} ?>
				<div style='margin-top:15px;'><a href='<?php echo $decline_link;?>'><span style='font-size: 11pt; line-height: 16pt;'><u><?php echo $decline_text; ?></u></span></a></div>
		</div>
	</div>
	<?php if ($isUpsellDuplicate != true) { ?>
		<script type="text/javascript">
	
			function showIt() {
				document.getElementById("purchase-links").style.display = "block";
				document.getElementById("countdown").style.display = "none";
			}
		
			window._wq = window._wq || [];

			// target our video by the first 3 characters of the hashed ID
			_wq.push({"p2x": function(video) {
						
					var showMyElementOnce = function() {
					  showIt();
					  video.unbind("betweentimes", <?php echo $timer_start; ?>, <?php echo $timer_finish; ?>, showMyElementOnce);
					};
					video.bind("betweentimes", <?php echo $timer_start; ?>, <?php echo $timer_finish; ?>, showMyElementOnce);
					video.bind("secondchange", function() {
						
						var seconds = <?php echo $timer_start;?> - Math.round(video.time());
						var minutes = Math.round((seconds - 30)/60);
						var remainingSeconds = seconds % 60;
						if (remainingSeconds < 10) {
							remainingSeconds = "0" + remainingSeconds; 
						}
						document.getElementById("countdown").innerHTML= "Time Remaining:<br />" + minutes + ":" + remainingSeconds;
					});

			}});
			
		</script>
		<?php } else { ?>
		
		<div class="alert alert-warning">You have already purchased this offer. If you would like to purchase it again, please call us at <a href="tel:617-674-2008">617-674-2008</a>.</div>
		
		<?php } ?>
</div>	
<script src="//ocus.s3.amazonaws.com/ocusnovo.js"></script>
<audio id="alert-sound" src="../../media/alert_audio.mp3" preload="auto"></audio>
<script>
	/*Check if the terms-of-service-checkbox is checked, if isn't then the Add to Order button will not work. In order to work this code must be inserted after ocusnovo.js script */ 	
	
	var payment_type = "<?=$payment_type?>";
	
	if(payment_type == 'creditcard') {
		document.getElementById("add-to-order-button").onclick = function(event){
			
			var audio = new Audio('../../media/alert_audio.mp3');
			
			
			if(document.getElementById('terms-of-service-checkbox').checked) {
				//console.log("is checked ");
			} else {
				//console.log("is not checked");
				document.getElementById('alert-sound').play()
				alert("You must agree to the terms of service in order to add this to your order. Please check the box that reads 'I agree to the terms of service' and click on the 'Add to Order' button.");
				event.preventDefault();
				return false;
				
			}

		}
	}
</script>
<?php include '/home/healthplus50/public_html/inc/simple-footer.php';?>