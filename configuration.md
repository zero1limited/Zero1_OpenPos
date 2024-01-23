# OpenPOS Configuration
Incase you didn't already know, OpenPOS is built to run as a standard Magento website, therefore you need to create yoyur Web/Store and configure this as you would any other. 
The steps below will then take you through locking down that store so that it is protected from standard web-traffic. 
You are also free to implement further firewall rules as you see fit, but since the front-end is locked from unauthenticated customer sessions this should not be necessary.

## Create your new web/store
Ensure you have a new web/store created and all relevant domain configuration complete so the site resolves and is initially accessible.

## Theme Association
As part of any traditional Hyvä Theme setup you might want to copy our base POS theme into app/design and inherit `zero1/pos`
If you are using our default template simply setb your POS website theme to 'Zero-1 POS'

## Configure OpenPOS
Login to Magento Admin and go to 
ZERO-1 POS > Configuration - General
 - Set 'Enable POS system' to 'Enable
 - Set 'POS Store' to whichever Web Store you wish - IMPORTANT - this will prevent unregistered users accessing the site
 - Set 'Redirect Store' to the store you wish in-case any unregistered users should visit the URL

ZERO-1 POS > Configuration - General
 - 'Receipt header' - Enter text you wish to show at the top - HTML is allowed

General Config - for the POS website only
 - Allow Guest Checkout	 - No
 - Display Mini Cart - No
 - After Adding a Product Redirect to Shopping Cart	- Yes
