Exemplo de Código em NodeJS


Dados a Enviar

Para efetuar o pedido de pagamento Online devem ser enviados os seguintes dados:



// CONFIGURACOES
var posID = "90051";
var posAutCode = "123456789A";


// OBTER DADOS DE PAGAMENTO
var amount = "1000";
var merchantRef = "R" + moment().format('YYYYMMDDHHmmss');
var merchantSession = "S" + moment().format('YYYYMMDDHHmmss');
var dateTime = moment().format('YYYY-MM-DD HH:mm:ss');


// URL PARA RECEBER RESPOSTA/RESULTADO DO PAGAMENTO
var responseUrl = "https://www.meusite.com/resposta-pagamento";	


// DADOS A ENVIAR
var formData = {
	transactionCode: "1",
	posID: posID,
	
	merchantRef: merchantRef,
	merchantSession: merchantSession,
	amount: amount,
	currency: "132",
	is3DSec: "1",
	urlMerchantResponse: responseUrl,
	languageMessages: "pt",
	timeStamp: dateTime,
	fingerprintversion: "1",
	entityCode: "",
	referenceNumber: ""
};



//GERAR PRINGER PRINT E ADICIONAR AOS DADOS DE ENVIOformData.fingerprint = GerarFingerPrintEnvio(		posAutCode, formData.timeStamp, formData.amount,		formData.merchantRef, formData.merchantSession, formData.posID,		formData.currency, formData.transactionCode, formData.entityCode, formData.referenceNumber	);


// URL PARA FAZER REQUISIÇÃO
var postURL = "https://mc.vinti4net.cv/Client_VbV_v2/biz_vbv_clientdata.jsp?FingerPrint=" + encodeURIComponent(formData.fingerprint ) + "&TimeStamp=" + encodeURIComponent(formData.timeStamp) + "&FingerPrintVersion=" + encodeURIComponent(formData.fingerprintversion);




Calcular Fingerprint

O FingerPrint  é gerado para garantir que os dados não foram alterados no envio, dando assim uma melhor

segurança ao processamento de pagamento.



O FingerPrint de envio de dados pode ser gerado utilizando a seguinte função:

function GerarFingerPrintEnvio(posAutCode, timestamp, amount, merchantRef, merchantSession, posID, currency, transactionCode, entityCode, referenceNumber)
{
    var toHash =
        GenerateSHA512StringToBase64(posAutCode) + timestamp + (Number(parseFloat(amount) * 1000)) +
        merchantRef.trim() + merchantSession.trim() + posID.trim() +
        currency.trim() + transactionCode.trim();
    if (entityCode)
        toHash += Number(entityCode.trim());
    if (referenceNumber)
        toHash += Number(referenceNumber.trim());
    return GenerateSHA512StringToBase64(toHash);
}

Funções auxiliares
function ToBase64 (u8) {
    return btoa(String.fromCharCode.apply(null, u8));
}
function GenerateSHA512StringToBase64(input){
	return ToBase64(sha512.digest(input));
}


Depedências utilizadas na função acima.

// CARREGAR BIBLIOTECA DE SHAJS
var sha512 = require('js-sha512');
// CARREGAR BTOA (BIBLIOTECA PARA TRANSFORMAR BINARIO PARA BASE64)
var btoa = require('btoa');


O FingerPrint de validar a resposta em caso de sucesso calcula-se de forma semelhante porêm com campos adicionais,

o desenvolvedor deve calcular o finger print de resposta seguindo a documentação disponibilizada pela SISP. Caso o desenvolvedor não valide o FingerPrint de resposta podem ser feitas compras fraudulentas no seu site ou sua aplicação.

function GerarFingerPrintRespostaBemSucedida
(
    posAutCode, messageType, clearingPeriod,
    transactionID, merchantReference, merchantSession,
    amount, messageID, pan,
    merchantResponse, timestamp, reference,
    entity, clientReceipt, additionalErrorMessage,
    reloadCode
)
{
	// EFETUAR O CALCULO CONFORME A DOCUMENTAÇÃO
}




Criar Formulário de Envio

Para enviar os dados o programador deve gerar um formulário e permitir que este faça post de forma automática.



Segue o código de Exemplo.

// CONSTRUIR UM FORM PARA FAZER POST AUTOMATICO
var formHtml = "<html><head><title>Pagamento vinti4</title></head><body onload='autoPost()'><div><h5>Processando o pagamento...</h5>";
formHtml += "<form action='" + postURL + "' method='post'>";
Object.keys(formData).forEach(function(key) {
  formHtml += "<input type='hidden' name='" + key + "' value='" + formData[key] + "'>";
});	
formHtml += "</form>";
formHtml += "<script>function autoPost(){document.forms[0].submit();}</script></body></html>";
res.send(formHtml);




Validar Resposta de Pagamento

No End Point (URL a receber resposta de pagamento) para validar o resultado de pagamento deve-se verificar se a resposta é de sucesso ou não, recordando que em caso de sucesso o campo messageType terá um dos seguintes valores "8", "10", "M" ou "P".

Além de verificar o campo messageType deve-se verificar se o FingerPrint recebido no campo resultFingerPrint é igual ao esperado que é calculado conforme a utilização da função GerarFingerPrintRespostaBemSucedida.



app.post("/resposta-pagamento", urlencodedParser, function(req, res){
	
	// CONSTANTES DE RESPOSTA DE SUCESSOS
	var successMessageType = ["8", "10", "M", "P"];
	if(successMessageType.includes(req.body.messageType))
	{
		var posAutCode = "123456789A";
		var fingerPrintCalculado = GerarFingerPrintRespostaBemSucedida(
		    posAutCode , req.body.messageType , req.body.merchantRespCP ,
	            req.body.merchantRespTid , req.body.merchantRespMerchantRef , req.body.merchantRespMerchantSession ,
	            req.body.merchantRespPurchaseAmount , req.body.merchantRespMessageID , req.body.merchantRespPan ,
	            req.body.merchantResp , req.body.merchantRespTimeStamp , req.body.merchantRespReferenceNumber ,
	            req.body.merchantRespEntityCode , req.body.merchantRespClientReceipt , trim(req.body.merchantRespAdditionalErrorMessage) ,
	            req.body.merchantRespReloadCode
		);
		/*ATENÇÃO: VALIDAR FRINGERPRINT DE SUCESSO
		PARA OBTER UMA MELHOR GARANTIA DE SEGURANÇA*/
		if(req.body.resultFingerPrint == fingerPrintCalculado)
			res.send("Pagamento bem sucedido");
		else
			res.send("Pagamento sem sucesso: Finger Print de Resposta Inválida");
	}
	else if(req.body.UserCancelled == "true")
	{
		res.send("Utilizador cancelou a requisição de compra");
	}
	else
	{
		res.send("Pagamento sem sucesso");
	}	
});
