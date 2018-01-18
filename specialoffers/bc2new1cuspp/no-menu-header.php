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
<?php 
	if (isset($top_progress_bar_image)) {
		$top_progress_bar_image_src = $top_progress_bar_image;
	}else{
		$top_progress_bar_image_src = "../../images/progressbar600step2.png";
	}
	
	if (isset($custom_body_class) ) {
		$body_class = $custom_body_class;
	}else{
		$body_class = '';
	}
?>
<body class="<?php echo $body_class; ?>">
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
				<img src="../../images/logo.png"/>
			</div>
			<div class="col-md-9 text-center">
				<img src="<?php echo $top_progress_bar_image_src;?>"/>
			</div>
		</div>
	</header><!-- /header -->
	<div id="main">
