<?php 
function getFieldTypeValue($fieldType){
	switch($fieldType){
		case 1:
			$fieldTypeValue = "boolean";
		break;
		case 2:
			$fieldTypeValue = "integer";
		break;
		case 4:
			$fieldTypeValue = "text";
		break;
		case 5:
			$fieldTypeValue = "email";
		break;
		case 6:
			$fieldTypeValue = "select";
		break;
		case 7:
			$fieldTypeValue = "state";
		break;
		case 8:
			$fieldTypeValue = "country";
		break;
		case 9:
			$fieldTypeValue = "hidden";
		break;
		default:
			$fieldTypeValue = "";
	}
	return $fieldTypeValue;
}

function generateFields($fieldsArray){
	
	foreach($fieldsArray as $singleFieldValue){
		
		$fieldName     = $singleFieldValue->name;
		$fieldLabel    = $singleFieldValue->label;
		$fieldOldValue = $singleFieldValue->oldValue;
		$fieldType     = getFieldTypeValue($singleFieldValue->type); 
		$fieldRequired = $singleFieldValue->required;
		$fieldOptions  = $singleFieldValue->options;

		$requiredFieldStar  = ($fieldRequired === true) ? '<span class="required-star"> * </span>' : '';
		
		$requiredFieldClass = ($fieldRequired === true) ? 'required-field' : '';
		
		$requiredFieldValue = ($fieldRequired === true) ? 'required' : '';
		
	


		if($fieldType === "hidden"){ 	

		} else if ($fieldType === "boolean"){
			if($fieldName === "usePaypal"){
		?>		
				
				<div class="form-group change-payment-radio-buttons">		
					<label class="radio-inline">
						<input type="radio" class="radio" name="<?php echo $fieldName ?>" value="0" checked="checked" >
						<img src="images/creditcard.png" class="paymentIcon">
						<span>Credit Card </span>
					</label>
					<label class="radio-inline">
						<input  type="radio" class="radio" name="<?php echo $fieldName ?>" value="1">
						<img src="images/paypal.png" class="paymentIcon">
						<span>PayPal</span>
					</label>
				</div>
		<?php
			}
		} else if ( ($fieldType === "select") || ($fieldType === "state") || ($fieldType === "country") ){  

			if ($fieldType === "state"){
				$stateOrCountryClass = "state-form-group";
			} else if ($fieldType === "country"){
				$stateOrCountryClass = "country-form-group";
			} else {
				$stateOrCountryClass = "";
			}
		?>
			<div class="form-group <?php echo $stateOrCountryClass;?>">
				<div class="col-md-3 col-sm-3 order-form-field-label">
					<?php echo $fieldLabel.$requiredFieldStar; ?>
				</div>
				<div class="col-md-6 col-sm-6">
					<select class="form-control input-sm <?=$requiredFieldClass?>" name="<?=$fieldName?>" <?=$requiredFieldValue?>>

						<?php foreach($fieldOptions as $singleOptionKey => $singleOptionValue){ ?>
							<?php $selected = ($singleOptionValue == 'United States') ?  'selected' : ''; ?>
							<?php if (isset($fieldOldValue)) {
								
								$selected = ($singleOptionKey == $fieldOldValue) ?  'selected' : '';
								
							} ?>
							<option value="<?php echo $singleOptionKey?>" <?php echo $selected; ?> ><?php echo $singleOptionValue;?></option>
						<?php }?>
						
					</select>
				</div>
				<div class="col-md-3 col-sm-3">	
					<span class="help-block with-errors"></span>
				</div>	
			</div>
		
		<?php	
		
		} else {

		?>
		
			<div class="form-group">	
				<div class="col-md-3 col-sm-3 order-form-field-label">
					<?php echo $fieldLabel.$requiredFieldStar; ?>
				</div>
				<div class="col-md-6 col-sm-6">
					<input type="<?=$fieldType?>" name="<?=$fieldName?>" class="form-control input-sm <?=$requiredFieldClass?>"  <?=$requiredFieldValue?> value="<?php if(isset($fieldOldValue)) { echo htmlentities ($fieldOldValue); }?>" >
				</div>	
				<div class="col-md-3 col-sm-3">	
					<span class="help-block with-errors"></span>
				</div>			
			</div>
			
	<?php
	
		}
	}
}