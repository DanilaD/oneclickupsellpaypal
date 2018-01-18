 <?php

	$wistiaID = 'm481xh58t2';
	$wistiaID_3chars = substr($wistiaID, 0, 3); 
	$timer_start = '296';
	$timer_finish = '364';
	$buy_link = 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CEBRU3XDR2B8E';
	$decline_link = 'http://www.healthplus50.com/specialoffers/bc2cdff/ordercomplete.php';
	$decline_text = "No thanks. I will pass on this offer and give up my savings of 50% off. If I choose to purchase this package at a later date I will pay the retail price of $39.99";
	$button 	 = "<img src='https://www.healthplus50.com/images/addtoorder.gif' width='420' height='117' border='0' />";
	
	
	$page_title = "HealthPlus50.com - Final Step";
	$custom_body_class = "no-mobile-logo order-final-step-page";
	include '/home/healthplus50/public_html/inc/no-menu-header.php';
?>

<div id="primary" class="inner-page responsive" style="max-width:650px;">
	<div class="text-center">
		<h1 class="order-page-red-title">Wait! Your Order is Not Complete...</h1>
		<h2 class="order-page-second-title">(Final Step)</h2>
		<p>Don't click the back button or else you won't see this page again</p>
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
		<span id="countdown" class="red-countdown"></span>
		<div id="purchase-links" class="display-none-element">
			<a href='<?php echo $buy_link;?>'><?php echo $button; ?></a>
			<div style='margin-top:15px;'><a href='<?php echo $decline_link;?>'><span style='font-size: 11pt; line-height: 16pt;'><u><?php echo $decline_text; ?></u></span></a></div>
		</div>
	</div>

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
</div>	
<?php include '/home/healthplus50/public_html/inc/simple-footer.php';?>  	