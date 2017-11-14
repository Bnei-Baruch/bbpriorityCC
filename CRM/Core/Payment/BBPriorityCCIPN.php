<?php

class CRM_Core_Payment_BBPriorityCCIPN extends CRM_Core_Payment_BaseIPN
{
    CONST BBP_RESPONSE_CODE_ACCEPTED = '000';
    private $_errors;
    private $_bbpAPI;

    function __construct()
    {
        parent::__construct();

        $this->_bbpAPI = new PelecardAPI;
        $this->errors = [
            '000' => 'Permitted transaction.',
            '001' => 'The card is blocked, confiscate it.',
            '002' => 'The card is stolen, confiscate it.',
            '003' => 'Contact the credit company.',
            '004' => 'Refusal by credit company.',
            '005' => 'The card is forged, confiscate it.',
            '006' => 'Incorrect CVV/ID.',
            '007' => 'Incorrect CAVV/ECI/UCAF.',
            '008' => 'An error occurred while building access key for blocked card files.',
            '009' => 'No communication. Please try again or contact System Administration',
            '010' => 'The program was stopped by user`s command (ESC) or COM PORT can\'t be open (Windows)',
            '011' => 'The acquirer is not authorized for foreign currency transactions',
            '012' => 'This card is not permitted for foreign currency transactions',
            '013' => 'The terminal is not permitted for foreign currency charge/discharge into this card',
            '014' => 'This card is not Supported.',
            '015' => 'Track 2 (Magnetic) does not match the typed data.',
            '016' => 'Additional required data was entered/not entered as opposed to terminal Settings (Z field).',
            '017' => 'Last 4 digits were not entered (W field).',
            '019' => 'Entry in INT_IN file is shorter than 16 characters.',
            '020' => 'The input file (INT_IN) does not exist.',
            '021' => 'Blocked cards file (NEG) does not exist or has not been updated, transmit or request authorization for each transaction.',
            '022' => 'One of the parameter files/vectors does not exist.',
            '023' => 'Date file (DATA) does not exist.',
            '024' => 'Format file (START) does not exist.',
            '025' => 'The difference in days in the blocked cards input is too large, transmit or request authorization for each transaction.',
            '026' => 'The difference in generations in the blocked cards input is too large, transmit or request authorization for each transaction.',
            '027' => 'When the magnetic strip is not completely entered, define the transaction as a telephone number or signature only.',
            '028' => 'The central terminal number was not entered into the defined main supplier terminal.',
            '029' => 'The beneficiary number was not entered into the defined main beneficiary terminal.',
            '030' => 'The supplier/beneficiary number was entered, however the terminal was not updated as the main supplier/beneficiary.',
            '031' => 'The beneficiary number was entered, however the terminal was updated as the main supplier.',
            '032' => 'Old transactions, transmit or request authorization for each transaction.',
            '033' => 'Defective card.',
            '034' => 'This card is not permitted for this terminal or is not authorized for this type of transaction.',
            '035' => 'This card is not permitted for this transaction or type of credit.',
            '036' => 'Expired card.',
            '037' => 'Installment error, the amount of transactions needs to be equal to: first installment plus fixed installments times number of installments.',
            '038' => 'Unable to execute a debit transaction that is higher than the credit card`s ceiling.',
            '039' => 'Incorrect control number.',
            '040' => 'The beneficiary and supplier numbers were entered, however the terminal is defined as main.',
            '041' => 'The transaction`s amount exceeds the ceiling when the input file contains J1, J2 or J3 (contact prohibited).',
            '042' => 'The card is blocked for the supplier where input file contains J1, J2 or J3 (contact prohibited).',
            '043' => 'Random where input file contains J1 (contact prohibited).',
            '044' => 'The terminal is prohibited from requesting authorization without transaction (J5).',
            '045' => 'The terminal is prohibited for supplier-initiated authorization request (J6).',
            '046' => 'The terminal must request authorization where the input file contains J1, J2 or J3 (contact prohibited).',
            '047' => 'Secret code must be entered where input file contains J1, J2 or J3 (contact prohibited).',
            '051' => 'Incorrect vehicle number.',
            '052' => 'The number of the distance meter was not entered.',
            '053' => 'The terminal is not defined as gas station (petrol card or incorrect transaction code was used).',
            '057' => 'An ID number is required (for Israeli cards only) but was not entered.',
            '058' => 'CVV is required but was not entered.',
            '059' => 'CVV and ID number are required (for Israeli cards only) but were not entered.',
            '060' => 'ABS attachment was not found at the beginning of the input data in memory.',
            '061' => 'The card number was either not found or found twice.',
            '062' => 'Incorrect transaction type.',
            '063' => 'Incorrect transaction code.',
            '064' => 'Incorrect credit type.',
            '065' => 'Incorrect currency.',
            '066' => 'The first installment and/or fixed payment are for non-installment type of credit.',
            '067' => 'Number of installments exist for the type of credit that does not require this.',
            '068' => 'Linkage to dollar or index is possible only for installment credit.',
            '069' => 'The magnetic strip is too short.',
            '070' => 'The PIN code device is not defined.',
            '071' => 'Must enter the PIN code number.',
            '072' => 'Smart card reader not available - use the magnetic reader.',
            '073' => 'Must use the Smart card reader.',
            '074' => 'Denied - locked card.',
            '075' => 'Denied - Smart card reader action didn\'t end in the correct time.',
            '076' => 'Denied - Data from smart card reader not defined in system.',
            '077' => 'Incorrect PIN code.',
            '079' => 'Currency does not exist in vector 59.',
            '080' => 'The club code entered does not match the credit type.',
            '090' => 'Cannot cancel charge transaction.Make charging deal.',
            '091' => 'Cannot cancel charge transaction.Make discharge transaction ',
            '092' => 'Cannot cancel charge transaction.Please create a credit transaction.',
            '099' => 'Unable to read/write/open the TRAN file.',
            '101' => 'No authorization from credit company for clearance. ',
            '106' => 'The terminal is not permitted to send queries for immediate debit cards.',
            '107' => 'The transaction amount is too large, divide it into a number of transactions.',
            '108' => 'The terminal is not authorized to execute forced transactions.',
            '109' => 'The terminal is not authorized for cards with service code 587.',
            '110' => 'The terminal is not authorized for immediate debit cards.',
            '111' => 'The terminal is not authorized for installment transactions.',
            '112' => 'The terminal is authorized for installment transactions only',
            '113' => 'The terminal is not authorized for telephone transactions.',
            '114' => 'The terminal is not authorized for signature-only transactions.',
            '115' => 'The terminal is not authorized for foreign currency transactions, or transaction is not authorized.',
            '116' => 'The terminal is not authorized for club transactions.',
            '117' => 'The terminal is not authorized for star /point/mile transactions.',
            '118' => 'The terminal is not authorized for Isracredit credit.',
            '119' => 'The terminal is not authorized for Amex credit.',
            '120' => 'The terminal is not authorized for dollar linkage.',
            '121' => 'The terminal is not authorized for index linkage.',
            '122' => 'The terminal is not authorized for index linkage with foreign cards.',
            '123' => 'The terminal is not authorized for star',
            '124' => 'The terminal is not authorized for Isra 36 credit.',
            '125' => 'The terminal is not authorized for Amex 36 credit.',
            '126' => 'The terminal is not authorized for this club code.',
            '127' => 'The terminal is not authorized for immediate debit transactions (except for immediate debit cards ).',
            '128' => 'The terminal is not authorized to accept Visa card staring with 3.',
            '129' => 'The terminal is not authorized to execute credit transactions above the ceiling.',
            '130' => 'The card is not permitted to execute club transactions.',
            '131' => 'The card is not permitted to execute star/point/mile transactions.',
            '132' => 'The card is not permitted to execute dollar transactions (regular or telephone).',
            '133' => 'The card is not valid according to Isracard `s list of valid cards.',
            '134' => 'Defective card according to system definitions (Isracard VECTOR1), error in the number of figures on the card.',
            '135' => 'The card is not permitted to execute dollar transactions according to system definitions (Isracard VECTOR1).',
            '136' => 'The card belongs to a group that is not permitted to execute transactions according to system definitions (Visa VECTOR 20).',
            '137' => 'The card` s prefix (7 figures) is invalid according to system definitions (Diners VECTOR21).',
            '138' => 'The card is not permitted to carry out installment transactions according to Isracard `s list of valid cards.',
            '139' => 'The number of installments is too large according to Isracard` s list of valid cards.',
            '140' => 'Visa and Diners cards are not permitted for club installment transactions.',
            '141' => 'Series of cards are not valid according to system definition (Isracard VECTOR5).',
            '142' => 'Invalid service code according to system definitions (Isracard VECTOR6).',
            '143' => 'The card `s prefix (2 figures) is invalid according to system definitions (Isracard VECTOR7).',
            '144' => 'Invalid service code according to system definitions (Visa VECTOR12).',
            '145' => 'Invalid service code according to system definitions (Visa VECTOR13).',
            '146' => 'Immediate debit card is prohibited for executing credit transaction.',
            '147' => 'The card is not permitted to execute installment transactions according to Alpha vector no. 31.',
            '148' => 'The card is not permitted for telephone and signature-only transactions according to Alpha vector no. 31.',
            '149' => 'The card is not permitted for telephone transactions according to Alpha vector no. 31.',
            '150' => 'Credit is not approved for immediate debit cards.',
            '151' => 'Credit is not approved for foreign cards.',
            '152' => 'Incorrect club code.',
            '153' => 'The card is not permitted to execute flexible credit transactions (Adif/30+) according to system definitions (Diners VECTOR21).',
            '154' => 'The card is not permitted to execute immediate debit transactions according to system definitions (Diners VECTOR21).',
            '155' => 'The payment amount is too low for credit transactions.',
            '156' => 'Incorrect number of installments for credit transaction.',
            '157' => 'Zero ceiling for this type of card for regular credit or Credit transaction.',
            '158' => 'ero ceiling for this type of card for immediate debit credit transaction.',
            '159' => 'Zero ceiling for this type of card for immediate debit in dollars.',
            '160' => 'Zero ceiling for this type of card for telephone transaction.',
            '161' => 'Zero ceiling for this type of card for credit transaction.',
            '162' => 'Zero ceiling for this type of card for installment transaction.',
            '163' => 'American Express card issued abroad not permitted for instalments transaction.',
            '164' => 'JCB cards are only permitted to carry out regular credit transactions.',
            '165' => 'The amount in stars/points/miles is higher than the transaction amount.',
            '166' => 'The club card is not within terminal range.',
            '167' => 'Star/point/mile transactions cannot be executed.',
            '168' => 'Dollar transactions cannot be executed for this type of card.',
            '169' => 'Credit transactions cannot be executed with other than regular credit.',
            '170' => 'Amount of discount on stars/points/miles is higher than the permitted.',
            '171' => 'Forced transactions cannot be executed with credit/immediate debit card.',
            '172' => 'The previous transaction cannot be cancelled (credit transaction or card number are not identical).',
            '173' => 'Double transaction.',
            '174' => 'The terminal is not permitted for index linkage of this type of credit.',
            '175' => 'The terminal is not permitted for dollar linkage of this type of credit.',
            '176' => 'The card is invalid according to system definitions (Isracard VECTOR1).',
            '177' => 'Unable to execute the self-service transaction if the gas station does not have self service.',
            '178' => 'Credit transactions are forbidden with stars/points/miles.',
            '179' => 'Dollar credit transactions are forbidden on tourist cards.',
            '180' => 'Phone transactions are not permitted on Club cards.',
            '200' => 'Application error.',
            '201' => 'Error receiving encrypted data',
            '205' => 'Transaction amount missing or zero.',
            '301' => 'Timeout on clearing page.',
            '306' => 'No communication to Pelecard.',
            '308' => 'Doubled transaction.',
            '404' => 'Terminal number does not exist. ',
            '500' => 'Terminal executes broadcast and/or updating data. Please try again later. ',
            '501' => 'User name and/or password not correct. Please call support team. ',
            '502' => 'User password has expired. Please contact support team. ',
            '503' => 'Locked user. Please contact support team. ',
            '505' => 'Blocked terminal. Please contact account team. ',
            '506' => 'Token number abnormal. ',
            '507' => 'User is not authorized in this terminal. ',
            '508' => 'Validity structure invalid. Use MMYY structure only. ',
            '509' => 'SSL verifying access is blocked. Please contact support team. ',
            '510' => 'Data not exist. ',
            '555' => 'Cancel url status code.',
            '597' => 'General error. Please contact support team. ',
            '598' => 'Necessary values are missing/wrong. ',
            '599' => 'General error. Repeat action. ',
            '999' => 'Necessary values missing to complete installments transaction.',
        ];
    }

