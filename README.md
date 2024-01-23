# Open POS

Open POS is a Free Magento 2 module which includes several sub-packages to provide core functionality for a fully functional EPOS (Electronic Point Of Sale) for Magento Open Source, Adobe Commerce or Mage-OS. It uses Hyvä, Hyvä Checkout and MageWire extensively to create an ultra-fast experience. 

 - [Installation](installation.md)
 - [Configuration](configuration.md)
 - FAQ
 - [CHANGELOG](CHANGELOG.md)
 - TODO
 - [License](LICENSE.txt)




 
## Snagging List
 - [x] Allow Back Orders - https://zero1.teamwork.com/app/tasks/34762912
 - [x] Cash Payment Tender - https://zero1.teamwork.com/app/tasks/34766069
 - [x] Add Product with Custom Price - discussed with callum, price field shows with some clever shit, if visible/populated, that price is used
 - [x] Assume all customers  'Allow Remote Support' login for only the POS website
 - [ ] Can we implement ACLs so master users can create roles so POS staff can only get to the customer page?
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
