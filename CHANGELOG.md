# OpenPOS changelog

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