    function single(&$input, &$ids, &$objects, $recur = FALSE, $first = FALSE)
    {
        $contribution = &$objects['contribution'];

        $transaction = new CRM_Core_Transaction();
        if ($input['PelecardStatusCode'] != self::BBP_RESPONSE_CODE_ACCEPTED) {
            //      $error = self::trimAmount($input['Ds_Response']);
            //      if (array_key_exists($error, $this->_errors)) {
            //        $input['reasonCode'] = $this->_errors[$error];
            //      }

            CRM_Core_Error::debug_log_message("BBPCC IPN Response: About to cancel contribution \n input: " . print_r($input, TRUE) . "\n ids: " . print_r($ids, TRUE) . "\n objects: " . print_r($objects, TRUE));
            return $this->cancelled($objects, $transaction, $input);
        }
        // check if contribution is already completed, if so we ignore this ipn
        if ($contribution->contribution_status_id == 1) {
            $transaction->commit();
            CRM_Core_Error::debug_log_message("returning since contribution has already been handled");
            echo "Success: Contribution has already been handled<p>";
            return TRUE;
        }

        $this->completeTransaction($input, $ids, $objects, $transaction, $recur);
        return true;
    }

    function getInput(&$input, &$ids)
    {
        $input = array(
            // GET Parameters
            'module' => self::retrieve('md', 'String', 'GET', true),
            'component' => self::retrieve('md', 'String', 'GET', true),
            'qfKey' => self::retrieve('qfKey', 'String', 'GET', false),
            'contributionID' => self::retrieve('contributionID', 'String', 'GET', true),
            'contactID' => self::retrieve('contactID', 'String', 'GET', true),
            'eventID' => self::retrieve('eventID', 'String', 'GET', false),
            'participantID' => self::retrieve('participantID', 'String', 'GET', false),
            'membershipID' => self::retrieve('membershipID', 'String', 'GET', false),
            'contributionPageID' => self::retrieve('contributionPageID', 'String', 'GET', false),
            'relatedContactID' => self::retrieve('relatedContactID', 'String', 'GET', false),
            'onBehalfDupeAlert' => self::retrieve('onBehalfDupeAlert', 'String', 'GET', false),
            'returnURL' => self::retrieve('returnURL', 'String', 'GET', false),
            // POST Parameters
            'PelecardTransactionId' => self::retrieve('PelecardTransactionId', 'String', 'POST', true),
            'PelecardStatusCode' => self::retrieve('PelecardStatusCode', 'String', 'POST', true),
            'ConfirmationKey' => self::retrieve('ConfirmationKey', 'String', 'POST', true),
            'UserKey' => self::retrieve('UserKey', 'String', 'POST', true),
        );

        $ids = array(
            'contribution' => $input['contributionID'],
            'contact' => $input['contactID'],
        );
        if ($input['module'] == "event") {
            $ids['event'] = $input['eventID'];
            $ids['participant'] = $input['participantID'];
        } else {
            $ids['membership'] = $input['membershipID'];
            $ids['related_contact'] = $input['relatedContactID'];
            $ids['onbehalf_dupe_alert'] = $input['onBehalfDupeAlert'];
        }
    }

