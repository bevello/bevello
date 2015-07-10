<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:14 PM
 */


    class Bevello_Slider_Model_Mysql4_Footerads extends Mage_Core_Model_Mysql4_Abstract
    {
        public function _construct()
    {
        $this->_init('footerads/footerads', 'slider_id');
    }
    }