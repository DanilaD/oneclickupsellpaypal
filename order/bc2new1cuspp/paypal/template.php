<?php 
echo "
<style>
  #waiting {
      display:none;
      position: fixed;
      z-index: 10;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
      width: auto;
      height: auto;
      max-width: 99%;
      max-height: 99%;
      margin: auto;
      box-shadow: 0 0 20px #000, 0 0 0 1000px rgba(210,210,210,.4);
  }
 </style>
 
 <img id='waiting' src='http://sandbox.cruisecontroldiet.com/order/new/paypal/please_wait.gif' alt='Please wait...' tabindex='0'/>
 
 <script>
    $(document).ready(function(){
        $('#click_button1').click(function(){
            $('#waiting').fadeIn(1000);
            $('#display_button1').fadeOut(1000);
            var product_id = '" . $product_id . "';
            var url = '" . $buy_link . "';
            $.post('" . $link_to_purchase . "', {product_id: product_id}, function(result){
                $(location).attr('href', url);
            });
        });
    });
</script>
";
?>