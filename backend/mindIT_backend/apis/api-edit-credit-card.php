<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$token = null;
        
if (isset($_POST['token'])) {$token = $_POST['token'];}

if (!is_null($token)) {

    require_once('jwt.php');

    // Get our server-side secret key from a secure location.
    $serverKey = '5f2b5cdbe5194f10b3241568fe4e2b24';

    try {
        $payload = JWT::decode($token, $serverKey, array('HS256'));

        //VALIDATE card id
        if( empty( $_POST['card_id'] ) ){
            sendError( 'missing card id', __LINE__ );
        }
        if( strlen($_POST['card_id']) < 5 ){
            sendError( 'card id too short', __LINE__ );
        }
        if( strlen($_POST['card_id']) > 55 ){
            sendError( 'card id too long', __LINE__ );
        }

        //VALIDATE exp month
        if( empty( $_POST['card_expMonth'] ) ){
            sendError( 'missing exp month', __LINE__ );
        }
        if( strlen($_POST['card_expMonth']) < 2 ){
            sendError( 'exp month too short', __LINE__ );
        }
        if( strlen($_POST['card_expMonth']) > 2 ){
            sendError( 'exp month too long', __LINE__ );
        }
        if( !ctype_digit($_POST['card_expMonth']) ){
            sendError( 'exp month needs to be numberic', __LINE__ );
        }

        //VALIDATE exp year
        if( empty( $_POST['card_expYear'] ) ){
            sendError( 'missing exp year', __LINE__ );
        }
        if( strlen($_POST['card_expYear']) < 4 ){
            sendError( 'exp year too short', __LINE__ );
        }
        if( strlen($_POST['card_expYear']) > 4 ){
            sendError( 'exp year too long', __LINE__ );
        }
        if( !ctype_digit($_POST['card_expYear']) ){
            sendError( 'exp year needs to be numberic', __LINE__ );
        }

        //VALIDATE CVV
        if( empty( $_POST['card_CVV'] ) ){
            sendError( 'missing CVV', __LINE__ );
        }
        if( strlen($_POST['card_CVV']) < 3 ){
            sendError( 'CVV too short', __LINE__ );
        }
        if( strlen($_POST['card_CVV']) > 3 ){
            sendError( 'CVV too long', __LINE__ );
        }
        if( !ctype_digit($_POST['card_CVV']) ){
            sendError( 'CVV needs to be numberic', __LINE__ );
        }

        require_once('../Credit_cards_class.php');

        $cards = new Cards();

        // CARD INFORMATION
        $card_id = $_POST['card_id'];
        $card_expMonth = $_POST['card_expMonth'];
        $card_expYear = $_POST['card_expYear'];
        $card_expiration_date = $card_expMonth . '/' . $card_expYear;
        $card_CVV = $_POST['card_CVV'];

        /* Get a list of all users in DB */
        $updateResult = $cards->update($card_id, $card_expiration_date, $card_CVV);

        if($updateResult !== true) {
            sendError( 'update went wrong', __LINE__ ); 
        } else {
            echo '{"status": 1, "message": "card updated successfully"}';
        }
    }
    catch(Exception $e) {
        sendError( 'error trying to check logged user', __LINE__ ); 
    }
} 
else {
    sendError( 'not autheticated with valid token', __LINE__ ); 
}


// ******************************* FUNCTIONS ************************************

function sendError($sErrorMessage, $iLineNumber){
    echo '{"status": 0, "message": "'.$sErrorMessage.'", "line": "'.$iLineNumber.'"}';
    exit;
}



