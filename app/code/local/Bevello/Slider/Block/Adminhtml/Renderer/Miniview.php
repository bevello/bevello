<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:33 PM
 */
class Bevello_Slider_Block_Adminhtml_Renderer_Miniview extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){
        $mediaurl=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $value = $row->getData($this->getColumn()->getIndex());
        $imgs = explode(',',$value);
        $html ='<p style="text-align:center;padding-top:10px;">';
        if($value) {
            foreach ($imgs as $img) {
                $html .= '<img src="' . $mediaurl . DS . $img . '"  style="width:100px;padding-right:10px;"/>';
            }
        } else {
            $html . '&nbsp;';
        }
        $html .= '</p>';
        return $html;
    }
}