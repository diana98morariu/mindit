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

        $users = new Users();

        $getUserResult = $users->get_user($user_id);

        if($getUserResult === false) {
            sendErrorMessage( 'get user went wrong', __LINE__ ); 
        } else {
            $results = $getUserResult;
            $results->creditCards = array();
    
            require_once('../Credit_cards_class.php');
    
            $cards = new Cards();
    
            $getUserCardsResult = $cards->get_user_cards($user_id);
    
            if($getUserCardsResult === false) {
                sendErrorMessage( 'get user cards went wrong', __LINE__ ); 
            } else {
                $results->creditCards = $getUserCardsResult;
                echo json_encode($results);
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

