<?php

/**
 * Emark
 */
class Emark_Mbbwebservice_Model_Observer
{
    protected static $fields = array ();

    public function sendTestMail ($observer)
    {
        $data = $observer->getEvent ()->getData ('data_object')->getData ();

        switch ($data ['field'])
        {
            case 'login':
            {
                self::$fields ['username'] = $data ['value'];

                break;
            }
            case 'password':
            case 'test':
            case 'test_address':
            {
                self::$fields [$data ['field']] = $data ['value'];

                break;
            }
        }

	$groups = Mage::app()->getFrontController()->getRequest()->getParam('groups');

	if(isset($groups['ident_general']['fields']['email'])) {
		if(!stristr($groups['ident_general']['fields']['email']['value'], "@e-mark.nl")) {
			throw new Exception('Your general e-mail address must be the e-mark domain (@e-mark.nl, system->configuration->Store Email Addresses->General Contact)');
			return;
		}
	} else if(!strstr(Mage::getStoreConfig ('trans_email/ident_general/email'), "@e-mark.nl")) {
		throw new Exception('Your general e-mail address must be the e-mark domain (@e-mark.nl, system->configuration->Store Email Addresses->General Contact)');
        return;
	}

        if (isset(self::$fields ['test']) && self::$fields ['test'] && 4 == count (self::$fields))
        {
            $configs = array (array ('ssl://', 465),
                              array ('tls://', 587),
                              array ('', 587),
                              array ('', 588),
                              array ('tls://', 2525),
                              array ('', 2525));

            $host = Mage::getStoreConfig(Emark_Mbbwebservice_Helper_Config::XML_PATH_SMTP_HOST);
            $connected = FALSE;

            for ($i = 0; $i < count ($configs); ++$i)
            {
                $soc = @fSockOpen ($configs [$i] [0].$host, $configs [$i] [1], $errno, $errstr, 5);

                if ($soc)
                {
                    fClose ($soc);

                    $connected = TRUE;

                    break;
                }
            }

            if ($connected)
            {
                if ('ssl://' == $configs [$i] [0])
                {
                    Mage::getConfig ()->saveConfig (Emark_Mbbwebservice_Helper_Config::XML_PATH_SMTP_SSL, 'SSL');
                }
                elseif ('tls://' == $configs [$i] [0])
                {
                    Mage::getConfig ()->saveConfig (Emark_Mbbwebservice_Helper_Config::XML_PATH_SMTP_SSL, 'TLS');
                }
                else
                {
                    Mage::getConfig ()->saveConfig (Emark_Mbbwebservice_Helper_Config::XML_PATH_SMTP_SSL, '');
                }

                Mage::getConfig ()->saveConfig (Emark_Mbbwebservice_Helper_Config::XML_PATH_SMTP_PORT, $configs [$i] [1]);

                Mage::getConfig ()->reinit ();
                Mage::app ()->reinitStores ();

                $to = self::$fields ['test_address'];
				$from = Mage::getStoreConfig ('trans_email/ident_general/email');

                $Mail = Mage::getModel ('mbbwebservice/mail');

                $Mail->setBody (Mage::helper ('mbbwebservice')->__ ('Your Emark configuration is ok!'));
                $Mail->setIsPlain (TRUE);
                $Mail->setSubject (Mage::helper ('mbbwebservice')->__ ('Your test mail from Emark'));

                $Mail
                    ->setFromName('Emark')
                    ->setFromEmail($from)
                    ->setReplyTo($from)
                    ->setToName($to)
                    ->setToEmail($to);

                $sender = Mage::getModel ('mbbwebservice/email_template')->load (Mage::getStoreConfig (Mage::app ()->getStore ()->getId ()));

                $sender->sendMail($Mail, self::$fields);
            }
            else
            {
                throw new Exception (sPrintF ('Please contact Emark support to sort this out.<br /><br />%d - %s', $errno, $errstr));
            }
        }
    }
}

?>
