<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 7/22/15
 * Time: 10:41 AM
 */

class Bevello_Slider_Block_Footerads extends Mage_Core_Block_Template {

    public function getSuggested(){
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {

            $collection = Mage::getResourceModel('sales/order_item_collection')
                ->addAttributeToSelect('*');
            $collection->getSelect()->join( array('orders'=> sales_flat_order),
                'orders.order_id=main_table.order_id',array('orders.customer_email','orders.customer_id'));

            $customer = Mage::getSingleton('customer/session')->getCustomer();

            $collection->addFieldToFilter('customer_id',$customer->getId());

            $collection->getSelect()
                ->columns('GROUP_CONCAT(DISTINCT (SELECT sku FROM catalog_product_entity WHERE entity_id = r.linked_product_id) SEPARATOR \', \') AS related_skus')
                ->joinLeft(array('r' => 'catalog_product_link'), 'r.product_id = e.entity_id AND r.link_type_id = 1')
                ->group('e.entity_id');

            foreach($collection as $product){
                $product->getProductId();
            }


        }
    }
}