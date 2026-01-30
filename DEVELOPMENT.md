# OpenPOS Development

This guide provides technical details for developers wishing to extend OpenPOS.

OpenPOS requires a website, store and store view to be created and configured.
The module then modifies the functionality of the above store, turning the interface into a point-of-sale.

Core development is a balance between keeping the POS clean and reliable, but at the same time, allowing all Magento extensions to modify the experience exactly as they would any other Magento store.

## Table of Contents
1. [Module Registration](#module-registration)
2. [Adding Payment Methods](#adding-payment-methods)
3. [Frontend Architecture](#frontend-architecture)

---

## Module Registration

OpenPOS includes a compatibility feature called **Module Integration Mode**. This feature can be configured to strip out 3rd party module blocks from the POS that aren't required or are causing issues.

Any modules that are OpenPOS specific can be whitelisted.

If your module adds UI elements to the POS frontend (e.g., via layout XML), you **must** register your module as an "OpenPOS Module" to ensure it renders correctly when the system is running in "Specific" or "None" integration modes.


### How to Register

To whitelist your module, add it to the `openPosModules` argument of `Zero1\OpenPos\Model\ModuleIntegration` in your module's `etc/di.xml`.

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Zero1\OpenPos\Model\ModuleIntegration">
        <arguments>
            <argument name="openPosModules" xsi:type="array">
                <item name="Vendor_MyModule" xsi:type="string">Vendor_MyModule</item>
            </argument>
        </arguments>
    </type>
</config>
```

OpenPOS will then automatically allow your module's blocks to render regardless of the configuration.


## Adding Payment Methods

The standard way for a POS order to be placed is through the Magento checkout. In this case any payment method will work out of the box.

Many Magento payment providers have online methods for physical card readers / terminals, where the cart total will be sent to the physical terminal. These work great with OpenPOS and require no code to get running.

---

However, OpenPOS also includes layaway functionality where orders can be placed without payment.

These orders enter Magento with a 'wrapper' payment method of: `openpos_layaways`.

From this stage the till user can create further payments from the POS. Only payment methods designed for use with OpenPOS can be used to make payments after an order is created.

To register a payment method with OpenPOS, inject the following configuration into the methods argument of `Zero1\OpenPos\Model\Payment\MethodProvider` via your `etc/di.xml`.

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Zero1\OpenPos\Model\Payment\MethodProvider">
        <arguments>
            <argument name="methods" xsi:type="array">
                <item name="my_custom_payment" xsi:type="array">
                    <item name="code" xsi:type="string">my_custom_payment</item>
                    <item name="label" xsi:type="string">My Custom Terminal</item>
                    <item name="module" xsi:type="string">Vendor_MyModule</item>
                    <item name="canUseForLayaways" xsi:type="boolean">true</item>
                    <item name="canUseForRma" xsi:type="boolean">true</item>
                </item>
            </argument>
        </arguments>
    </type>
</config>
```

| Key                     | Type    | Description                                                                 |
|-------------------------|---------|-----------------------------------------------------------------------------|
| code                    | string  | The Magento payment method code.                                            |
| label                   | string  | The label displayed to the cashier.                                         |
| module                  | string  | The module name responsible for this method.                                |
| canUseForLayaways       | boolean | If true, this method can be used to pay off Layaway balances.               |
| canUseForRma            | boolean | If true, this method can be used in RMA transactions.             |


**At the moment only offline methods / manual payments are supported for layaway orders. Online integrations are planned so the above is subject to change.**


## Frontend Architecture

The OpenPOS till interface is a Hyvä theme and utilizes Magewire for a lot of the interaction elements.

While the checkout also used to use Hyvä, it now uses the Magento Luma theme.
This was a decision made to make OpenPOS accessible to more sites.