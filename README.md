# Coinapult Currency Rate Importer

Designed for use in a Magento system that can use Bitcoin as a native currency (not supported by default).

Prices are converted based on the rates given by the Coinapult API at
https://api.coinapult.com/api/ticker

This version includes manual updates for currencies.


Features
-------------
Currencies: USD, EUR, GBP, XAG, XAU
Runs every 15 minutes via CRON to ensure currency accuracy. Won't do anything if not the selected service.

Compatibility
-------------
Coinapult CurrencyConverter has been tested with the following Magento versions:
- Magento Community Edition 1.9.0.1

But is expected to also be compatible with:
Coinapult CurrencyConverter has been tested with the following Magento versions:
- Magento Community Edition 1.5.1.0
- Magento Community Edition 1.6.2.0
- Magento Community Edition 1.7.0.2
- Magento Community Edition 1.8.0.0

Recommended Additional Modules
-------------
Aoe_Scheduler - Because knowing when stuff's happening is sensible.

https://github.com/AOEpeople/Aoe_Scheduler/

Installation Notes
-------------
* Be sure to set Coinapult as your currency rate provider in Scheduled Import Settings under System -> Configuration -> Currency Setup

Works best with a modgit install

```
modgit clone coinapultcurrencyrate https://github.com/drewdotpro/CoinapultCurrencyRateImporter.git
```

Currently uses the 'small' option. Change config.xml's config/drewdotpro_coinapultcurrencyrateimporter/filter if you want to use a different filter set as per:

https://coinapult.com/developer/api/http#ticker

