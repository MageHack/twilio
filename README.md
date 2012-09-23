# Magehack Sms plugin for Magento

This plugin was developed during the 2012 London Magento Hackathon.
It provides simple integration with the Twilio web service to send SMS on order status changes.
It is based on the Mediaburst Sms plugin [1].

## Troubleshooting

* Clear/disable the caches in Magento (System -> Cache Management), and logout of the Admin Panel.
* Magento is iffy about letting you login when your URL doesn't have dots in it. Don't install it on localhost, instead use a virtual host such as magento.dev.

[1]: http://www.magentocommerce.com/magento-connect/mediaburst-sms-9183.html
