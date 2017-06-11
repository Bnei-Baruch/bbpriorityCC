<?php
class PelecardAPI {
  /******  Array of request data ******/
  var $vars_pay = array();

  /******  Set parameter ******/
  function setParameter($key, $value) {
    $this->vars_pay[$key] = $value;
  }

  /******  Get parameter ******/
  function getParameter($key) {
    if (isset($this->vars_pay[$key])) {
      return $this->vars_pay[$key];
    } else {
      return NULL;
    }
  }

  /****** Request URL from PeleCard ******/
  function getRedirectUrl() {
    // Push constant parameters
    $this->setParameter("ActionType", 'J4');
    $this->setParameter("CardHolderName", 'hide');
    $this->setParameter("CustomerIdField", 'hide');
    $this->setParameter("Cvv2Field", 'must');
    $this->setParameter("EmailField", 'hide');
    $this->setParameter("TelField", 'hide');
    $this->setParameter("FeedbackDataTransferMethod", 'POST');
    $this->setParameter("FirstPayment", 'auto');
    $this->setParameter("ShopNo", 1000); // TODO: What should be shop number?
    $this->setParameter("SetFocus", 'CC');
    $this->setParameter("HiddenPelecardLogo", true);
    $cards = [
      "Amex" => true,
      "Diners" => false,
      "Isra" => true,
      "Master" => true,
      "Visa" => true,
    ];
    $this->setParameter("SupportedCards", $cards);

    $json = $this->arrayToJson();
    $this->connect($json, '/init');

    $error = $this->getParameter('Error');
    if (is_array($error)) {
      if ($error['ErrCode'] > 0) {
        return array($error['ErrCode'], $error['ErrMsg']);
      } else {
        return array(0, $this->getParameter('URL'));
      }
    }
  }

  function connect($params, $action) {
    $ch = curl_init('https://gateway20.pelecard.biz/PaymentGW' . $action);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
      array('Content-Type: application/json; charset=UTF-8', 'Content-Length: ' . strlen($params)));
    $result = curl_exec($ch);
    if ($result == '0') {
      $this->vars_pay = [
        'Error' => array( -1, 'Error')
      ];
    } elseif ($result == '1') {
      $this->vars_pay = [
        'Identified' => array( 0, 'Identified')
      ];
    } else {
      $this->stringToArray($result);
    }
  }

  /******  Convert Hash to JSON ******/
  function arrayToJson() {
    return json_encode($this->vars_pay); //(PHP 5 >= 5.2.0)
  }

  /******  Convert String to Hash ******/
  function stringToArray($data) {
    if (is_array($data)) {
      $this->vars_pay = $data;
    } else {
      $this->vars_pay = json_decode($data, true); //(PHP 5 >= 5.2.0)
    }
  }

  /****** Validate Response ******/
  function validateResponse($processor, $data) {
    $PelecardTransactionId = $data['PelecardTransactionId'];
    $PelecardStatusCode = $data['PelecardStatusCode'];
    $ConfirmationKey = $data['ConfirmationKey'];
    $UserKey = $data['UserKey'];
    $amount = $data['amount'];

    $this->vars_pay = [];
    $this->setParameter("user", $processor["user_name"]);
    $this->setParameter("password", $processor["password"]);
    $this->setParameter("terminal", $processor["signature"]);
    $this->setParameter("TransactionId", $PelecardTransactionId);

    $json = $this->arrayToJson();
    $this->connect($json, '/GetTransaction');

    $error = $this->getParameter('Error');
    if (is_array($error) && $error['ErrCode'] > 0) {
      CRM_Core_Error::debug_log_message("Error[{error}]: {message}", ["error" => $error['ErrCode'], "message" => $error['ErrMsg']]);
      return false;
    }

    $this->stringToArray($this->getParameter('ResultData'));

    $ShvaResult = $this->getParameter('ShvaResult');
    $VoucherId = $this->getParameter('VoucherId');
    $TransactionPelecardId = $this->getParameter('TransactionPelecardId');
    $ShvaFileNumber = $this->getParameter('ShvaFileNumber');
    $StationNumber = $this->getParameter('StationNumber');
    $Reciept = $this->getParameter('Reciept');
    $JParam = $this->getParameter('JParam');
    $CreditCardNumber = $this->getParameter('CreditCardNumber');
    $CreditCardExpDate = $this->getParameter('CreditCardExpDate');
    $CreditCardCompanyClearer = $this->getParameter('CreditCardCompanyClearer');
    $CreditCardCompanyIssuer = $this->getParameter('CreditCardCompanyIssuer');
    $CreditType = $this->getParameter('CreditType');
    $CreditCardAbroadCard = $this->getParameter('CreditCardAbroadCard');
    $DebitType = $this->getParameter('DebitType');
    $DebitCode = $this->getParameter('DebitCode');
    $DebitTotal = $this->getParameter('DebitTotal');
    $DebitCurrency = $this->getParameter('DebitCurrency');
    $TotalPayments = $this->getParameter('TotalPayments');
    $FirstPaymentTotal = $this->getParameter('FirstPaymentTotal');
    $FixedPaymentTotal = $this->getParameter('FixedPaymentTotal');
    $CreditCardBrand = $this->getParameter('CreditCardBrand');
    $CardHebrewName = $this->getParameter('CardHebrewName');
    $ShvaOutput = $this->getParameter('ShvaOutput');
    $ApprovedBy = $this->getParameter('ApprovedBy');
    $TransactionInitTime = $this->getParameter('TransactionInitTime');
    $TransactionUpdateTime = $this->getParameter('TransactionUpdateTime');

    $this->vars_pay = [];
    $this->setParameter("ConfirmationKey", $ConfirmationKey);
    $this->setParameter("UniqueKey", $UserKey);
    $this->setParameter("TotalX100", $amount * 100);

    $json = $this->arrayToJson();
    $this->connect($json, '/ValidateByUniqueKey');

    $error = $this->getParameter('Error');
    if (is_array($error) && $error['ErrCode'] > 0) {
      CRM_Core_Error::debug_log_message("Error[{error}]: {message}", ["error" => $error['ErrCode'], "message" => $error['ErrMsg']]);
      return false;
    }

    // TODO: Store all parameters in DB
    return true;
  }
}

/******  Base64 Functions  ******/
function base64_url_encode($input) {
  return strtr(base64_encode($input), '+/', '-_');
}

function encodeBase64($data) {
  $data = base64_encode($data);
  return $data;
}

function base64_url_decode($input) {
  return base64_decode(strtr($input, '-_', '+/'));
}
