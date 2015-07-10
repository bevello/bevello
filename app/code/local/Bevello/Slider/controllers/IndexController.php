<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 1:02 PM
 */

class Bevello_Slider_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
{
    $this->loadLayout();
    $this->renderLayout();
}
}