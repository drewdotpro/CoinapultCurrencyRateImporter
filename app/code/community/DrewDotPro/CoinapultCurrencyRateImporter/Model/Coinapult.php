<?php

class DrewDotPro_CoinapultCurrencyRateImporter_Model_Coinapult extends Mage_Directory_Model_Currency_Import_Abstract
{
    protected $_url = 'https://api.coinapult.com/api/ticker';
    protected $currencySets = array(
        "USD",
        "EUR",
        "GBP",
        "XAG",
        "XAU",
        "BTC",
    );
    protected $_messages = array();
    protected $_rates = array();

    protected function _convert($currencyFrom, $currencyTo, $retry = 0)
    {
        $mageFilename = 'app/Mage.php';
        require_once $mageFilename;
        umask(0);
        Mage::app();
        Mage::log($currencyFrom . " TO " . $currencyTo, null, "debug.log");

        if (!in_array($currencyFrom, $this->currencySets)) {
            $this->_messages[] = Mage::helper('directory')->__('No rate provision for %s.', $currencyFrom);
            return null;
        }
        if (!in_array($currencyTo, $this->currencySets)) {
            $this->_messages[] = Mage::helper('directory')->__('No rate provision for %s.', $currencyTo);
            return null;
        }
        try {
            $curl = new Varien_Http_Adapter_Curl();
            $curl->setConfig(array(
                'timeout' => 15 //Timeout in no of seconds
            ));
            $this->_rates["BTC"] = 1;
            $requiredRates = array($currencyFrom, $currencyTo);
            $filter = Mage::getConfig()->getNode('global/drewdotpro_coinapultcurrencyrateimporter/filter')->asArray();

            foreach ($requiredRates as $requiredRate) {
                if (!isset($this->_rates[$requiredRate])) {
                    $url = $this->_url . '?filter=' . $filter . "&market=" . $requiredRate . "_BTC";
                    $curl->write(Zend_Http_Client::GET, $url, '1.0');
                    $data = $curl->read();
                    $curl->close();
                    list($headers, $data) = explode("\r\n\r\n", $data, 2);
                    $data = trim($data);
                    if ($data === false) {
                        $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s.', $url);
                        return null;
                    }
                    $parsedData = json_decode($data, true);
                    if ($parsedData === false || !is_array($parsedData) || !isset($parsedData[$filter]['bid'])) {
                        $this->_messages[] = Mage::helper('directory')->__('Cannot parse rate data from %s.', $$url);
                        return null;
                    }
                    $this->_rates[$requiredRate] = $parsedData[$filter]['bid'];
                }
            }

            if (!isset($this->_rates[$currencyFrom])) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate for %s.', $currencyFrom);
                return null;
            }

            if (!isset($this->_rates[$currencyTo])) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate for %s.', $currencyTo);
                return null;
            }
            Mage::log($this->_rates[$currencyFrom] . " TO " . $this->_rates[$currencyTo], null, "debug.log");
            return (float)1 / $this->_rates[$currencyFrom] * $this->_rates[$currencyTo];
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
