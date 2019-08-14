<?php

/**
 * Emark
 */
class Emark_Mbbwebservice_Model_Email_Template extends Mage_Core_Model_Email_Template {

    private $_saveRange = array();

    protected function _saveMail($email, $name=null, array $variables = array()) {
        Mage::getModel('mbbwebservice/mail')
        ->setSubject()
        ->setIsPlain()
        ->setBody()
        ->setFromEmail()
        ->setFromName()
        ->setToEmail()
        ->setToName()
        ->save();

        return $this;
    }

    public function sendMail(Emark_Mbbwebservice_Model_Mail $Mail, $config = NULL) {

        if (is_null ($config))
        {
            $config = array('username' => Mage::getStoreConfig(Emark_Mbbwebservice_Helper_Config::XML_PATH_SMTP_LOGIN),
                            'password' => Mage::getStoreConfig(Emark_Mbbwebservice_Helper_Config::XML_PATH_SMTP_PASSWORD));
        }

        $config ['smtp'] = Mage::getStoreConfig(Emark_Mbbwebservice_Helper_Config::XML_PATH_SMTP_HOST);
        $config ['port'] = Mage::getStoreConfig(Emark_Mbbwebservice_Helper_Config::XML_PATH_SMTP_PORT);
        $config ['auth'] = 'login';
	/*echo "<pre>";
	var_dump($config);
	var_dump(Mage::getStoreConfig('system/smtp/host'));
	var_dump(Mage::getStoreConfig('mbbwebservice/smtp/ports'));
	die();*/

        $ssl = Mage::getStoreConfig(Emark_Mbbwebservice_Helper_Config::XML_PATH_SMTP_SSL);

        if (! empty ($ssl))
        {
            $config ['ssl'] = $ssl;
        }

        $transport = new Zend_Mail_Transport_Smtp ($config ['smtp'], $config);

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();

        $mail->addHeader ('X-Mailer', 'Emark-for-Magento/1.0', TRUE);

        $mail->setSubject('=?utf-8?B?' . base64_encode($Mail->getSubject()) . '?=');

        if(!empty($this->_saveRange)) {
            foreach($this->_saveRange as $range) {
                $mail->addTo($range['email'], '=?utf-8?B?' . base64_encode($range['name']) . '?=');
            }
        }

        else {
            $mail->addTo($Mail->getToEmail(), '=?utf-8?B?' . base64_encode($Mail->getToName()) . '?=');
        }

        $mail->setFrom($Mail->getFromEmail(), $Mail->getFromName());

        if ($Mail->getIsPlain()) {
            $mail->setBodyText($Mail->getBody());
        } else {
            $mail->setBodyHTML($Mail->getBody());
        }

        $this->setUseAbsoluteLinks(true);

        try {
            $mail->send($transport);
            $this->_mail = null;
        } catch (Exception $e) {

            throw($e);
            return false;
        }
        return true;
    }

    public function send($email, $name=null, array $variables = array()) {
        if (!Mage::getStoreConfig(Emark_Mbbwebservice_Helper_Config::XML_PATH_ENABLED)) {
            return parent::send($email, $name, $variables);
        }

        if (!$this->isValidForSend() || !$email) {
            return false;
        }

        $Mail = Mage::getModel('mbbwebservice/mail');

        if (is_null($name)) {
            $name = substr($email, 0, strpos($email, '@'));
        }

        $variables['email'] = $email;
        $variables['name'] = $name;

        $Mail->setBody($this->getProcessedTemplate($variables, true));
        $Mail->setIsPlain($this->isPlain());
        $Mail->setSubject($this->getProcessedTemplateSubject($variables));


        $Mail
        ->setFromName($this->getSenderName())
        ->setFromEmail($this->getSenderEmail())
        ->setReplyTo($this->getReplyTo())
        ->setToName($name)
        ->setToEmail($email)
        ->setTemplateId($this->getTemplateId())
        ->setStoreId(Mage::app()->getStore()->getId());

        $this->sendMail($Mail);

        return true;
    }

    private function _getToData($email,$name) {

        $range = array();

        if(!is_array($name)) {
            $name = (array) $name;
        }

        for($i=(count($email)-1);$i>=0;$i--) {

            if (!isset($name[$i])) {
                $name[$i] = substr($email[$i], 0, strpos($email[$i], '@'));
            }

            if(isset($name[$i]) && !is_array($name[$i]) && empty($name[$i])) {
                $name[$i] = substr($email[$i], 0, strpos($email[$i], '@'));
            }

            $range[$i]['email'] = $email[$i];
            $range[$i]['name'] = $name[$i];

        }

        return $range;
    }

}
