# Clockwork plugin for Magento

This is the Clockwork plugin for Magento.

## Installing the plugin

These files should be placed in `app/code/community/Mediaburst/Sms` in your Magento installation.

You can also install the most recent release of the plugin from Magento Commerce ([http://www.magentocommerce.com/magento-connect/mediaburst-sms-9183.html][1]) and then paste the extension key in System -> Magento Connect.

## Packaging the plugin

1. Go to System -> Magento Connect -> Package Extensions.
2. Use the below details to package the plugin.
3. Click 'Save Data and Create Package'
4. The package (.tgz file) will be in var/connect/ in your Magento install.
5. 

### Package Info

**Name:** Mediaburst_SMS
**Channel:** Community
**Supported Releases:** 1.5.0.0 & later
**Summary:** Send order status and shipping text messages automatically.
**Description:** 

    <h2>Clockwork SMS</h2>
    <p>The Clockwork SMS add-on enables Magento to send text messages to you and your customers whenever an order status changes.</p>
    <ul>
    <li>Your website can send you a text when somebody places an order.</li>
    <li>Send you customer a text when you ship their order</li>
    <li>Notify your customer if shipping is delayed.</li>
    </ul>
    <p>To use the plugin you need to sign up for a <a href="https://www.clockworksms.com/?utm_source=plugin&utm_medium=magento&utm_campaign=magento">Clockwork SMS API</a> account. Mediaburst SMS API is a premium service using network connected SMS routes (it doesnt use the free email to SMS routes). This means you can deliver text messages to over 400 networks in over 150 countries around the world with high throughput and super fast delivery.</p>
    <p>The price for a Clockwork account is 5p per text message irrespective of which country you are sending to.</p>
    
**License:** ISC
**License URL:** http://opensource.org/licenses/isc-license.txt

### Authors

**Name:** Mediaburst
**User:** mediaburst
**Email:** hello@mediaburst.co.uk

### Dependencies

**Minimum PHP Version:** 5.1.0
**Maximum PHP Version:** 6.0.0

### Contents

<table>
  <tr>
    <th>Target</th>
    <th>Path</th>
    <th>Type</th>
  </tr>
  <tr>
    <td>Magento Community module file</td>
    <td>Mediaburst</td>
    <td>Recursive Dir</td>
  </tr>
</table>

## Troubleshooting

* Clear/disable the caches in Magento (System -> Cache Management), and logout of the Admin Panel.
* Magento is iffy about letting you login when your URL doesn't have dots in it. Don't install it on localhost, instead use a virtual host such as magento.dev.

[1]: http://www.magentocommerce.com/magento-connect/mediaburst-sms-9183.html