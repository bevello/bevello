<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:14 PM
 */


    class Bevello_Slider_Model_Mysql4_Slider extends Mage_Core_Model_Mysql4_Abstract
    {
        public function _construct()
    {
        $this->_init('slider/slider', 'slider_id');
    }
    }