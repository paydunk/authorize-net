# Paydunk Authorize.net / PHP Integration

<img alt="Paydunk" src="http://paydunk.com/wp-content/themes/paydunk/images/footr_logo.png" />

Download the front-end plugin (found here: https://github.com/paydunk/front-end-plugin/blob/master/jquery.paydunk.js) and use the following files to get started quickly with Paydunk & Authorize.net! 

### index.php

Update this file with your paydunk App ID and price (order total). Request API access and register applications at https://developers.paydunk.com. Check in the Applications tab to view all of your apps. You'll see each of your apps are given an App ID and App Secret.

### test.php

Update this file with:

* info from your order database if necessary (lines 12-17)
* your authorize.net API Login ID & Transaction Key (line 20)
* your custom code depending on the order status i.e., check the payment status and update your order database accordingly (starting on line 91) 
* your Paydunk App ID and App Secret (lines 156 & 157)

### thankyou.php

Sample thank you page which the user is redirected to if the payment was successful. Specify the link to your thank you page when you register a new application at https://developers.paydunk.com.

### AuthorizeNet.php

The Authorize.net SDK. There is no need to make any changes to this file.
