<?php
class Bevello_SEO_Model_Observer
{

    public function productView(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        /* @var $product Mage_Catalog_Model_Product */

        if ($product) {
            $currentCategory = Mage::registry('current_category');
            if ($currentCategory && $currentCategory instanceof Mage_Catalog_Model_Category) {
                $cat =  $currentCategory->getName();
            }

            $name = ucwords(strtolower($product->getName() . ' | ' .$cat));

            // Add the product name
            $desc = 'Free Shipping over $100 | ' . $name . ' | Call 919.890.0829';

            $product->setMetaTitle($name);
            $product->setMetaDescription($desc);
        }
    }

}