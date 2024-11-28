# OpenPOS Configuration
Incase you didn't already know, OpenPOS is built to run as a standard Magento website, therefore you need to create your Web/Store and configure this as you would any other. 

## Create your new web/store
Ensure you have a new web/store created and all relevant domain configuration complete so the site resolves and is initially accessible.

## Theme Association
As part of any traditional Hyvä Theme setup you might want to copy our base POS theme into app/design and inherit `openpos/default`
If you are using our default template simply set your POS website theme to 'OpenPOS Default'

## Basic configuration of OpenPOS
Login to Magento Admin and go to 
ZERO-1 POS > Configuration - General
 - Set 'Enable POS system' to 'Enable'
 - Set 'POS Store' to whichever Web Store you wish - IMPORTANT - this will prevent unauthorised access to the site
 - Choose admin users you would like to have access to a till under 'Till users'

General Magento Config - for the POS website only
 - Allow Guest Checkout	- Yes
 - Display Mini Cart - Yes
 - After Adding a Product Redirect to Shopping Cart	- No