    function validateResult($paymentProcessor, &$input, &$ids, &$objects, $required = TRUE, $paymentProcessorID = NULL)
    {
        // This also initializes $objects
        if (!parent::validateData($input, $ids, $objects, $required, $paymentProcessorID)) {
            CRM_Core_Error::debug_log_message("\n\nparent::validateResult: VALIDATION ERROR\n\n");
            return false;
        }

        if ($input['UserKey'] != $input['qfKey']) {
            CRM_Core_Error::debug_log_message("Pelecard Response param UserKey is invalid");
            return false;
        }

        $contribution = &$objects['contribution'];
        $input['amount'] = $contribution->total_amount;
        $valid = $this->_bbpAPI->validateResponse($paymentProcessor, $input, $contribution, $this->errors);

        if (!$valid) {
            CRM_Core_Error::debug_log_message("Pelecard Response is invalid");
            return false;
        }

        $contribution->txrn_id = $valid;
        return true;
    }

    static function retrieve($name, $type, $location = 'POST', $abort = true)
    {
        static $store = null;
        $value = CRM_Utils_Request::retrieve($name, $type, $store, false, null, $location);
        if ($abort && $value === null) {
            CRM_Core_Error::debug_log_message("Could not find an entry for $name in $location");
            echo "Failure: Missing Parameter: $name<p>";
            exit();
        }
        return $value;
    }

    static function trimAmount($amount, $pad = '0')
    {
        return ltrim(trim($amount), $pad);
    }
}
