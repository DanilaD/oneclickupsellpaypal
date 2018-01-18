<?php
  		
	$page_title = "HealthPlus50.com - Special Offer";
	$top_progress_bar_image = "../../images/progressbar600step2.png";
	
	$buy_link = 'http://singleclicksale.com/yes/?ocus=k3KwYkFvbp75';
	$decline_link = 'http://singleclicksale.com/no/?ocus=k3KwYkFvbp75';

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
	<h3>How Would You Like to Save an Extra $50 on Carb Defender?</h3>
	<p>Congratulations for taking yet another BIG step to improving your health. And because you've chosen to take action today (and as a first-time customer only)... we'd like to offer you an additional $50 off Carb Defender.</p>
	<p>Here's the deal...</p>
	<p>When you add another 6 bottles to your order of Carb Defender, we can skip the cost of fulfillment and processing for each bottle individually. This means we can offer you this clinically proven weight management formula for people who love carbs at the best possible price.</p>
	<p>This is a limited offer and we're not showing this to everyone. Instead, we're testing this idea with a select few customers and letting you <strong>take $50 off the original price of Carb Defender.</strong>.</p>
	<p>But, there is a catch...</p>
	<p><strong>This is the ONLY time you will see this offer... ever! It's a one-time opportunity</strong>.</p>
	<p>The best part?</p>
	<p>You can try Carb Defender and get the $50 discount... AND if you are not DELIGHTED with your results, you can still contact us for a full 100% refund - no questions asked.</p>
	<p>So, don't miss out on this incredible deal. Purchase a 6 bottle supply and save $50 off the regular price!</p>
	<p><strong>By clicking the button below, you will get a 6 bottle supply of Carb Defender added to your order for only $107.82 (Limit 1 Per Customer):</strong>:<br /><br /></p> 
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