<?php

class GSD_AjaxLogin_IndexController extends Mage_Core_Controller_Front_Action
{	
	public function loginPostAction(){
		if ($this->getRequest()->isPost()) {
			
			if($this->getRequest()->getParam('ajaxlogin_is_forgot_pwd')){
				$this->_forward('forgotPasswordPost');
			}
			
			$session = Mage::getSingleton('customer/session');
			
            $login = $this->getRequest()->getPost('login');
			
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
					if($session->getCustomer()->getId()){
						$result['success'] = true;
						$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
						return;
					}
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($login['username']));
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
					$result['error'] = true;
					$result['error_message'] = $message;
					$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    //$session->addError($message);
                    //$session->setUsername($login['username']);
					return;
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                //$session->addError($this->__('Login and password are required.'));
				$result['error'] = true;
				$result['error_message'] = $this->__('Login and password are required.');
				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
			
			
        }
	}
	
	public function forgotPasswordPostAction()
    {
        // $email = $this->getRequest()->getPost('email');
		$login = $this->getRequest()->getPost('login');
		$email = $login['username'];
		$result['is_forgot_pwd'] = true;
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                //$this->_getSession()->addError($this->__('Invalid email address.'));
				$result['error'] = true;
				$result['error_message'] = $this->__('Invalid email address.');
                //$this->getResponse()->setRedirect(Mage::getUrl('*/*/forgotpassword'));
				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newPassword = $customer->generatePassword();
                    $customer->changePassword($newPassword, false);
                    $customer->sendPasswordReminderEmail();

                    //$this->_getSession()->addSuccess($this->__('A new password has been sent.'));
					$result['success'] = true;
					$result['success_message'] = $this->__('A new password has been sent.');

                    //$this->getResponse()->setRedirect(Mage::getUrl('*/*'));
					$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
                catch (Exception $e){
					$result['error'] = true;
					$result['error_message'] = $e->getMessage();
					$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    //$this->_getSession()->addError($e->getMessage());
                }
            }
            else {
				$result['error'] = true;
				$result['error_message'] = $this->__('This email address was not found in our records.');
				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                // $this->_getSession()->addError($this->__('This email address was not found in our records.'));
                // $this->_getSession()->setForgottenEmail($email);
            }
        } else {
			$result['error'] = true;
			$result['error_message'] = $this->__('Please enter your email.');
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            // $this->_getSession()->addError($this->__('Please enter your email.'));
            // $this->getResponse()->setRedirect(Mage::getUrl('*/*/forgotpassword'));
            return;
        }

        //$this->getResponse()->setRedirect(Mage::getUrl('*/*/forgotpassword'));
    }
}