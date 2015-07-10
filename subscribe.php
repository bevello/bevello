<?php

require_once('lib/Drewm/MailChimp.php');

if(
	!isset($_REQUEST['id']) ||
	!isset($_REQUEST['FNAME']) ||
	!isset($_REQUEST['LNAME']) ||
	!isset($_REQUEST['EMAIL']) ||
    !isset($_REQUEST['STATE']) ||
    !isset($_REQUEST['POSTAL']) ||
    !isset($_REQUEST['BIRTHDAY'])
){
	header('HTTP/1.1 400 Bad Request', true, 400);
	echo "All fields are required.\n\n";
	var_dump($_REQUEST);
}else{
	$MailChimp = new MailChimp('a538079e399f5a7cf0c99ccd8139d0ca-us3');
	$result = $MailChimp->call('lists/subscribe', array(
		'id'                => '2ef3db6c94',
		'email'             => array('email'=>$_REQUEST['EMAIL']),
		'merge_vars'        => array(
			'FNAME' => $_REQUEST['FNAME'],
			'LNAME' => $_REQUEST['LNAME'],
			'EMAIL' => $_REQUEST['EMAIL'],
			'STATE' => $_REQUEST['STATE'],
			'POSTAL' => $_REQUEST['POSTAL'],
			'BIRTHDAY' => $_REQUEST['BIRTHDAY']
		),
		'double_optin'      => false,
		'update_existing'   => true,
		'replace_interests' => false,
		'send_welcome'      => true
	));
	if($result){
		header('Location: /?subscribeSuccess=' . $_REQUEST['id']);
	}else{
		header('Location: /?subscribeFailure=' . $_REQUEST['id']);
	}
}