# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.16]
### Fixed
 - Issue where a quote wasn't being seen as virtual (hardcoded store ID from debugging)

## [0.0.15]
 - [x] (BREAKING CHANGE) Changed reciept_header template and block to just 'receipt'. Theme overrides of existing installations will need template renaming.
 - [x] Added ability to add a QR code in the receipt footer. URL configurable by admin, order increment ID added as query string.

## [0.0.14]

## [0.0.13]
 - [x] Remote shopping assistance is now forced enabled regardless of customer setting. THIS IS TEMPORARY
 - [x] Logging in to POS system as guest from admin panel menu now opens a new tab

## [0.0.12]


## [0.0.11]
 - [x] Moved MSI based plugins to their own module
 - [x] Added initial support for 'super mode' / custom price entering

## [0.0.10]
 - [x] Experimental support for stock check bypassing (Allow Back Orders - https://zero1.teamwork.com/app/tasks/34762912)

## [0.0.9]


## [0.0.8]
### Fixed
 -  Logo URL / src on receipt is now obtained from Magento

## [0.0.7]
### Fixed
 - POS Payment method modules do not enable/disable according to config
 - POS Success Page code needs to show only if the current store ID matches the Assigned pos_store config
 - Admin input for Walkin Customer (email address) - currently hard-coded
 - Started to neaten up the guest login controller

