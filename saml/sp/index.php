<?php
/**
 *  SAML Handler
 */

session_start();

require_once dirname(dirname(__FILE__)).'/_toolkit_loader.php';

require_once 'settings.php';

$auth = new OneLogin_Saml2_Auth($settingsInfo);

if (isset($_GET['sso'])) {
    $auth->login();
} else if (isset($_GET['sso2'])) {
    $returnTo = $spBaseUrl.'/sp/attrs.php';
    $auth->login($returnTo);
} else if (isset($_GET['slo'])) {
    $returnTo = null;
    $paramters = array();
    $nameId = null;
    $sessionIndex = null;
	echo "5-----" . $_SESSION['samlNameId'] . "<br />";
	echo "6-----" . $_SESSION['samlSessionIndex'] . "<br />";
	//return;
    if (isset($_SESSION['samlNameId'])) {
        $nameId = $_SESSION['samlNameId'];
    }
    if (isset($_SESSION['samlSessionIndex'])) {
        $sessionIndex = $_SESSION['samlSessionIndex'];
    }

    $auth->logout($returnTo, $paramters, $nameId, $sessionIndex);
} else if (isset($_GET['acs'])) {
	//echo '<p><a href="?slo" >Logout</a></p>';
	//echo "-----------<br />";
	$strdecode = $_POST['SAMLResponse'];
	$sxe  = base64_decode($strdecode);
	$sxe = new SimpleXMLElement($sxe);
	
	$sxe -> registerXPathNamespace ( 's' ,  'urn:oasis:names:tc:SAML:2.0:protocol');
	$result  =  $sxe -> xpath ( '//s:StatusCode' );
	if($result[0]["Value"] == "urn:oasis:names:tc:SAML:2.0:status:Responder"){
		setcookie("samlNameId", "error000", time()+3600);
		return;
	}
	
	$sxe -> registerXPathNamespace ( 'c' ,  'urn:oasis:names:tc:SAML:2.0:assertion' );
	$result1  =  $sxe -> xpath ( '//c :AuthnStatement' );
	//echo "3---- " . $result1[0]["SessionIndex"] . "<br />";
	$_SESSION['samlSessionIndex'] = "" . $result1[0]["SessionIndex"];

	$sxe -> registerXPathNamespace ( 'c' ,  'urn:oasis:names:tc:SAML:2.0:assertion' );
	$result  =  $sxe -> xpath ( '//c :Subject//c :NameID' );
	//echo "2---- " . $result[0] . "<br />";
	$samlNameId = "" . $result[0];
	$_SESSION['samlNameId'] = $samlNameId;
	setcookie("samlNameId", $samlNameId, time()+3600);
	return;

	foreach ( $result  as  $title ) {
		echo  $title  .  "<br />" ;
	}
	echo "<br />1------1<br />";
	$sxe -> registerXPathNamespace ( 's' ,  'urn:oasis:names:tc:SAML:2.0:protocol');
	$result  =  $sxe -> xpath ( '//s:StatusCode' );
	echo  $result[0]["Value"]  .  "<br />" ;
	
	foreach ( $result  as  $title ) {
		echo  $title["Value"]  .  "<br />" ;
	}
	
	//$sxe -> registerXPathNamespace ( 'u' ,  'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified');
	$sxe -> registerXPathNamespace ( 'u' ,  'urn:oasis:names:tc:SAML:2.0:assertion' );
	$result  =  $sxe -> xpath ( '//u:Subject' );
	print_r("----1----");
	print_r($result);
	//print_r(base64_decode($_POST['SAMLResponse']));
	echo "<br />-----------<br />";
	echo "<br /> 7.-------" . $_SESSION['samlNameId'];
	$_SESSION["GUID"] = "";
	$_SESSION["GUID_ADM"] = "";
	$_SESSION["loginLevel"] = ""; //#001 Add
	$_SESSION["loginName"] = ""; //#001 Add
	session_write_close();
	header("Location:/uat/login.php");
	exit();
	return;
    $auth->processResponse();
    $errors = $auth->getErrors();

    if (!empty($errors)) {
        print_r('<p>1'.implode(', ', $errors).'</p>');
    }

    if (!$auth->isAuthenticated()) {
        echo "<p>2 Not authenticated</p>";
        exit();
    }

    $_SESSION['samlUserdata'] = $auth->getAttributes();
    $_SESSION['samlNameId'] = $auth->getNameId();
    $_SESSION['samlSessionIndex'] = $auth->getSessionIndex();        
    if (isset($_POST['RelayState']) && OneLogin_Saml2_Utils::getSelfURL() != $_POST['RelayState']) {
        $auth->redirectTo($_POST['RelayState']);
    }
} else if (isset($_GET['sls'])) {
    $auth->processSLO();
    $errors = $auth->getErrors();
    if (empty($errors)) {
        print_r('<p>Sucessfully logged out</p>');
    } else {
        print_r('<p>'.implode(', ', $errors).'</p>');
    }
}

if (isset($_SESSION['samlUserdata'])) {
    if (!empty($_SESSION['samlUserdata'])) {
        $attributes = $_SESSION['samlUserdata'];
        echo 'You have the following attributes:<br>';
        echo '<table><thead><th>Name</th><th>Values</th></thead><tbody>';
        foreach ($attributes as $attributeName => $attributeValues) {
            echo '<tr><td>' . htmlentities($attributeName) . '</td><td><ul>';
            foreach ($attributeValues as $attributeValue) {
                echo '<li>' . htmlentities($attributeValue) . '</li>';
            }
            echo '</ul></td></tr>';
        }
        echo '</tbody></table>';
    } else {
        echo "<p>You don't have any attribute</p>";
    }

    echo '<p><a href="?slo" >Logout</a></p>';
} else {
    echo '<p><a href="?sso" >Login</a></p>';
    echo '<p><a href="?sso2" >Login and access to attrs.php page</a></p>';
}
