# Zero1_Pos

ZERO-1 POS is a Free Magento 2 module & theme which uses Hyvä and Hyvä Checkout extensively to create an ultra-fast Point of Sale system for Mage-OS and Magento Open Source. You must purchase the core extension from ZERO-1 in order to install it. Composer keys will be provided.

This extension comprises of the following additional packages

 - POS Theme
 - POS Pay Card
 - POS Pay Cash
 - POS Pay Multi?


## Installation & Configuration

composer require zero1/pos
php bin/magento setup:upgrade
php bin/magento deploy:mode:set production

### Essential Steps
Set website theme to 'Zero-1 POS'

### Configure the POS for optimum use
Login to Admin and go to 
ZERO-1 POS > Configuration - General
 - Set 'Enable POS system' to 'Enable
 - Set 'POS Store' to whichever Web Store you wish - IMPORTANT - this will prevent unregistered users accessing the site
 - Set 'Redirect Store' to the store you wish in-case any unregistered users should visit the URL

ZERO-1 POS > Configuration - General
 - 'Receipt header' - Enter text you wish to show at the top - HTML is allowed

General Config - for the POS website
 - Allow Guest Checkout	 - No
 - Display Mini Cart - No
 - After Adding a Product Redirect to Shopping Cart	- Yes

## Release Notes
### 0.0.6
 - [x] Order the POS menu item higher, cant get to it on iPad easily

### 0.0.7
 - [x] Snag fix: POS Payment method modules do not enable/disable according to config
 - [x] Snag fix: POS Success Page code needs to show only if the current store ID matches the Assigned pos_store config
 - [x] Snag fix: Admin input for Walkin Customer (email address) - currently hard-coded
 - [x] Started to neaten up the guest login controller

### 0.0.8
 - [x] Logo URL / src on receipt is now obtained from Magento
 
## Snagging List
 - [ ] Allow Back Orders - https://zero1.teamwork.com/app/tasks/34762912
 - [ ] Cash Payment Tender - https://zero1.teamwork.com/app/tasks/34766069
 - [ ] Add Product with Custom Price - discussed with callum, price field shows with some clever shit, if visible/populated, that price is used
 - [ ] Assume all customers  'Allow Remote Support' login for only the POS website
 - [ ] Redirect Logged in customer to homepage instead of dashboard
 - [ ] FED - Cant tell which payment method selected

## Feature List - TBC
 - [ ] Ensure focus is always on search
 - [ ] Add something to the basket when there's no items in there (widget of popular products?)
 - [ ] Customer account changes - only show recent sales in customer account if customer is "walk-in" or "guest" (as above)
 - [ ] Qty buttons in basket for sausage fingers
 - [ ] Swipe to delete on ios in basket
 - [ ] Change font to match zero-1?
 - [ ] Multi-payment pethods through your phone
 - [ ] Create homepage content as part of module's installation?
 - [x] Restrict access to admin session only - so till users must be authed admin users
 - [x] Main MAP Menu for POS
 - [x] Customer ID for Walk-in Customer - guest@divinetrash.co.uk
 - [x] Fallback to catalog search result if search term is not a SKU
 - [x] Always show minicart (not needed anymore)

TODO
barcode printing
stock in, adjustments


## Thoughts for future Dev
 - Additional screen for customer to enter their email address
 - comms with card terminal so it knows the amount due
