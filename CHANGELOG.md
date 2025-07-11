# OpenPOS changelog

## [2.1.3] - in progress

### Fixed
- RMA items in POS orders no longer deduct product stock.
- Resolved an issue where out of stock items could not be added regardless of bypass stock configuration.
- Resolved an issue where occasionally the first RMA item added to the cart would temporarily display as 0 total.
- Resolved category filter layout issues

### Changed
- Non-RMA payment methods now no longer show on negative quanity carts.

## [2.1.2] - 2025-06-21

### Added
- Item options are now visible in the cart.

### Changed
- Improvements to customer search logic.
- Increased compatible Hyva versions.

### Fixed
- Resolved an issue where product images would fail to load in the cart.

## [2.1.1] - 2025-05-06

### Fixed
- Resolved an issue where a product search would be performed containing a trailing slash.
- Resolved an issue where a new login would be presented with 'You have logged out [user]' notice, even if the other user had an expired till session.

## [2.1.0] - 2025-04-16

### Added
- You can now edit an already scanned item's price from the cart.
- You can now search for customers via name, email, telephone rather than having to enter the full email address.
- A POS order billing address can now be the customers default billing address or the physical store's address (pulled from core config).
- Added the optional ability to emulate the physical store's address as the POS order's shipping address, despite the order being virtual and not having one.
- Added a CLI command for easy setup.

### Changed
- Ensured the OpenPOS theme cannot be used on any non-POS stores, in the event of a misconfiguration.
- Attempts to access the OpenPOS login on non-POS stores return a 404.
- Default address logic has changed. Shipping and billing addresses for POS quotes are set to the store's address by default. Billing address will be set to customer's default billing address if valid.
- Created new configuration group 'Advanced' and moved module intergration there.

### Fixed
- Resolved an issue where the notice to flush Magento cache after modifying till users does not display.
- Resolved an issue where product isSaleable bypass may affect other stores.

## [2.0.4] - 2025-01-29

### Added
- You can now configure different exit / return characters for the barcode scanner.

### Fixed
- Resolved an issue where the minicart total occasionally does not update immediately. 
- Resolved a display issue with the built in cash payment method.
- Improved handling of orders placed from the Magento admin on the POS store.

## [2.0.3] - 2025-01-14

### Changed
- Removed hardcoded pound sign on receipt print template.
- When a product was not successfully added to the cart from a barcode scan, the till user will now be redirected to the PDP (for entering required options etc).

## [2.0.2] - 2024-12-31

### Fixed

- Resolved an issue where a custom products price would not save when adding to cart.

## [2.0.1] - 2024-12-02

### Added

- You can now limit other Magento modules from making changes to the OpenPOS frontend. This is useful in cases where a module is causing an issue, or you don't want it making visual changes to the till.

### Fixed

- Resolved an issue during checkout where field validation could fail.

## [2.0.0] - 2024-11-28

### Added

- A complete overhaul of OpenPOS, increasing reliablity and compatibility.
- A cleaner and more intuitive theme for the till / frontend of OpenPOS.
- Multi-till support.
- Customer switching functionality right from the till UI.
- Google 2FA support on till login.
- A till session lifetime can now be configured.
- Ability to restrict which Magento admin users can operate tills.
- RMA functionlity is now built into the base OpenPOS module.

### Changed

- The Magento admin is no longer used to login to the till, instead you login from the till UI itself.
- A 'redirect' store is no longer required.
- A guest Magento customer entity is no longer required for walk-in customers.
- The receipt print / email process has been simplified, and is now easier to customise.
- Checkout process has been redesigned making it quicker to complete a customers order.
