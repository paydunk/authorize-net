<?php
/*
Paydunk Authorize.net Integration
*/

$status = 'error';

require('AuthorizeNet.php');
try
{
	
	//update the following lines from your order database
	$amount = 20.00;
	$shipping = 0.00;
	$tax = 0.00;
	$total = $amount+$shipping;
    $user_id = 1;
    $product = 'A test transaction';
 	
	//update the following line with your authorize.net API Login ID & Transaction Key
    $payment = new AuthnetAIM('8Z3V8mt9mg2', '2Nu6y39TB2K5fr3p', true); 
	
	// get the information posted from the Paydunk API - see https://developers.paydunk.com for more info!
	$expiration_date = $_POST["expiration_date"]; 
	$expiration_dates = explode('/', $expiration_date);
	$month = $expiration_dates[0];
	$year = "20".$expiration_dates[1];
	$expiration = date($month."-".$year);
	$creditcard = $_POST["card_number"];
	$cvv = $_POST["cvv"];
	$transaction_uuid = $_POST["transaction_uuid"];
	$order_number = $_POST["order_number"];
	
	if(isset($_POST["billing_name"])) {
	  $business_name = $_POST["billing_name"];
	  $business_names = explode(' ', $business_name);
	  $business_firstname = $business_names[0];
	  $business_lastname = $business_names[1];
	} else {
	$business_firstname = 'na'; //authorize.net needs a default value
	$business_lastname = 'na'; //authorize.net needs a default value
	}
	if(isset($_POST["shipping_name"])) {
	  $shipping_name = $_POST["shipping_name"]; 
	  $shipping_names = explode(' ', $shipping_name);
	  $shipping_firstname = $shipping_names[0];
	  $shipping_lastname = $shipping_names[1];
	} else {
	$shipping_firstname = 'na'; //authorize.net needs a default value
	$shipping_lastname = 'na'; //authorize.net needs a default value
	}
	if(isset($_POST["billing_phone"])) { $business_telephone = $_POST["billing_phone"]; } 
	else {
    $business_telephone = '555-555-5555'; //authorize.net needs a default value
	}
    $shipping_address   = $_POST["shipping_address_1"]." ".$_POST["billing_address_2"];
    $shipping_city      = $_POST["shipping_city"];
    $shipping_state     = $_POST["shipping_state"];
    $shipping_zipcode   = $_POST["shipping_zip"];
    $email = $_POST["email"];
    $business_address   = $_POST["billing_address_1"]." ".$_POST["shipping_address_2"];
    $business_city      = $_POST["billing_city"];
    $business_state     = $_POST["billing_state"];
    $business_zipcode   = $_POST["billing_zip"];
 
    $invoice    = substr(time(), 0, 6);
	
    $payment->setTransaction($creditcard, $expiration, $total, $cvv, $invoice, $tax);
    $payment->setParameter("x_duplicate_window", 180);
    $payment->setParameter("x_cust_id", $user_id);
    $payment->setParameter("x_customer_ip", $_SERVER['REMOTE_ADDR']);
    $payment->setParameter("x_email", $email);
    $payment->setParameter("x_email_customer", FALSE);
    $payment->setParameter("x_first_name", $business_firstname);
    $payment->setParameter("x_last_name", $business_lastname);
    $payment->setParameter("x_address", $business_address);
    $payment->setParameter("x_city", $business_city);
    $payment->setParameter("x_state", $business_state);
    $payment->setParameter("x_zip", $business_zipcode);
    $payment->setParameter("x_phone", $business_telephone);
    $payment->setParameter("x_ship_to_first_name", $shipping_firstname);
    $payment->setParameter("x_ship_to_last_name", $shipping_lastname);
    $payment->setParameter("x_ship_to_address", $shipping_address);
    $payment->setParameter("x_ship_to_city", $shipping_city);
    $payment->setParameter("x_ship_to_state", $shipping_state);
    $payment->setParameter("x_ship_to_zip", $shipping_zipcode);
    $payment->setParameter("x_description", $product);
    $payment->process();
 
    if ($payment->isApproved())
    {
        // Get info from Authnet to store in the database
        $approval_code  = $payment->getAuthCode();
        $avs_result     = $payment->getAVSResponse();
        $cvv_result     = $payment->getCVVResponse();
        $transaction_id = $payment->getTransactionID();
 
        // Do stuff with this. Most likely store it in a database.
        // Direct the user to a receipt or something similiar.
		$status = 'success';
    }
    else if ($payment->isDeclined())
    {
        // Get reason for the decline from the bank. This always says,
        // "This credit card has been declined". Not very useful.
        $reason = $payment->getResponseText();
 
        // Politely tell the customer their card was declined
        // and to try a different form of payment.
		$status = 'declined';
    }
    else if ($payment->isError())
    {
        // Get the error number so we can reference the Authnet
        // documentation and get an error description.
        $error_number  = $payment->getResponseSubcode();
        $error_message = $payment->getResponseText();
 
        // OR
 
        // Capture a detailed error message. No need to refer to the manual
        // with this one as it tells you everything the manual does.
        $full_error_message =  $payment->getResponseMessage();
 
        // We can tell what kind of error it is and handle it appropriately.
        if ($payment->isConfigError())
        {
            // We misconfigured something on our end.
        }
        else if ($payment->isTempError())
        {
            // Some kind of temporary error on Authorize.Net's end. 
            // It should work properly "soon".
        }
        else
        {
            // All other errors.
        }
 
        // Report the error to someone who can investigate it
        // and hopefully fix it
 
        // Notify the user of the error and request they contact
        // us for further assistance
    }
}
catch (AuthnetAIMException $e)
{
    echo 'There was an error processing the transaction. Here is the error message: ';
    echo $e->__toString();
}

//PAYDUNK: set data for PUT request
$bodyparams = array(
			"client_id" => "dA4Ps5NvklUhlHl7vTrScrOx84pHbEFq3XjV0m4F", // your APP ID goes here!!!
			"client_secret" => "iUSIyU7R1m3ElQyuNAisQJsx8tYPISrABB3ktyIK", // your APP SECRET goes here!!!
			"status" => $status);
//sends the PUT request to the Paydunk API
function CallAPI($method, $url, $data = false){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_PUT, 1);		
		$update_json = json_encode($data);	
		curl_setopt($curl, CURLOPT_URL, $url . "?" . http_build_query($data));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSLVERSION, 4);
		$result = curl_exec($curl);  
		$api_response_info = curl_getinfo($curl);
		curl_close($curl);
		return $result;
}
//get the transaction_uuid from Paydunk & call the the Paydunk API
$transaction_uuid = $_POST['transaction_uuid'];
if (isset($transaction_uuid)) {
	$url = "https://api.paydunk.com/api/v1/transactions/".$transaction_uuid;
	CallAPI("PUT", $url, $bodyparams);	
}
?>
