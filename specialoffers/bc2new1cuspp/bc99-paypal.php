<?php

	// Split tester
	require '/home/honestw/public_html/abtester/abtester.php';
		
	//require_once 'configuration.php';
	//require_once 'common_functions.php';
	
	//Upsell Duplicate Check
	//require_once '/home/healthplus50/public_html/specialoffers/bc2cewlm/configuration_bc99.php';
	//require_once '/home/honestw/public_html/upsell_duplicate_check/isdk.php';
	//require_once '/home/honestw/public_html/upsell_duplicate_check/upsell_duplicate_check.php';
	
	//$isUpsellDuplicate = CCDUpsellDuplicateCheck::isUpsellDuplicate();
	
	$leadsource = isset($_GET['LeadSource'])?$_GET['LeadSource']:'';
	
	$video = '<script charset="ISO-8859-1" src="//fast.wistia.com/assets/external/E-v1.js" async></script><div class="wistia_embed wistia_async_iqbbc5gjn1 videoFoam=true playerColor=ff0000" >&nbsp;</div>';
	$timer_start = '492';
	$timer_finish = '614';
	$buy_link = 'http://singleclicksale.com/yes/?ocus=nDm7cLM9Q5MY';
	$decline_link = 'http://singleclicksale.com/no/?ocus=nDm7cLM9Q5MY';
	$decline_text = 'No thanks. I will pass on this offer and give up my savings of 60% off. If I choose to purchase this package at a later date I will pay the retail price of $238.97';
	$button 	 = "<img src='https://www.healthplus50.com/images/addtoorder.gif' width='420' height='117' border='0' />";	
	
	
	$page_title = "HealthPlus50.com - Order Step 2";
	$custom_body_class = "no-mobile-logo order-second-step-page";
	include '/home/healthplus50/public_html/inc/no-menu-header.php';
       
    /*
     *  check PayPal and replace link for button BUY and DECLINE 
     */
    /**** NEW PAYPAL ****/
    $paypal = filter_input(INPUT_GET, 'paypal');
    
    if ( $paypal == 'yes' ) {
      
        // img for button
          $img = 'https://www.healthplus50.com/images/addtoorder.gif';
          
        // product_id
          $product_id = filter_input(INPUT_GET, 'ProductId');
          
        // get data for products
          include '/home/healthplus50/public_html/order/bc2new1cuspp/paypal/paypal_configuration.php';
          
          $link_to_products = ( SANDBOX == TRUE ) ? SANDBOX_PRODUCT_FILE : LIFE_PRODUCT_FILE;
          
          include '/home/healthplus50/public_html/order/bc2new1cuspp/paypal/' . $link_to_products;
          
        // for jQuery link to complete purchase
          $link_to_purchase = '/order/bc2new1cuspp/paypal/purchase.php';
          
        // link downsell
          $decline_link = $products[$product_id]['cancel'];
          
        // link upsell
          $buy_link = $products[$product_id]['return'];   

    }
    /**** END ****/
	
?>

<div id="primary" class="inner-page responsive" style="max-width:650px;">
	<div class="text-center">
		<h1 class="order-page-red-title">Wait! Your Order is Not Complete...</h1>
		<h2 class="order-page-second-title">(Order Step 2)</h2>
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
		<div id="purchase-links" class="display-none-element">
            <?php if ( empty($paypal) ): ?>
              <a href='<?php echo $buy_link;?>'><?php echo $button; ?></a>
              <div style='margin-top:15px;'><a href='<?php echo $decline_link;?>'><span style='font-size: 11pt; line-height: 16pt;'><u><?php echo $decline_text; ?></u></span></a></div>
			<?php else: ?>
              <div id="display_button1">                
				<input id="click_button1" name="button" type="image" src="<?php echo $img; ?>" alt="Add to order" />
				<div style='margin-top:15px;'><a href='<?php echo $decline_link;?>'><span style='font-size: 11pt; line-height: 16pt;'><u><?php echo $decline_text; ?></u></span></a></div>
              </div>
            <?php endif;?>
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
			_wq.push({ "iqb": function(video) {
						
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

<!-- Google Code for CCD Frontend Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 996742966;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "cb0nCMHQoWAQtq6k2wM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/996742966/?label=cb0nCMHQoWAQtq6k2wM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<script type="text/javascript">
var sadsHost = (("https:" == document.location.protocol) ? "https://" : "http://");
document.write(unescape("%3Ciframe src='" + sadsHost + "fisico.sitescoutadserver.com/act/A06612C?' width='1' height='1' frameborder='0'%3E%3C/iframe%3E"));
</script> 
<img src="https://trends.revcontent.com/conv.php?t=Fsjs6O6p%2BbgyHKXmB9V6u2zjkZ3fqUP4fVW9uHWD32g%3D" />
<img src='https://api.content.ad/Lib/TrackConversion.aspx?aid=a023409f-0174-489e-bea4-318914b66d4e' width='1' height='1' />
<img src="https://fisicotrack.com/path/image.php?tid=&offerid=&amount=&subid=" style="display:none;" />
<script src="//ocus.s3.amazonaws.com/ocusnovo.js"></script>

<?php
// FOR NEW PAYPAL
include '/home/healthplus50/public_html/order/bc2new1cuspp/paypal/template.php';
?>

<?php include '/home/healthplus50/public_html/inc/simple-footer.php';?>
