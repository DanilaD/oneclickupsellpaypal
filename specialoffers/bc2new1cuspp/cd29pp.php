<?php
	
	$wistiaID = 'zd1hovob1q';
	$wistiaID_3chars = substr($wistiaID, 0, 3);
	$timer_start = '223';
	$timer_finish = '264';
	$buy_link_1bottle = 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6A2HVYG4GAJLE';
	$buy_link_3bottles = 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=U2WBU5X3SSMHU';
	$buy_link_6bottles = 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QCP7KZQQLVQPQ';
	$decline_link = 'http://www.healthplus50.com/specialoffers/bc2cdff/fit19pp.php';
	$decline_text = "No thanks. I will pass on the chance to enjoy ever MORE of my favorite foods without gaining weight. If I decide to purchase this at a later date I will pay the full price.";
	$button 	 = "<img src='https://www.healthplus50.com/images/addtoorder.gif' width='420' height='117' border='0' />";
	
	
	$page_title = "HealthPlus50.com - Order Step 3";
	$custom_body_class = "no-mobile-logo order-final-step-page";
	include '/home/healthplus50/public_html/inc/no-menu-header.php';
?>


<div id="primary" class="inner-page responsive" style="max-width:650px;">
	<div class="text-center">
		<h1 class="order-page-red-title">Wait! Your Order is Not Complete...</h1>
		<h2 class="order-page-second-title">(Order Step 3)</h2>
		<p>Don't click the back button or else you won't see this page again</p>
	</div>
	<div class="center-block text-center max-width-600px">
		<div>
			<script charset="ISO-8859-1" src="//fast.wistia.com/assets/external/E-v1.js" async></script>
			<div class="wistia_embed wistia_async_<?=$wistiaID?> videoFoam=true playerColor=ff0000" >&nbsp;</div>
		</div>
	</div>
	<div style="text-align: center;">
		<br/>
		(<strong>Watch the video above</strong> for a very important message about your order)
	</div>
	<br />
	<div class="text-center">
		<span id="countdown" class="red-countdown"></span>
	</div>
	<p>&nbsp;</p>
	<script type="text/javascript">
		function showIt() {
			document.getElementById("purchase-links").style.display = "block";
			document.getElementById("countdown").style.display = "none";
		}
	
		window._wq = window._wq || [];

		// target our video by the first 3 characters of the hashed ID
		_wq.push({ "<?php echo $wistiaID_3chars; ?>": function(video) {
					
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
	<a name="productoptions"></a>
    <div id="purchase-links" class="display-none-element">
		<div class="row products-grid">
			<div class="col-md-4 product-column">
				<img src="images/cd-1.jpg">
				<p><strong>1 Bottle</strong></p>
				<div class="product-price">
					<p>ONLY $29.95 per Bottle</p>
					<a href="<?php echo $buy_link_1bottle; ?>"><img src="images/addtoorder.gif"></a>
				</div>
			</div>
			<div class="col-md-4 product-column">
				<img src="images/cd-3.jpg">
				<p><strong>3 Bottles</strong></p>
				<div class="product-price">
					<p>ONLY $25.31 per Bottle</p>
					<a href="<?php echo $buy_link_3bottles; ?>"><img src="images/addtoorder.gif"></a>
				</div>
			</div>
			<div class="col-md-4 product-column">
				<img src="images/cd-6.jpg">
				<p><strong>BEST VALUE:<br /> 6 Bottles</strong></p>
				<div class="product-price">
					<p><strong>ONLY $22.65 per Bottle</strong></p>
					<a href="<?php echo $buy_link_6bottles; ?>"><img src="images/addtoorder.gif"></a>
				</div>
			</div>
		</div>
		<p>&nbsp;</p>
		<p><a href="<?php echo $decline_link; ?>"><u><?php echo $decline_text; ?></u></a></p>
    

		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<div class="faq-section-wrapper">
			<h3>Frequently Asked Questions</h3>
			<h4>1. How does Carb Defender work?</h4>
			<p>Carb Defender blocks an enzyme known as alpha-amylase. Alpha-amylase is responsible for the digestion and absorption of carbs. By blocking this enzyme, Carb Defender prevents your body from digesting and absorbing all of the carbs you eat.</p>
			<p>Carb Defender's patented ingredient has undergone <strong>more than a dozen clinical studies proving it to be a safe and effective weight management formula for people who love carbs</strong>.</p>
			<p>In fact, in a recent clinical trial in Italy (a country where carb-heavy meals are typically eaten) demonstrated an average loss of 6.45 pounds and 10.45% body fat after only 30 days of taking Carb Defender's active ingredient. In contrast, those taking a placebo lost less than a pound. That's more than a <strong>600% boost in fat loss</strong>.</p>
			<h4>2. What are the ingredients in Carb Defender?</h4>
			<p><img src="images/label.jpg" border="0" /></p>
			<h4>3. What is your guarantee?</h4>
			<p>When it comes to Carb Defender, your satisfaction is our top priority. That's why all orders are backed by a 90-day, full-money-back guarantee (less any S&H). If you're not satisfied with your purchase - for any reason whatsoever - simply contact us to request a prompt and courteous refund.</p>
			<h4>4. How do I take Carb Defender?</h4>
			<p>For best results, take one (2) capsules with a glass of water before each meal containing complex carbohydrates or starches.</p>
			<h4>5. Does this product contain caffeine or any other stimulants?</h4>
			<p>No. This product is both caffeine and stimulant-free.</p>
			<h4>6. Are there any allergy concerns with this product?</h4>
			<p>Carb Defender is all natural and free of gluten, dairy, wheat, soy, egg, fish, crustacean shellfish, tree nuts, and peanuts. Furthermore, it contains NO sugar and NO artificial sweeteners, flavors, colors or preservatives. Best of all, it’s 100% vegetarian and vegan friendly. If you have any concerns you should consult your physicians before taking this (or any other) supplement.</p>
			<h4>7. If I have a medical condition, can I take this product?</h4>
			<p>Carb Defender is an all-natural formula that is considered safe and extremely effective for nearly everyone. That said, if you take prescription medications or have a medical condition, we recommend that you show a bottle of Carb Defender to your physician for proper approval. You can always return it later with our 90-day money-back-guarantee. However, do not take Carb Defender if you are pregnant, nursing or under the age of 18.</p>
			<h4>8. Will anything else be sent to me (or billed to me) after I order?</h4>
			<p>No. This is NOT an auto-ship program. You only get shipped what you order today and nothing more.</p>
		</div>
		<p>&nbsp;</p>
		<h3><a href="#productoptions"><u>Click (or tap) here to take advantage of this special offer...</u></a></h3>
	</div>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<div class="text-center">
		<p>Disclaimer: These statements have not been evaluated by the Food and Drug Administration. This product is not intended to diagnose, treat, cure, or prevent any disease. This information is not intended to be a substitute or replacement for any medical treatment. Please seek the advice of a healthcare professional for your specific health concerns. Individual results may vary.</p>
	</div>
</div>	
<?php include '/home/healthplus50/public_html/inc/simple-footer.php'; ?>  	