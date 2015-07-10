<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:12 PM
 */


    class Bevello_Slider_Model_Slider extends Mage_Core_Model_Abstract
    {
        public function _construct()
    {
        parent::_construct();
        $this->_init('slider/slider');
    }
    }