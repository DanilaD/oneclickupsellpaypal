<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title><?php echo $page_title; ?></title>

    <!-- Bootstrap -->
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
	<link href="../../style.css" rel="stylesheet">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    <script>
  		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  		ga('create', 'UA-71269239-1', 'auto');
 	 	ga('send', 'pageview');

	</script>
	
	<script data-obct type="text/javascript">
	/** DO NOT MODIFY THIS CODE**/
	!function(_window, _document) {
	var OB_ADV_ID='00196652c6c6f82c0920e4e110ddcd1cf4';
	if (_window.obApi) { return; }
	var api = _window.obApi = function() {api.dispatch ? api.dispatch.apply(api, arguments) : api.queue.push(arguments);};api.version = '1.0';api.loaded = true;api.marketerId = OB_ADV_ID;api.queue = [];var tag = _document.createElement('script');tag.async = true;tag.src = '//amplify.outbrain.com/cp/obtp.js';tag.type = 'text/javascript';var script = _document.getElementsByTagName('script')[0];script.parentNode.insertBefore(tag, script);}(window, document);
	obApi('track', 'PAGE_VIEW');
	</script>
</head>
<body class="<?php if (isset($custom_body_class)) { echo $custom_body_class;}  ?>">
<div class="container">
<?php 
	if (isset($top_advertorial_text)) {
		
		echo '<p class="text-center top-advertorial">';
		echo $top_advertorial_text;
		echo '</p>';
	}
?>
	<header id="site-header">
		<div class="row">
			<div class="col-md-3 header-logo">
				<a href="http://healthplus50.com"><img src="../../images/logo.png"/></a>
			</div>
			<div class="top-right-mobile-menu-toggle">
				<div class="navbar-header">
				  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				  </button>
				</div>
			</div>
			<div class="col-md-9 header-navigation">
				<nav class="navbar navbar-default">
				  <div class="container-fluid">
					<!-- Collect the nav links, forms, and other content for toggling -->
					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					  <ul class="nav navbar-nav">
						<li id="home-menu-item"><a href="http://www.healthplus50.com">Home</a></li>
						<li id="program-menu-item"><a href="http://www.healthplus50.com/program.php">The Program</a></li>
						<li id="mission-menu-item"><a href="http://www.healthplus50.com/mission.php">Our Mission</a></li>
						<li id="5foods-menu-item"><a href="http://www.healthplus50.com/5foods.php">5 Foods To Never Eat</a></li>
						<li id="articles-menu-item"><a href="http://www.healthplus50.com/articles.php">Articles</a></li>
						<li id="contact-menu-item"><a href="http://www.healthplus50.com/contact.php">Contact</a></li>
						<li id="order-menu-item"><a href="https://www.healthplus50.com/order/control/">Order</a></li>
					  </ul>
					</div><!-- /.navbar-collapse -->
				  </div><!-- /.container-fluid -->
				</nav>
			</div>
		</div>
	</header><!-- /header -->
	<div id="main">
