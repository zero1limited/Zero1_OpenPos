<img src="https://www.zero1.co.uk/media/wysiwyg/openpos_1.jpg" width="100%" height="auto" />

# What is OpenPOS?

Everything you need to know about OpenPOS [is here](https://www.zero1.co.uk/blog/post/stories/openpos) 😁

Your Help is GREATLY appreciated.

If you are a merchant or agency wanting to implement OpenPOS please [join the OpenPOS Slack Channel Here] (https://join.slack.com/t/openpos-workspace/shared_invite/zt-32ozggysy-s1XeL_IcChy6PLLNmtUMeg)

# Info

[Installation](#installation)

[Configuration](#configuration)

[Changelog](CHANGELOG.md)


# Installation

```
composer require zero1/open-pos
```

```
php bin/magento setup:upgrade
```

```
php bin/magento deploy:mode:set production
```

# Configuration

We appreciate feedback (including bugs or installation issues) via our new [community Slack Channel](https://join.slack.com/t/openpos-workspace/shared_invite/zt-32ozggysy-s1XeL_IcChy6PLLNmtUMeg). 

> [!TIP]
> Incase you didn't already know, OpenPOS is built to run as a standard Magento website, therefore you need to create your Web/Store and configure this as you would any other


## Create your new web/store
Ensure you have a new web/store created and all relevant domain configuration complete so the site resolves and is initially accessible.

## Theme Association
As part of any traditional Hyvä Theme setup you might want to copy our base POS theme into app/design and inherit `openpos/default`
If you are using our default template simply set your POS website theme to 'OpenPOS Default'

## Setup
Login to Magento Admin and go to 
ZERO-1 POS > Configuration - General
 - Set 'Enable POS system' to 'Enable'
 - Set 'POS Store' to whichever Web Store you wish - IMPORTANT - this will prevent unauthorised access to the site
 - Choose admin users you would like to have access to a till under 'Till users'
 - If you would like a core OpenPOS experience, or you are noticing issues, please set 'Module integration mode' to 'None'

General Magento Config - for the POS website only
 - Allow Guest Checkout	- Yes
 - Display Mini Cart - Yes
 - After Adding a Product Redirect to Shopping Cart	- No
