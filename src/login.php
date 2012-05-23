<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title>Javascript RSA - Login Test</title>
</head>

<body>

<?php

define("KEY_PUBLIC", "-----BEGIN PUBLIC KEY-----
MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBAMYQWDqtLgDKlQvWzacGeBMQpbicd/uo
XAvgLNpFZLM7zuYFDhrYncRsl8LIHK0K3f7e1aFmUVgM4LrKU2WFIw0CAwEAAQ==
-----END PUBLIC KEY-----
");

define("KEY_PRIVATE", "-----BEGIN RSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: DES-EDE3-CBC,2BE9EB9BD7712C2B

FQ9nRtev8hFY+FXkbnH2qBdg7+cD4x759C5c+5PhwWAVccOA4nvtBnE4AUT1bC+H
r/viTPzL5M0vFbAfpOPeUVfuCYXmAxFwcW+pn++UtlNezMtWqZdGPSPc86OqtChE
PjZ5rNBhjTAY7xXX2n+jbZSq8M2LSWyM4gy3Oj8QMnKwdGNWeM/E/4uYyMr5V3Eb
7KveReWJnZ3r3mF7uWJYCjABRzVF8k5sn86FpRn6pLWRHigkpiyNGF7acJMRqaSY
RUIrVf5xclLloUoSuEAe8HSdTH7oxl3vqf8byedqzuWyAxCFWRNr2e+TJ79f1XPJ
m9vLhWhm1BWM3OiB8iw2MkaTx/RCEf31O3cgNG3bcW/uIZrvdV0xRhHsjk0HNFNI
QOEcS73avo2o4ncPJpxLGqg+a0ERtRhFRp0JdgwCxl8=
-----END RSA PRIVATE KEY-----
");

define("KEY_PASSPHRASE", "testkey");
define("TEST_PASSWORD", "test");

function login($email, $login) {
	// decrypt argument
	if(!openssl_private_decrypt($login, $login, openssl_pkey_get_private(KEY_PRIVATE,KEY_PASSPHRASE))) {
		echo "Failed to decrypt message.\n";
		return false;
	}
	// expecting sha1password+timestamp
	if(strlen($login)<44) return false;
	// extract password
	$password = substr($login,0,40);
	// extract stamp, stamp has milliseconds and is bigger than int
	$stamp = substr($login,40);
	// extract timestamp, timestamp is in seconds, and is an int
	$timestamp = substr($stamp,0,strlen($stamp)-3);
	if(!is_numeric($timestamp)) return false;
	// check timestamp
	if(abs(time() - (int)$timestamp) > 300) {
		echo "Timestamp expired. Client and server times may be out of sync.\n";
		return false;
	}
	// construct stamp
	//$stamp = "user.login.".sha1($email).".".$stamp;
	// take a note of the stamp, each unique stamp can only be used once
	//if($memcache->get($stamp) != NULL) return false;
	//$memcache->set($stamp,1,USER_LOGIN_TIMESTAMP_TTL);
	// connect to db and check password
	// check password
	if (pack("H*",$password)!=pack("H*",sha1(TEST_PASSWORD))) {
		echo "Password incorrect.\n";
		return false;
	}
	return true;
}


?>

<h1>Javascript RSA - Login Test</h1>
This test is an example to perform user login using javascript RSA. <br/>
<ol>
<li>Once the encrypted data is received, the server side decrypt using private key.</li>
<li>The message is separated into two parts, the hash and the timestamp.</li>
<li>The timestamp is checked to make sure the request is made in recent time. Set to allow up to 30 second difference.</li>
<li>The timestamp is recorded to make sure no single timestamp is repeated for a user.</li>
<li>The password hash is compared to the hash in the database.</li>
</ol>
For testing purpose, the credential to login is any E-mail with the password "test".<br/>
No database connection is made in this test. A hardcoded check is used. <br/>
Also the duplicate timestamp check is by-passed since it requires the presence of a memcached server.<br/>
The result is displayed:<br/>

<pre>
<?php
	$email = $_REQUEST["email"];
	$login = base64_decode($_REQUEST["login"]);
	if(login($email, $login))
		echo "login succeeded!";
	else
		echo "login failed!";
?>
</pre>
The source code for this php file is available <a href="login.txt">here</a>.
</body>

</html>

