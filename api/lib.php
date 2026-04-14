<?php

function GerarFingerPrintEnvio
	(
	    $posAutCode, $timestamp, $amount,
	    $merchantRef, $merchantSession, $posID,
	    $currency, $transactionCode, $entityCode,
	    $referenceNumber
	)
{
	$toHash = base64_encode(hash('sha512', $posAutCode, true)) . $timestamp . ((int)((float)$amount * 1000))
			. $merchantRef . $merchantSession . $posID
			. $currency . $transactionCode . $entityCode . $referenceNumber
		;

	return base64_encode(hash('sha512', $toHash, true));
}

function GerarFingerPrintRespostaBemSucedida
	(
	    $posAutCode, $messageType, $clearingPeriod,
        $transactionID, $merchantReference, $merchantSession,
        $amount, $messageID, $pan,
        $merchantResponse, $timestamp, $reference,
        $entity, $clientReceipt, $additionalErrorMessage,
        $reloadCode
	)
{
	// EFETUAR O CALCULO CONFORME A DOCUMENTAÇÃO
	$toHash = base64_encode(hash('sha512', $posAutCode, true)) . $messageType . $clearingPeriod . $transactionID
			. $merchantReference . $merchantSession .
	        ((int)((float)$amount * 1000)) . $messageID . $pan .
	        $merchantResponse . $timestamp . $reference .
	        $entity . $clientReceipt . $additionalErrorMessage .
	        $reloadCode
		;

	return base64_encode(hash('sha512', $toHash, true));
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if(!$length)
    {
        return true;
    }
    return substr($haystack, -$length) === $needle;
}

?>