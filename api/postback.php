<?php

// REMOVE CHACHE
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// INIT

require_once("../../../../wp-load.php");
require __DIR__ .'/../vendor/autoload.php';
include 'lib.php';

global $woocommerce;
$order = new WC_Order( $_GET['order_id'] );
//$hoje = date('YmdHsm');
//echo($order);



$valid = true;

// GET PROTOCOL TO MAKE RESPONSE LINK
$protocol = "http://";

if (isset($_SERVER['HTTPS']) &&
    ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
    $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
  $protocol = 'https://';
}

// RESPONSE LINK
$callback_link = dirname("$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") . "/callback.php";

$posID = get_option('pos_id');
$posAutCode = get_option('pos_auth_code');

$lang = "pt";

if(strpos(get_locale(), 'en') !== false )
{
	$lang = "en";
}

// get Country code
$isoCodes = new \Sokil\IsoCodes\IsoCodesFactory();
$countryCode = $isoCodes->getCountries()->getByAlpha2($order->get_billing_country())->getNumericCode();

// get states code
// Read the JSON file 
$json = file_get_contents('iso_3166-2.json');
$state = $order->get_shipping_state();
// Decode the JSON file
$json_data = json_decode($json,true);

foreach ($json_data as &$value) {
	foreach ($value as &$value2) {
		if($value2["name"]==$order->get_shipping_state()){
			$state =$value2["code"];
			$state = substr($state,strpos($state, '-')+1,2);
			print_r($state);
			break;
		}
	}
}

$phone =  $order->get_billing_phone();
$dateNow = date('Ymd');
$dateNow2 = date('YmdHsm');
$purchaseRequest = 
	[

		"acctID"=> "x",
		"acctInfo"=> [
		  "chAccAgeInd"=> "04",
		  "chAccChange"=> $dateNow,
		  "chAccDate"=> $dateNow,
		  "chAccPwChange"=> $dateNow,
		  "chAccPwChangeInd"=> "05",
		  "suspiciousAccActivity"=> "01"

		],
	  
		"email"=> $order->get_billing_email(),
	  
		"addrMatch"=> "N",
		"billAddrCity"=> $order->get_billing_city(),
		"billAddrCountry"=> $countryCode,
		"billAddrLine1"=> $order->get_billing_address_1(),
		"billAddrLine2"=> $order->get_billing_address_2(),
		"billAddrLine3"=> $order->get_billing_address_2(),
		"billAddrPostCode"=> $order->get_billing_postcode(),
		"billAddrState"=> $state,
	  
		"shipAddrCity"=> $order->get_shipping_city(),
		"shipAddrCountry"=> $countryCode,
		"shipAddrLine1"=> $order->get_shipping_address_1(),
		"shipAddrPostCode"=> $order->get_shipping_postcode(),
		"shipAddrState"=> $state,
	  
		"workPhone"=> [
		  "cc"=> "1",
		  "subscriber"=> $order->get_billing_phone()
		],
	  
	   "mobilePhone"=> [
		  "cc"=> "1",
		  "subscriber"=> $order->get_billing_phone()
		],
	  
		"purchaseDate"=> $dateNow2
	  ];

//print_r(json_encode($purchaseRequest));
//die();

$text = utf8_encode(json_encode($purchaseRequest));
// Convert to Base64
$purchaseRequestEncoded = base64_encode($text);



$fields = [
	'transactionCode' => '1',
	'posID' => $posID,
	'merchantRef' => $_GET['order_id'],
	'merchantSession' => "R" . date('YmdHis'),
	'amount' => (int)$order->get_total(),
	'currency' => '132',
	'is3DSec' => '1',
	'urlMerchantResponse' => $callback_link,
	'languageMessages' => $lang,
	'timeStamp' => date('Y-m-d H:i:s'),
	'fingerprintversion' => '1',
	'purchaseRequest'=>$purchaseRequestEncoded
];



// GERAR PRINGER PRINT

$fields['fingerprint'] = GerarFingerPrintEnvio(
	$posAutCode, $fields['timeStamp'], $fields['amount'],
	$fields['merchantRef'], $fields['merchantSession'], $fields['posID'],
	 $fields['currency'], $fields['transactionCode'], '', ''
);

// URL PARA FAZER REQUISIÇÃO

$postUrl = get_option("vbv2_url");

if(empty($posID) || empty($posAutCode) || empty($postUrl))
	$valid = false;

if (!endsWith($postUrl, '?'))
	$postUrl .= "?";

$postUrl .= "FingerPrint=" . urlencode($fields["fingerprint"]) . "&TimeStamp=" . urlencode($fields["timeStamp"]) . "&FingerPrintVersion=" . urlencode($fields["fingerprintversion"]);

?>

<?php  if($valid){?>
<html>
	<head>
		<title>Pagamento vinti4</title>
		<style type="text/css">
			.box{
				text-align: center;
				margin: 32px 0;
				font-family: sans-serif;
				color: #444;
			}

			img{
				height: 64px;
			}
		</style>
	</head>
	<body onload='autoPost()'>
		<div class="box">
			
			<img src="../logo_vbv.png">
			<h3>Processando o pagamento...</h3>

			<form action='<?= $postUrl ?>' method='post'>
				<?php

					foreach ($fields as $key => $value) {
						echo "<input type='hidden' name='" . $key . "' value='" . $value . "'>";
					}

				?>
			</form>

		</div>		
		<script>
			
			document.forms[0].submit();

		</script>
	</body>
</html>
<?php  } else{?>
	<html>
	<head>
		<title>Pagamento vinti4</title>
	</head>
	<body onload='autoPost()'>
		<div>
			<h5>Configurações de <b> POS ID, POS AUTH CODE</b> e <b>VBV URL</b> pendentes!</h5>
		</div>
	</body>
</html>
<?php  }?>