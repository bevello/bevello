<?php
error_reporting(E_ALL | E_STRICT);
define('MAGENTO_ROOT', getcwd());
$mageFilename = MAGENTO_ROOT . '/app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
Mage::app();
$products = Mage::getModel("catalog/product")->getCollection();
$products->addAttributeToSelect('category_ids')->addAttributeToSelect('name')->addAttributeToSelect('weight');
$fp = fopen('var/export/exports.csv', 'w');
$csvHeader = array("sku", "categories","name","weight");
fputcsv( $fp, $csvHeader,",");
foreach ($products as $product){
    $sku = $product->getSku();
	$name = $product->getName();
	$weight = $product->getWeight();
	$catname = '';
	foreach ($product->getCategoryIds() as $id){
		$category = Mage::getModel('catalog/category')->load($id); 
		$catname .= $category->getName() . ' ';
	}
    fputcsv($fp, array($sku, $catname, $name, $weight), ",");
}
fclose($fp);