+function ($) {
  'use strict';

  // VALIDATOR CLASS DEFINITION
  // ==========================

  var Validator = function (element, options) {
    this.$element = $(element)
    this.options  = options

    options.errors = $.extend({}, Validator.DEFAULTS.errors, options.errors)

    for (var custom in options.custom) {
      if (!options.errors[custom]) throw new Error('Missing default error message for custom validator: ' + custom)
    }

    $.extend(Validator.VALIDATORS, options.custom)

    this.$element.attr('novalidate', true) // disable automatic native validation
    this.toggleSubmit()

    this.$element.on('input.bs.validator change.bs.validator focusout.bs.validator', $.proxy(this.validateInput, this))
    this.$element.on('submit.bs.validator', $.proxy(this.onSubmit, this))

    this.$element.find('[data-match]').each(function () {
      var $this  = $(this)
      var target = $this.data('match')

      $(target).on('input.bs.validator', function (e) {
        $this.val() && $this.trigger('input.bs.validator')
      })
    })
  }

  Validator.INPUT_SELECTOR = ':input:not([type="submit"], button):enabled:visible'

  Validator.DEFAULTS = {
    delay: 500,
    html: false,
    disable: true,
    custom: {},
    errors: {
      match: 'Does not match',
      minlength: 'Not long enough',
	  maxlength: 'Too long',
	  matchlength: ''
    },
    feedback: {
      success: 'glyphicon-ok',
      error: 'glyphicon-remove'
    }
  }

  Validator.VALIDATORS = {
    'native': function ($el) {
      var el = $el[0]
      return el.checkValidity ? el.checkValidity() : true
    },
    'match': function ($el) {
      var target = $el.data('match')
      return !$el.val() || $el.val() === $(target).val()
    },
    'minlength': function ($el) {
		var minlength = $el.data('minlength')
		return !$el.val() || $el.val().length >= minlength
    },
	'maxlength': function ($el) {
		var maxlength = $el.data('maxlength')
		return !$el.val() || $el.val().length <= maxlength
    },
	'matchlength': function ($el) {
		var matchlength = $el.attr('data-matchlength')		
		return !$el.val() || $el.val().length == matchlength
    }
  }

  Validator.prototype.validateInput = function (e) {
    var $el        = $(e.target)
    var prevErrors = $el.data('bs.validator.errors')
    var errors

    if ($el.is('[type="radio"]')) $el = this.$element.find('input[name="' + $el.attr('name') + '"]')

    this.$element.trigger(e = $.Event('validate.bs.validator', {relatedTarget: $el[0]}))

    if (e.isDefaultPrevented()) return

    var self = this

    this.runValidators($el).done(function (errors) {
      $el.data('bs.validator.errors', errors)

      errors.length ? self.showErrors($el) : self.clearErrors($el)

      if (!prevErrors || errors.toString() !== prevErrors.toString()) {
        e = errors.length
          ? $.Event('invalid.bs.validator', {relatedTarget: $el[0], detail: errors})
          : $.Event('valid.bs.validator', {relatedTarget: $el[0], detail: prevErrors})

        self.$element.trigger(e)
      }

      self.toggleSubmit()

      self.$element.trigger($.Event('validated.bs.validator', {relatedTarget: $el[0]}))
    })
  }


  Validator.prototype.runValidators = function ($el) {
    var errors   = []
    var deferred = $.Deferred()
    var options  = this.options

    $el.data('bs.validator.deferred') && $el.data('bs.validator.deferred').reject()
    $el.data('bs.validator.deferred', deferred)

    function getErrorMessage(key) {
      return $el.data(key + '-error')
        || $el.data('error')
        || key == 'native' && $el[0].validationMessage
        || options.errors[key]
    }

    $.each(Validator.VALIDATORS, $.proxy(function (key, validator) {
      if (($el.data(key) || key == 'native') && !validator.call(this, $el)) {
        var error = getErrorMessage(key)
        !~errors.indexOf(error) && errors.push(error)
      }
    }, this))

    if (!errors.length && $el.val() && $el.data('remote')) {
      this.defer($el, function () {
        var data = {}
        data[$el.attr('name')] = $el.val()
        $.get($el.data('remote'), data)
          .fail(function (jqXHR, textStatus, error) { errors.push(getErrorMessage('remote') || error) })
          .always(function () { deferred.resolve(errors)})
      })
    } else deferred.resolve(errors)

    return deferred.promise()
  }

  Validator.prototype.validate = function () {
    var delay = this.options.delay

    this.options.delay = 0
    this.$element.find(Validator.INPUT_SELECTOR).trigger('input.bs.validator')
    this.options.delay = delay

    return this
  }

  Validator.prototype.showErrors = function ($el) {
    var method = this.options.html ? 'html' : 'text'

    this.defer($el, function () {
      var $group = $el.closest('.form-group')
      var $block = $group.find('.help-block.with-errors')
      var $feedback = $group.find('.form-control-feedback')
      var errors = $el.data('bs.validator.errors')

      if (!errors.length) return

      errors = $('<ul/>')
        .addClass('list-unstyled')
        .append($.map(errors, function (error) { return $('<li/>')[method](error) }))

      $block.data('bs.validator.originalContent') === undefined && $block.data('bs.validator.originalContent', $block.html())
      $block.empty().append(errors)
      $group.addClass('has-error')

      $feedback.length
        && $feedback.removeClass(this.options.feedback.success)
        && $feedback.addClass(this.options.feedback.error)
        && $group.removeClass('has-success')
    })
  }

  Validator.prototype.clearErrors = function ($el) {
    var $group = $el.closest('.form-group')
    var $block = $group.find('.help-block.with-errors')
    var $feedback = $group.find('.form-control-feedback')

    $block.html($block.data('bs.validator.originalContent'))
    $group.removeClass('has-error')

    $feedback.length
      && $feedback.removeClass(this.options.feedback.error)
      && $feedback.addClass(this.options.feedback.success)
      && $group.addClass('has-success')
  }

  Validator.prototype.hasErrors = function () {
    function fieldErrors() {
      return !!($(this).data('bs.validator.errors') || []).length
    }

    return !!this.$element.find(Validator.INPUT_SELECTOR).filter(fieldErrors).length
  }

  Validator.prototype.isIncomplete = function () {
    function fieldIncomplete() {
      return this.type === 'checkbox' ? !this.checked                                   :
             this.type === 'radio'    ? !$('[name="' + this.name + '"]:checked').length :
                                        $.trim(this.value) === ''
    }

    return !!this.$element.find(Validator.INPUT_SELECTOR).filter('[required]').filter(fieldIncomplete).length
  }

  
  Validator.prototype.check_expiration_date = function () {
	
		var month_expiration_field = $('.change-payment-method-section select[name="ExpirationMonth"]');
		var year_expiration_field  = $('.change-payment-method-section select[name="ExpirationYear"]');
		
		var creditcardexpiration_value = month_expiration_field.data('creditcardexpiration');
		var creditcardexpiration_value_array = creditcardexpiration_value.split("/");
		var creditcardexpiration_month = parseInt(creditcardexpiration_value_array[0]);
		var creditcardexpiration_year = parseInt(creditcardexpiration_value_array[1]);

		var creditcardexpiration_month_value = parseInt(month_expiration_field.val());

		var creditcardexpiration_year_value = parseInt(year_expiration_field.val());


		var return_response = ( (creditcardexpiration_month_value >= creditcardexpiration_month && creditcardexpiration_year_value >= creditcardexpiration_year) || (creditcardexpiration_year_value > creditcardexpiration_year) );
		
		if (return_response) {
			
			month_expiration_field.parent().parent().removeClass('has-error');
			year_expiration_field.parent().parent().removeClass('has-error');
			month_expiration_field.parent().parent().find('.help-block.with-errors').empty();
			year_expiration_field.parent().parent().find('.help-block.with-errors').empty();
			
			return "true";
			
		} else {
			
			month_expiration_field.parent().parent().addClass('has-error');
			year_expiration_field.parent().parent().addClass('has-error');
			month_expiration_field.parent().parent().find('.help-block.with-errors').html("Expiration date should be greater than today's date");
			year_expiration_field.parent().parent().find('.help-block.with-errors').html("Expiration date should be greater than today's date");
			
			return "false";
			
		}
	
	}
  
  Validator.prototype.onSubmit = function (e) {

    this.validate()
	var payment_method_value = $('#orderForm input[name=usePaypal]:checked').val();
	if(payment_method_value === '1') {
		var check_expiration_date_response = "true";
	} else {
		var check_expiration_date_response = this.check_expiration_date();
	}
	
	var xverify_response = "true";
	
	if ($(".xverify-ui-tooltip-error").length) {
		
		xverify_response = "false";
		$("input.xverify_email").parent().parent().addClass('has-error');
		$(".btn.submit-order").addClass('disabled');
		
	}
	
    if ( this.isIncomplete() || this.hasErrors() || check_expiration_date_response === "false" || xverify_response === "false" ) { 
		e.preventDefault();
		$(".help-block.with-errors").show();
		$('html, body').animate({
			scrollTop: $('.form-group.has-error:visible:first').offset().top - 30
		}, 1000);
		
	} else {
		
		$(".btn.submit-order").prop('disabled', true);
		
	}
  }

  Validator.prototype.toggleSubmit = function () {
    if(!this.options.disable) return

    var $btn = $('button[type="submit"], input[type="submit"], input[type="image"]')
      .filter('[form="' + this.$element.attr('id') + '"]')
      .add(this.$element.find('input[type="submit"], button[type="submit"]'))

    $btn.toggleClass('disabled', this.isIncomplete() || this.hasErrors())
  }

  Validator.prototype.defer = function ($el, callback) {
    callback = $.proxy(callback, this)
    if (!this.options.delay) return callback()
    window.clearTimeout($el.data('bs.validator.timeout'))
    $el.data('bs.validator.timeout', window.setTimeout(callback, this.options.delay))
  }

  Validator.prototype.destroy = function () {
    this.$element
      .removeAttr('novalidate')
      .removeData('bs.validator')
      .off('.bs.validator')

    this.$element.find(Validator.INPUT_SELECTOR)
      .off('.bs.validator')
      .removeData(['bs.validator.errors', 'bs.validator.deferred'])
      .each(function () {
        var $this = $(this)
        var timeout = $this.data('bs.validator.timeout')
        window.clearTimeout(timeout) && $this.removeData('bs.validator.timeout')
      })

    this.$element.find('.help-block.with-errors').each(function () {
      var $this = $(this)
      var originalContent = $this.data('bs.validator.originalContent')

      $this
        .removeData('bs.validator.originalContent')
        .html(originalContent)
    })

    this.$element.find('input[type="submit"], button[type="submit"]').removeClass('disabled')
	
    this.$element.find('.has-error').removeClass('has-error')
	

    return this
  }

  // VALIDATOR PLUGIN DEFINITION
  // ===========================


  function Plugin(option) {
    return this.each(function () {
      var $this   = $(this)
      var options = $.extend({}, Validator.DEFAULTS, $this.data(), typeof option == 'object' && option)
      var data    = $this.data('bs.validator')

      if (!data && option == 'destroy') return
      if (!data) $this.data('bs.validator', (data = new Validator(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  var old = $.fn.validator

  $.fn.validator             = Plugin
  $.fn.validator.Constructor = Validator


  // VALIDATOR NO CONFLICT
  // =====================

  $.fn.validator.noConflict = function () {
    $.fn.validator = old
    return this
  }


  // VALIDATOR DATA-API
  // ==================

  $(window).on('load', function () {
    $('form[data-toggle="validator"]').each(function () {
      var $form = $(this)
      Plugin.call($form, $form.data())
    })
  })

}(jQuery);



jQuery(document).ready(function($) {

  // Disable autofill for CC fields 
  
  $('#orderForm input[name=CardNumber], #orderForm input[name=SecurityCode]').attr("autocomplete","off");
  
  
  
  // Payment Methods
  // =====================
  
	var paypalSwitchValue = $('#orderForm input[name=usePaypal]:checked').val();
	if (paypalSwitchValue === '1'){
		$('.payment-methods-fields .form-group:not(:first-child)').hide();
	}else if (paypalSwitchValue === '0'){
		$('.payment-methods-fields .form-group:not(:first-child)').show();
	}
	
	$('#orderForm input[name=usePaypal]').click(function() {
		var paypalSwitchValue = $('#orderForm input[name=usePaypal]:checked').val();
		if (paypalSwitchValue === '1'){
			$('.payment-methods-fields .form-group:not(:first-child)').hide();
		}else if (paypalSwitchValue === '0'){
			$('.payment-methods-fields .form-group:not(:first-child)').show();
		}
	});

  // Show/Hide Shipping Address and auto fill the Shipping Fields Depending by Contact Fields
  // =====================
  
	var shippingCheckElement 	= $('#shipping_check');
	var billingAddressOne 		= '.billing-address-section input[name="StreetAddress1"]';
	var billingAddressTwo 		= '.billing-address-section input[name="StreetAddress2"]';
	var billingCity				= '.billing-address-section input[name="City"]';
	var billingZip				= '.billing-address-section input[name="PostalCode"]';
	var billingState 			= '.billing-address-section select[name="State"]';
	var billingCountry			= '.billing-address-section select[name="Country"]';
	
	var shippingAddressOne 		= '.shipping-address-section input[name="Address2Street1"]';
	var shippingAddressTwo 		= '.shipping-address-section input[name="Address2Street2"]';
	var shippingCity 			= '.shipping-address-section input[name="City2"]';
	var shippingZip				= '.shipping-address-section input[name="PostalCode2"]';
	var shippingState			= '.shipping-address-section select[name="State2"]';
	var shippingCountry			= '.shipping-address-section select[name="Country2"]';
	

	function changeFieldValue(givenField,receiverField,checkValue){
		
		$(givenField).change(function() {
			givenFieldValue = $(this).val();
			$(receiverField).val(givenFieldValue);
			$(receiverField).trigger("change");
		});
		
		if( (checkValue != undefined) ||(checkValue != null) ){
			givenFieldValue = $(givenField).val();
			$(receiverField).val(givenFieldValue);
		}
		
		$(receiverField).trigger("change");
	}
	
	
	if ($(shippingCheckElement).is(':checked')){
		
		$('.shipping-address-section').hide();
		

		
		changeFieldValue(billingAddressOne,shippingAddressOne);
		changeFieldValue(billingAddressTwo,shippingAddressTwo);
		changeFieldValue(billingCity,shippingCity);
		changeFieldValue(billingZip,shippingZip);
		changeFieldValue(billingState,shippingState);
		changeFieldValue(billingCountry,shippingCountry);

	} else {
		$('.shipping-address-section').show();
	}
	
	$(shippingCheckElement).click(function() {
		
		if ($(this).is(':checked')){
			
			$('.shipping-address-section').hide();
			
			changeFieldValue(billingAddressOne,shippingAddressOne,1);
			changeFieldValue(billingAddressTwo,shippingAddressTwo,1);
			changeFieldValue(billingCity,shippingCity,1);
			changeFieldValue(billingZip,shippingZip,1);
			changeFieldValue(billingState,shippingState,1);
			changeFieldValue(billingCountry,shippingCountry,1);

		} else {
			$('.shipping-address-section').show();
			
			$(billingAddressOne + "," + billingAddressTwo + "," + billingState).off("change");
			
		}
	});
	
	
	//Auto fill the prices
    // =====================
	
	
	var shippingCity     = '.shipping-address-section input[name="City2"]';
	var shippingState    = '.shipping-address-section select[name="State2"]';
	var shippingCountry  = '.shipping-address-section select[name="Country2"]';
	
	function changePrices(allPrices){
		
		$('.product-price-value').text("$" + allPrices["price"]);
		$('.shipping-fee-value').text("$" + allPrices["shipping"].toFixed(2));
		$('.tax-fee-value').text("$" + allPrices["tax"].toFixed(2));
		$('.total-price-value').text("$" + allPrices["totalPrice"].toFixed(2));
		
	}
	
	function getShippingFieldsValue(city,state,country){
		
		result = {};
		
		result.city     = $(city).val();
		result.state    = $(state + " :selected").val().toUpperCase();
		result.country  = $(country).val();
		
		return result;
	}
	
	var shippingFieldsValues  = getShippingFieldsValue(shippingCity, shippingState, shippingCountry);
	var allPrices = ccdCalculatePrices(shippingFieldsValues["city"], shippingFieldsValues["state"], shippingFieldsValues["country"]);
	
	changePrices(allPrices);	

	$('.shipping-address-section input[name="City2"]' ).change(function() {
		
		var shippingFieldsValues  = getShippingFieldsValue(shippingCity, shippingState, shippingCountry);
		var allPrices = ccdCalculatePrices(shippingFieldsValues["city"], shippingFieldsValues["state"], shippingFieldsValues["country"]);
		
		changePrices(allPrices);
		
	});

	$('.shipping-address-section select[name="State2"]' ).change(function() {
		
		var shippingFieldsValues  = getShippingFieldsValue(shippingCity, shippingState, shippingCountry);
		var allPrices = ccdCalculatePrices(shippingFieldsValues["city"], shippingFieldsValues["state"], shippingFieldsValues["country"]);
		
		changePrices(allPrices);	
		
	});
	
	$('.shipping-address-section select[name="Country2"]' ).change(function() {
		
		var shippingFieldsValues  = getShippingFieldsValue(shippingCity, shippingState, shippingCountry);
		var allPrices = ccdCalculatePrices(shippingFieldsValues["city"], shippingFieldsValues["state"], shippingFieldsValues["country"]);
		
		changePrices(allPrices);
		
	});
	
	
	//Disable State Select if the Country is not US and add/remove require start depends by selected country
    // =====================
	
	$('.billing-address-section select[name="Country"]' ).change(function() {
		
		if (($('.billing-address-section select[name="Country"]').val()) === "United States"){
		
			$('.billing-address-section select[name="State"]').attr('disabled', false).prop('required',true);
			$('.billing-address-section select[name="State"]').parent().parent().find('.order-form-field-label').append(' *');
			
		}else{
			$('.billing-address-section select[name="State"]').attr('disabled', true).prop('required',false);
			$('.billing-address-section select[name="State"]').parent().parent().removeClass("has-error");
			$('.billing-address-section select[name="State"]').parent().parent().find('.help-block.with-errors').empty();
			var billing_address_section_state_label = $('.billing-address-section select[name="State"]').parent().parent().find('.order-form-field-label');
			$(billing_address_section_state_label).html($(billing_address_section_state_label).html().split("*").join(""));
		}
	});
	
	$( '.shipping-address-section select[name="Country2"]' ).change(function() {
		
		if (($('.shipping-address-section select[name="Country2"]').val()) === "United States"){
		
			$('.shipping-address-section select[name="State2"]').attr('disabled', false).prop('required',true);
			$('.shipping-address-section select[name="State2"]').parent().parent().find('.order-form-field-label').append(' *');
			
		}else{
			$('.shipping-address-section select[name="State2"]').attr('disabled', true).prop('required',false);
			$('.shipping-address-section select[name="State2"]').parent().parent().removeClass("has-error");
			$('.shipping-address-section select[name="State2"]').parent().parent().find('.help-block.with-errors').empty();
			var shipping_address_section_state_label = $('.shipping-address-section select[name="State2"]').parent().parent().find('.order-form-field-label');
			$(shipping_address_section_state_label).html($(shipping_address_section_state_label).html().split("*").join(""));
		}
	});

	
	if (($('.billing-address-section select[name="Country"]').val()) === "United States"){
	
		$('.billing-address-section select[name="State"]').attr('disabled', false).prop('required',true);
		$('.billing-address-section select[name="State"]').parent().parent().find('.order-form-field-label').append(' *');
		
	}else{
		$('.billing-address-section select[name="State"]').attr('disabled', true).prop('required',false);
		var billing_address_section_state_label = $('.billing-address-section select[name="State"]').parent().parent().find('.order-form-field-label');
		$(billing_address_section_state_label).html($(billing_address_section_state_label).html().split("*").join(""));
	}
	
	if (($('.shipping-address-section select[name="Country2"]').val()) === "United States"){
	
		$('.shipping-address-section select[name="State2"]').attr('disabled', false).prop('required',true);
		$('.shipping-address-section select[name="State2"]').parent().parent().find('.order-form-field-label').append(' *');
		
	}else{
		$('.shipping-address-section select[name="State2"]').attr('disabled', true).prop('required',false);
		var shipping_address_section_state_label = $('.shipping-address-section select[name="State2"]').parent().parent().find('.order-form-field-label');
		$(shipping_address_section_state_label).html($(shipping_address_section_state_label).html().split("*").join(""));
	}
		
	//Adding some validation for Card Number and Security Code
    // =====================
	

	
	$('.change-payment-method-section select[name="CardType"]' ).change(function() {
		
		var cards_16_digits_array = ["Discover", "MasterCard", "Visa"];
		var cards_15_digits_array = ["American Express"];
		
		var cardTypeValue = $(this).val();
		
		//VISA, Discover, and Mastercard cards should have 16 digits
		//VISA, Mastercard, and Discover should have 3 digit CVV codes
		
		if(cards_16_digits_array.indexOf(cardTypeValue) != -1) {
			
			$('.change-payment-method-section input[name="CardNumber"]').removeData('matchlength-error').attr({'data-matchlength':16, 'data-matchlength-error':'Card number must be 16 digits'});
			$('.change-payment-method-section input[name="SecurityCode"]').removeData('matchlength-error').attr({'data-matchlength':3, 'data-matchlength-error':'CVC must be 3 digits'});
			
		}
		
		
		//American express cards should have 15 digits
		//American Express should have 4 digit CVV codes
		
		if(cards_15_digits_array.indexOf(cardTypeValue) != -1) {
			
			$('.change-payment-method-section input[name="CardNumber"]').removeData('matchlength-error').attr({'data-matchlength':15,'data-matchlength-error':'Card number must be 15 digits'});
			$('.change-payment-method-section input[name="SecurityCode"]').removeData('matchlength-error').attr({'data-matchlength':4, 'data-matchlength-error' : 'CVC must be 4 digits'});
			
		}
		
		//Disable real time check for CC fields
		$('.payment-methods-fields').find(".help-block.with-errors").hide();
		
	});
	
	
	//Expiration date should be greater than today's date

		
	var currentDate = new Date();
	var currentMonth = ("0" + (currentDate.getMonth() + 1)).slice(-2);
	var currentYear = currentDate.getFullYear();
	var data_creditcardexpiration = currentMonth + "/" + currentYear;
	
	
	$('.change-payment-method-section select[name="ExpirationMonth"]').attr('data-creditcardexpiration',data_creditcardexpiration);	
	$('.change-payment-method-section select[name="ExpirationYear"]').attr('data-creditcardexpiration',data_creditcardexpiration);	
	$('.contact-information-section input[type="email"]').attr('data-error', "Invalid Email");	
		
	
	$('.change-payment-method-section select[name="ExpirationMonth"]').add('.change-payment-method-section select[name="ExpirationYear"]').change(function() {
		
		var month_expiration_field = $('.change-payment-method-section select[name="ExpirationMonth"]');
		var year_expiration_field  = $('.change-payment-method-section select[name="ExpirationYear"]');
		
		var creditcardexpiration_value = month_expiration_field.data('creditcardexpiration');
		var creditcardexpiration_value_array = creditcardexpiration_value.split("/");
		var creditcardexpiration_month = parseInt(creditcardexpiration_value_array[0]);
		var creditcardexpiration_year = parseInt(creditcardexpiration_value_array[1]);

		var creditcardexpiration_month_value = parseInt(month_expiration_field.val());

		var creditcardexpiration_year_value = parseInt(year_expiration_field.val());


		var creditcardexpiration_response = ( (creditcardexpiration_month_value >= creditcardexpiration_month && creditcardexpiration_year_value >= creditcardexpiration_year) || (creditcardexpiration_year_value > creditcardexpiration_year) );
		
		console.log("creditcardexpiration_response " + creditcardexpiration_response);
		
		if (creditcardexpiration_response) {
			
			
			month_expiration_field.parent().parent().removeClass('has-error');
			year_expiration_field.parent().parent().removeClass('has-error');
			month_expiration_field.parent().parent().find('.help-block.with-errors').empty();
			year_expiration_field.parent().parent().find('.help-block.with-errors').empty();
			
		} else {
			
			month_expiration_field.parent().parent().addClass('has-error');
			year_expiration_field.parent().parent().addClass('has-error');
			$('btn.submit-order').addClass('disabled');
			
		}
		
		
		
	});

	
	//Add CVC Popup
	
	$('.change-payment-method-section input[name="SecurityCode"]').after($('#cvc-popup'));
	
	//Confirm Order Case
    // =====================
	
	$('.confirm-not-duplicate').click(function() {
		$(this).addClass('disabled');
		$("#orderForm").prepend('<input type="hidden" name="duplicateOk" value="1">');
	});
	
	//Check xverify response for email input
	
	$("#orderForm").on('focusout',"input.xverify_email",function () {
		
		var started = Date.now();
			
		var interval = setInterval(function(){ 
		
			// for 8 seconds
			if (Date.now() - started > 8000) {
				
				// and then pause it
				clearInterval(interval);
				
			} else {
				
				if ($(".xverify-ui-tooltip-error").length) {

					$(".btn.submit-order").prop('disabled', false);
					$("input.xverify_email").parent().parent().addClass('has-error');
					$(".btn.submit-order").addClass('disabled');
					
				} else {
					
					$("input.xverify_email").parent().parent().removeClass('has-error');
					
				}
				
			}

		}, 1000);
		
	});
	

	
});
