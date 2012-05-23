<?php
$pass = "testkey";
$config = array(
"private_key_bits"=>512
);

// Create the keypair
$res=openssl_pkey_new($config);
openssl_pkey_export($res, $privkey, $pass);
echo $privkey."\n";
// Get public key
$pubkey=openssl_pkey_get_details($res);
//var_dump($res);
$pubkey=$pubkey["key"];
echo $pubkey;
?>
