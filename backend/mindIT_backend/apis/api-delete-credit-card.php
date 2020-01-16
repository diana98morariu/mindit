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

        // GET CARD ID
        $card_id = $_GET['cardID'];

        require_once('../Credit_cards_class.php');

        $cards = new Cards();

        $userCards = $cards->get_user_cards($user_id);

        foreach($userCards as $sKey => $userCard){
            if($userCard->id === $card_id) {
                if($userCard->isPrimary == 1) {
                    sendErrorMessage( 'cannot delete primary card', __LINE__ ); 
                } else {
                    if($userCard->user === $user_id) {
                        $deleteResult = $cards->delete($card_id);
            
                        if($deleteResult !== true) {
                            sendErrorMessage( 'delete went wrong', __LINE__ ); 
                        } else {
                            echo '{"status": 1, "message": "card deleted successfully"}';
                            exit;
                        }       
                    } else {
                        sendErrorMessage( 'you are not authorized', __LINE__ ); 
                    }
                }
            }
        }
        sendErrorMessage( 'card not found', __LINE__ ); 
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



