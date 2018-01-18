	</div><!-- /#main -->
	<div style="padding: 50px;">&nbsp;</div>
	<footer id="site-footer">
		<p>&copy; <?php echo date('Y'); ?> HealthPlus50.com I All rights reserved.</p>
		<p>We take your privacy very seriously. You can read our entire <a href="https://www.healthplus50.com/privacy.php">privacy policy</a> here.</p>
		<ul class="footer-menu">
			<li><a href="https://www.healthplus50.com/index.php">Home</a></li>
			<li><a href="https://www.healthplus50.com/program.php">The Program</a></li>
			<li><a href="https://www.healthplus50.com/mission.php">Our Mission</a></li>
			<li><a href="https://www.healthplus50.com/5foods.php">5 Foods To Never Eat</a></li>
			<li><a href="https://www.healthplus50.com/contact.php">Contact</a></li>
			<li><a href="https://www.healthplus50.com/order/control/">Order</a></li>
			<li><a href="https://www.healthplus50.com/terms.php">Terms of Service</a></li>
			<li><a href="https://www.healthplus50.com/returns.php">Return Policy</a></li>
		</ul>
		<p style="font-size: 13pt;"><strong>*Disclaimer</strong>: This information is not intended to be a substitute or replacement for any medical treatment. Please seek the advice of a healthcare professional for your specific health concerns. Individual results may vary.</p>
		<p>Fisico Inc.<br />581 Boylston St. Ste. 806<br />Boston, MA 02116</p>
		
	</footer><!-- /footer -->
</div><!-- /container -->

<script>
jQuery(document).ready(function ($) {
	$(".header-navigation .nav li").removeClass("active");
	$('#'+ current_page).addClass('active');
	
	$('.navbar-toggle').bind('click', function() {
		$('.navbar-collapse').toggleClass('in');
		$(this).toggleClass('collapsed');
	});
});
</script>
<!-- Google Code for Remarketing Tag -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 996742966;
var google_custom_params = window.google_tag_params;
var google_remarketing_only = true;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/996742966/?value=0&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<script type="application/javascript">(function(w,d,t,r,u){w[u]=w[u]||[];w[u].push({'projectId':'10000','properties':{'pixelId':'10017088'}});var s=d.createElement(t);s.src=r;s.async=true;s.onload=s.onreadystatechange=function(){var y,rs=this.readyState,c=w[u];if(rs&&rs!="complete"&&rs!="loaded"){return}try{y=YAHOO.ywa.I13N.fireBeacon;w[u]=[];w[u].push=function(p){y([p])};y(c)}catch(e){}};var scr=d.getElementsByTagName(t)[0],par=scr.parentNode;par.insertBefore(s,scr)})(window,document,"script","https://s.yimg.com/wi/ytc.js","dotq");</script>

	<!-- xVerify Javascript code goes here -->
 

	<link rel="stylesheet" type="text/css"  href="https://www.xverify.com/css/ui_tooltip_style.css"  />


	<script type="text/javascript" src="https://www.xverify.com/sharedjs/jquery-ui.min.js"></script>
	<script type="text/javascript" src="https://www.xverify.com/js/clients/fisicoinc/client.js"></script>
	<script type="text/javascript" src="https://www.xverify.com/sharedjs/xverify.ui.js"></script>

	<script type="text/javascript">
		jQuery(document).ready(function($){
			$('.contact-information-section input[type="email"]').addClass('xverify_email');
			bindRequiredInputFields();
			if($('.contact-information-section input[type="email"]').val().length>0){
				jQuery('.contact-information-section input[type="email"]').trigger('change');

				if ($(".xverify-ui-tooltip-error").length) {
					$(".btn.submit-order").prop('disabled', false);
					$("input.xverify_email").parent().parent().addClass('has-error');
					$(".btn.submit-order").addClass('disabled');
				} else {
					$("input.xverify_email").parent().parent().removeClass('has-error');
				}
			}
		});
	</script>


	<!-- xVerify Javascript code ends here  -->
</body>
</html>