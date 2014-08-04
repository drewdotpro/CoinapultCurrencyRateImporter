<?php

class DrewDotPro_CoinapultCurrencyRateImporter_Model_Observer extends Mage_Directory_Model_Observer
{
    const IMPORT_SERVICE_NAME = 'drewdotpro_coinapultcurrencyrateimporter';

    public function scheduledUpdateCurrencyRates($schedule)
    {

        $importWarnings = array();
        if (!Mage::getStoreConfig(self::IMPORT_ENABLE) || !Mage::getStoreConfig(self::CRON_STRING_PATH)) {
            return;
        }

        $service = Mage::getStoreConfig(self::IMPORT_SERVICE);

        if ($service !== self::IMPORT_SERVICE_NAME) {
            return;
        }
        if (!$service) {
            $importWarnings[] = Mage::helper('directory')->__('FATAL ERROR:') . ' ' . Mage::helper('directory')->__('Invalid Import Service specified.');
        }
        try {
            $importModel = Mage::getModel(Mage::getConfig()->getNode('global/currency/import/services/' . $service . '/model')->asArray());
        } catch (Exception $e) {
            $importWarnings[] = Mage::helper('directory')->__('FATAL ERROR:') . ' ' . Mage::throwException(Mage::helper('directory')->__('Unable to initialize the import model.'));
        }
        $rates = $importModel->fetchRates();
        $errors = $importModel->getMessages();
        if (sizeof($errors) > 0) {
            foreach ($errors as $error) {
                $importWarnings[] = Mage::helper('directory')->__('WARNING:') . ' ' . $error;
            }
        }
        if (sizeof($rates > 0)) {
            Mage::getModel('directory/currency')->saveRates($rates);
        }
        if (sizeof($importWarnings) > 0) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);

            /* @var $mailTemplate Mage_Core_Model_Email_Template */
            $mailTemplate = Mage::getModel('core/email_template');
            $mailTemplate->setDesignConfig(array(
                'area' => 'frontend',
            ))->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_ERROR_TEMPLATE),
                    Mage::getStoreConfig(self::XML_PATH_ERROR_IDENTITY),
                    Mage::getStoreConfig(self::XML_PATH_ERROR_RECIPIENT),
                    null,
                    array(
                        'warnings' => join("\n", $importWarnings),
                    )
                );

            $translate->setTranslateInline(true);
        }
    }
}
