<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:15 PM
 */

class Bevello_Slider_Model_Mysql4_Footerrow_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
{
    //parent::__construct();
    $this->_init('footerrow/footerrow');
}
}