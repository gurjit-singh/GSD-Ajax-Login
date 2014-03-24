<?php

class GSD_AjaxLogin_Block_Login extends Mage_Core_Block_Template
{
	public function __construct(){
		$this->setTemplate('ajaxlogin/login.phtml');
	}
	protected function _toHtml(){
	return parent::_toHtml();
	}
}