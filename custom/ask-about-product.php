<?php
	include("includes/config.php");
	
	// Create the necessary variable that will be used in detecting errors
	$errorFound = false;
	$errorFields = array();
	// Create variables and retrieve the values for each element of the main form
	$product_url = stripslashes(trim($_REQUEST['product_url']));
	$product_name = stripslashes(trim($_REQUEST['product_name']));
	$user_name = stripslashes(trim($_POST['user_name']));
	$email = stripslashes(trim($_POST['email']));
	$question = stripslashes(trim($_POST['question']));
	
	// validateForm()
	// This function verifies that the form has been filled out correctly
	function validateForm() {
		// Start by making the necessary variables "global" so that they can be accessed by this function
		global $errorFound, $errorFields, $product_url, $product_name, $user_name, $email, $question;
		// Check for any empty values
		if(empty($product_url)) { $errorFound = true; $errorFields[] = 'product_url'; }
		if(empty($product_name)) { $errorFound = true; $errorFields[] = 'product_name'; }
		if(empty($user_name)) { $errorFound = true; $errorFields[] = 'user_name'; }
		if(empty($email)) { $errorFound = true; $errorFields[] = 'email'; }
		if(empty($question)) { $errorFound = true; $errorFields[] = 'question'; }
		// Check for any spam
		if( (strstr($product_name,'http:')!=false) || (strstr($product_name,'href=')!=false) ) { $errorFound = true; $errorFields[] = 'product_url'; }
		if( (strstr($user_name,'http:')!=false) || (strstr($user_name,'href=')!=false) ) { $errorFound = true; $errorFields[] = 'product_name'; }
		if( (strstr($email,'http:')!=false) || (strstr($email,'href=')!=false) ) { $errorFound = true; $errorFields[] = 'email'; }
		if( (strstr($question,'http:')!=false) || (strstr($question,'href=')!=false) ) { $errorFound = true; $errorFields[] = 'question'; }
		// If any errors were found, then return false.  Othwerwise, return true.
		if($errorFound) { return false; }
		return true;
	}

	// Check to see if the form has been submitted
	if(isset($_POST['submitted'])) {
		// Check to see if the form is valid
		validateForm();
		if(!$errorFound) {
			// If the form passes validation, then email the form to the appropriate address.
			// Create variables necessary for sending the form information and fill the body of the eamil with the appropriate information.
			$body .= "\nThe information provided in this email was submitted from bevello.com.\n";
			$body .= "A visitor has a question about the following product:\n\n\n";
			$body .= "PRODUCT NAME:  ".$product_name."\n";
			$body .= "PRODUCT URL:  ".urldecode($product_url)."\n\n";
			$body .= "VISITOR'S NAME:  ".$user_name."\n";
			$body .= "EMAIL:  ".$email."\n\n";
			$body .= "QUESTION:\n".$question;
			$body .= "\n\n\n";
			$body .= "-- End of Message.";
			
			// Create variables necessary for sending the form information and fill the body of the email with the appropriate information.
			$toAddress = "bevelloemail@gmail.com";
			$bccToAddress = "trimarkwebdesign@gmail.com";
			$fromAddress = "info@bevello.com";
			$emailSubject = "bevello.com - Question About a Product";
			$emailHeader = "From: " . $fromAddress . "\r\n";
			$emailHeader .= "Bcc: " . $bccToAddress . "\r\n";
			$emailHeader .= "X-Mailer: PHP/" . phpversion();
			// Finaly, email the results of the form to the appropriate person.  Allow the script to continue to run at this point.
			mail($toAddress, $emailSubject, $body, $emailHeader);
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Bevello - Ask Us About This Product</title>
<link rel="stylesheet" type="text/css" href="/skin/frontend/default/bevello-modern/css/styles-bevello.css" />
</head>
<body class="ask-about-product-form">

	<div class="title slanted">Ask Us About This Product</div>
	
	<?php
	// Check to see if the form has been submitted to itself and if it's passed validation.
	if(isset($_POST['submitted']) && (!$errorFound)) {
		echo "<p>Thank you! Your question has been delivered. Someone will be in touch with your shortly to answer any questions that you may have. Thank you for your interest in bevello.</p>\n";
	} else {
	?>
	<form name="myform" action="" method="post">
		<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td class="label">Product:</td>
			<td><input type="text" class="textfield<?php echo in_array('product_name', $errorFields) ? ' error' : ''; ?>" name="product_name" value="<?php echo $product_name; ?>" readonly="readonly" /></td>
		</tr>
		<tr>
			<td class="label">Your Name:</td>
			<td><input type="text" class="textfield<?php echo in_array('user_name', $errorFields) ? ' error' : ''; ?>" name="user_name" value="<?php echo $user_name; ?>" /></td>
		</tr>
		<tr>
			<td class="label">Your Email:</td>
			<td><input type="text" class="textfield<?php echo in_array('email', $errorFields) ? ' error' : ''; ?>" name="email" value="<?php echo $email; ?>" /></td>
		</tr>
		<tr>
			<td class="label" valign="top">Question:</td>
			<td valign="top"><textarea class="textfield<?php echo in_array('question', $errorFields) ? ' error' : ''; ?>" name="question" cols="5"><?php echo $question; ?></textarea></td>
		</tr>
		<tr>
			<td class="label"></td>
			<td><a class="button" onclick="document.myform.submit();"><span>Submit Your Question</span></a></td>
		</tr>
		</table>
		<input type="hidden" name="product_url" value="<?php echo $product_url; ?>" />
		<input type="hidden" name="submitted" value="true" />
	</form>
	<?php } ?>
	
</body>
</html>