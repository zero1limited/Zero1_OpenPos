# Zero1_Pos

ZERO-1 POS is a Free Magento 2 module & theme which uses Hyvä and Hyvä Checkout extensively to create an ultra-fast Point of Sale system for Mage-OS and Magento Open Source. You must purchase the core extension from ZERO-1 in order to install it. Composer keys will be provided.

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
 - 

ZERO-1 POS > Configuration - General
 - 'Receipt header' - Enter text you wish to show at the top - HTML is allowed

General Config - for the POS website
 - Allow Guest Checkout	 - No
 - Display Mini Cart - No
 - After Adding a Product Redirect to Shopping Cart	- Yes


## Feature TODO List

 - [x] Restrict access to admin session only - so till users must be authed admin users
 - [x] Main MAP Menu for POS
 - [ ] Customer ID for Walk-in Customer - guest@divinetrash.co.uk
 - [ ] Ensure focus is always on search
 - [ ] Fallback to catalog search result if search term is not a SKU
 - [x] Always show minicart (not needed anymore)
 - [ ] Add something to the basket when there's no items in there (widget of popular products?)
 - [ ] Customer account changes - only show recent sales in customer account if customer is "walk-in" or "guest" (as above)
 - [ ] Qty buttons in basket for sausage fingers
 - [ ] Swipe to delete on ios in basket
 - [ ] Change font to match zero-1?
 - [ ] Multi-payment pethods through your phone
 - [ ] Pay by bank
 - [ ] Create homepage content as part of module's installation?

## Thoughts for future Dev
 - Additional screen for customer to enter their email address
 - comms with card terminal so it knows the amount due
