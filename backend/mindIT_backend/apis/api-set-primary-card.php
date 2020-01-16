<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$token = null;
        
if (isset($_GET['token'])) {$token = $_GET['token'];}

if (!is_null($token)) {

    require_once('jwt.php');

    // Get our server-side secret key from a secure location.
    $serverKey = '5f2b5cdbe5194f10b3241568fe4e2b24';

    try {
        $payload = JWT::decode($token, $serverKey, array('HS256'));

        require_once('../Users_class.php');
      
        // USER INFORMATION
        $user_id = $payload->userID;

        require_once('../Credit_cards_class.php');

        $creditCard = new Cards();

        $card_id = $_GET['cardID'];

        $setAllCardsToNoPrimaryResult = $creditCard->set_user_cards_noPrimary($user_id);

        if($setAllCardsToNoPrimaryResult !== true) {
            sendErrorMessage( 'set cards to no primary card went wrong', __LINE__ ); 
        } else {
            $setPrimaryCardResult = $creditCard->set_primary_card($card_id, $user_id);
            if($setPrimaryCardResult !== true) {
                sendErrorMessage( 'get card went wrong', __LINE__ ); 
            } else {
                echo '{"status": 1, "message": "card set to primary successfully"}';
            }
        }
    }
    catch(Exception $e) {
        sendErrorMessage( 'error trying to check logged user', __LINE__ ); 
    }
} 
else {
    sendErrorMessage( 'not autheticated with valid token', __LINE__ ); 
}
// ******************************* FUNCTIONS ************************************

function sendErrorMessage($sErrorMessage, $iLineNumber){
    echo '{"status": 0, "message": "'.$sErrorMessage.'", "line": "'.$iLineNumber.'"}';
    exit;
}



