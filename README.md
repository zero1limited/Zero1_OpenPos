<img src="https://www.zero1.co.uk/media/wysiwyg/openpos_1.jpg" width="100%" height="auto" />

# What is OpenPOS?

Everything you need to know about OpenPOS [is here](https://www.zero1.co.uk/blog/post/stories/openpos) 😁

Your Help is GREATLY appreciated.

If you are a merchant or agency wanting to implement OpenPOS please [join the OpenPOS Slack Channel Here](https://join.slack.com/t/openpos-workspace/shared_invite/zt-32ozggysy-s1XeL_IcChy6PLLNmtUMeg)

# Info

[Installation](#installation)

[Configuration](#configuration)

[Changelog](CHANGELOG.md)

[Development](DEVELOPMENT.md)

# Version Compatibility

⚠️ **Important Hyvä Requirements**

| OpenPOS Version      | Hyvä Theme                 | Hyvä Checkout |
|----------------------|----------------------------|---------------|
| **2.3.x**            | **Hyvä 1.4** (Open Source) | Not required  |
| **2.2.x and below**  |  **Hyvä 1.3 or lower**     | **Required**  |

Please ensure your Hyvä setup matches the OpenPOS version you are installing to avoid compatibility issues.


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

```
(optional) php bin/magento openpos:setup-wizard
```

# Configuration

We appreciate feedback (including bugs or installation issues) via our new [community Slack Channel](https://join.slack.com/t/openpos-workspace/shared_invite/zt-32ozggysy-s1XeL_IcChy6PLLNmtUMeg). 

Configuration can be found in:
[Stores] -> [Configuration] -> [OpenPOS] -> [Configuration]

Once the module is installed, you can run `bin/magento openpos:setup-wizard` for guided setup of the POS store.

Because the point of sale runs on a separate website, you may need to associate products with the POS website.
You can run `bin/magento openpos:assign-products` to automatically do this.


> [!TIP]
> Incase you didn't already know, OpenPOS is built to run as a standard Magento website, therefore you need to perform all relevant domain and webserver configuration to ensure the site resolves and is externally accessible.


# Theme Association
As part of any traditional Hyvä Theme setup you might want to copy our base POS theme into app/design and inherit `openpos/default`
If you are using our default template simply set your POS website theme to 'OpenPOS Default'

The default checkout is based on Luma, so the Hyvä theme fallback needs configuring as per the [Hyvä docs](https://docs.hyva.io/hyva-themes/luma-theme-fallback/index.html)

The setup wizard will apply this configuration for you.