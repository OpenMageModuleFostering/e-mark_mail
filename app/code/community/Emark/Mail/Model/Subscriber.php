<?php

/**
 * @desc  Rewrite the newsletter subscribe Model
 *
 * Rewrites the method subscribe and the confirm method. The only thing that is changed
 * Is that the subscriber will be synct to the EM.
 *
 */
class Emark_Mail_Model_Subscriber extends Mage_Newsletter_Model_Subscriber
{
    public function subscribe($email)
    {	

        $this->loadByEmail($email);
        $customerSession = Mage::getSingleton('customer/session');

        if(!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        $isConfirmNeed   = (Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1) ? true : false;
        $isOwnSubscribes = false;
        $ownerId = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email)
            ->getId();
        $isSubscribeOwnEmail = $customerSession->isLoggedIn() && $ownerId == $customerSession->getId();

        if (!$this->getId() || $this->getStatus() == self::STATUS_UNSUBSCRIBED
            || $this->getStatus() == self::STATUS_NOT_ACTIVE
        ) {
            if ($isConfirmNeed === true) {
                // if user subscribes own login email - confirmation is not needed
                $isOwnSubscribes = $isSubscribeOwnEmail;
                if ($isOwnSubscribes == true){
                    $this->setStatus(self::STATUS_SUBSCRIBED);
                } else {
                    $this->setStatus(self::STATUS_NOT_ACTIVE);
                }
            } else {
                $this->setStatus(self::STATUS_SUBSCRIBED);
            }
            $this->setSubscriberEmail($email);
        }

        if ($isSubscribeOwnEmail) {
	    $storeId = $customerSession->getCustomer()->getStoreId();
            $this->setStoreId($storeId);
            $this->setCustomerId($customerSession->getCustomerId());
        } else {
	    $storeId = Mage::app()->getStore()->getId();
            $this->setStoreId($storeId);
            $this->setCustomerId(0);
        }

        $this->setIsStatusChanged(true);

        try {
            $this->save();
            if ($isConfirmNeed === true
                && $isOwnSubscribes === false
            ) {
                $this->sendConfirmationRequestEmail();
            } else {
                $this->sendConfirmationSuccessEmail();

		#sync to the EM
    		Mage::Helper('mail/customer')->sync($email, $storeId);
            }

            return $this->getStatus();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
	}
    }

     public function confirm($code) {

	if($this->getCode()==$code) {
            $this->setStatus(self::STATUS_SUBSCRIBED)
                 ->setIsStatusChanged(true)
                 ->save();

	    $email = $this->getEmail();
	    $storeId = $this->getStoreId();

	    # sync to the EM
	    Mage::Helper('mail/customer')->sync($email, $storeId);

            return true;
	}

	return false;
     }
}
