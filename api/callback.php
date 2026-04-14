<?php

require_once("../../../../wp-load.php");
include 'lib.php';

//$preffix = "/wordpress";
$preffix = "../../../../";

$posAutCode = get_option('pos_auth_code');

$error = "";
$errorDetail = "";
$errorAditional = "";

$successMessageType = array('8', '10', 'P', 'M');
if(isset($_POST["messageType"]) && in_array($_POST["messageType"], $successMessageType))
{
	// FAZER VALIDAÇÕES DE FINGERPRINT
	$fingerPrintCalculado = GerarFingerPrintRespostaBemSucedida(
	    $posAutCode , $_POST["messageType"] , $_POST["merchantRespCP"] ,
        $_POST["merchantRespTid"] , $_POST["merchantRespMerchantRef"] , $_POST["merchantRespMerchantSession"] ,
        $_POST["merchantRespPurchaseAmount"] , $_POST["merchantRespMessageID"] , $_POST["merchantRespPan"] ,
        $_POST["merchantResp"] , $_POST["merchantRespTimeStamp"] , $_POST["merchantRespReferenceNumber"] ,
        $_POST["merchantRespEntityCode"] , $_POST["merchantRespClientReceipt"] , trim($_POST["merchantRespAdditionalErrorMessage"]) ,
        $_POST["merchantRespReloadCode"]
	);
	
	if($_POST["resultFingerPrint"] == $fingerPrintCalculado)
	{
		// ENCERRAR O CARRINHO DE COMPRAS
		global $woocommerce;
		$order = new WC_Order($_POST['merchantRespMerchantRef']);

		if($_POST['merchantRespPurchaseAmount'] == $order->get_total())
		{
			// Mark as completed
		    $order->update_status('completed', __( 'Awaiting cheque payment', 'woocommerce' ));

		    // Reduce stock levels
		    $order->reduce_order_stock();

		    // Remove cart
		    $woocommerce->cart->empty_cart();

		    // REDIRECIONAR PARA PAGINA DE SUCESSO
			header("Location: " . $order->get_checkout_order_received_url());
		}
		else
		{
			header("Location: " . $preffix . "checkout-sem-sucesso?error=amount");
		}
	}
	else
	{
		header("Location: " . $preffix . "checkout-sem-sucesso?error=fgpt");
	}
	
}
else
{
	if(isset($_POST["messageType"]))
	{
		//header("Location: $preffix/checkout-sem-sucesso?error=" . $_POST["merchantRespErrorDetail"] . "&detail=" . $_POST["merchantRespErrorDescription"]);
		$error = $_POST["merchantRespErrorDetail"];
		$errorDetail = $_POST["merchantRespErrorDescription"];
		$errorAditional = $_POST["merchantRespAdditionalErrorMessage"];
	}
	elseif(isset($_GET["messageType"]))
	{
		//header("Location: $preffix/checkout-sem-sucesso?error=" . $_GET["merchantRespErrorDetail"] . "&detail=" . $_GET["merchantRespErrorDescription"]);
		$error = $_GET["merchantRespErrorDetail"];
		$errorDetail = $_GET["merchantRespErrorDescription"];
		$errorAditional = $_GET["merchantRespAdditionalErrorMessage"];
	}
	else
	{
		$error = "Erro na realização de Pagamento";
		$errorDetail = "Ocorreu algo inesperado.";
		//header("Location: $preffix/checkout-sem-sucesso?error=mt");
	}
}

?>

<?php /* Template Name: CustomPageT1 */ ?>
 
<?php get_header(); ?>
 
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

	   <h3><?php echo $errorAditional; ?></h3>

       <p><?php echo $error; ?></p>
       <p><?php echo $errorDetail; ?></p>
       

       <a href='<?php echo wc_get_checkout_url();?>'>Tentar Novamente</a>
 
    </main> 
 
</div>
 
<?php get_footer(); ?>