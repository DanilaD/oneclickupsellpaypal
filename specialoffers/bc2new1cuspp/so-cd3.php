==<?php
  		
	$page_title = "HealthPlus50.com - Special Offer";
	$top_progress_bar_image = "../../images/progressbar600step2.png";
	
	$buy_link = 'http://singleclicksale.com/yes/?ocus=gpcOT3Yuw8Bw';
	$decline_link = 'http://singleclicksale.com/no/?ocus=gpcOT3Yuw8Bw';
	
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
			

<div id="primary" class="inner-page responsive" style="max-width:650px; font-size: 13pt;"">
	<h1 style="text-align: center; color: red; margin-bottom: 10px;">WAIT... Your Order Is Not Complete!</h1>
	<p style="text-align: center; font-weight: bold;">Do not hit the 'Back' button as this may cause multiple charges on your card<br /><br /></p>
	<h3>How About a 3 Month Supply at 30% Off?</h3>
	<p>Because this is a new deal we can still offer you a 3 month supply at a huge discount.</p>
	<p>You save $27.06 and get an entire 3 month supply for 30% off.</p>
	<p>But this is also a one-time only offer.</p>
	<p>Once you leave this page, we can not show you this again - no matter what.</p>
	<p>So, we would like to give you one last chance to get our best discount on Carb Defender</p>
	<p><strong>Click the yes button and get a 3 bottle supply of Carb Defender added to your order for just $62.89</strong>:<br /><br /></p>
    <?php if ( empty($paypal) ): ?>
        <p style="text-align: center;"><a href="<?php echo $buy_link;?>"><img src="/images/addtoorder.gif" width="420" height="117" border="0" /></a></p>
        <p style="text-align: center;">or...</p>
        <p style="text-align: center;"><a href="<?php echo $decline_link;?>" style="text-decoration: underline;">No thanks. I will pass on this one-time-offer and give up my $50 savings. If I choose to purchase a 6 bottle supply down the road I will gladly pay the full price</a>.</p>
    <?php else: ?>
      <div id="display_button1">                
        <p style="text-align: center;"><input id="click_button1" name="button" type="image" src="<?php echo $img; ?>" alt="Add to order" /></p>
        <p style="text-align: center;">or...</p>
        <p style="text-align: center;"><a href="<?php echo $decline_link;?>" style="text-decoration: underline;">No thanks. I will pass on this one-time-offer and give up my $50 savings. If I choose to purchase a 6 bottle supply down the road I will gladly pay the full price</a></p>
      </div>
    <?php endif;?>
    <p>&nbsp;</p>
	<p><img src="/images/guarantee.jpg" style="float: left; margin-right: 20px;" /> <strong>Guarantee</strong></p>
	<p>You are fully protected by our iron-clad money-back guarantee. If you don't see results in 90 days (or you're not satisfied for any reason whatsoever), just let us know and we'll send you a prompt 100% refund.</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p style="text-align: center;">*** I understand I will NEVER see this page ever again ***</p>
</div>
<script src="//ocus.s3.amazonaws.com/ocusnovo.js"></script>
<?php
// FOR NEW PAYPAL
include '/home/healthplus50/public_html/order/bc2new1cuspp/paypal/template.php';
?>
<?php include '/home/healthplus50/public_html/inc/simple-footer.php';?>  