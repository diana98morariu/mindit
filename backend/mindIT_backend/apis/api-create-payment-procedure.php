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

        if (strpos($user_id, 'administrator040e2b167b33b') === false) {
            sendErrorMessage( 'not authorized', __LINE__ ); 
        }

        require_once('../Payments_class.php');
        require_once('../Users_class.php');
        require_once('../Credit_cards_class.php');

        $payment = new Transaction();
        $cards = new Cards();
        $users = new Users();

        /* Get a list of all active users in DB */
        $usersList = $users->list_active_users();
        $deletedUsers = 0;

        foreach($usersList as $sKey => $oneUser) {
            // CHECKING IF IT IS NOT ADMINISTRATOR
            if (strpos($oneUser->id, 'administrator040e2b167b33b') === false) {
            
                // GET USER PRIMARY CARD
                $userPrimaryCard = $cards->get_primary_card($oneUser->id);
                if($userPrimaryCard === false) {
                    sendErrorMessage( 'fetching primary card went wrong', __LINE__ ); 
                } else {
                    // CHECK USER PRIMARY'S CARD BALANCE
                    if(intval($userPrimaryCard->card_balance) >= 20) {
                        // EXECUTE PROCEDURE
                        $resultPayment = $payment->execute_payment($oneUser->id, $userPrimaryCard->card_id);
                        if($resultPayment !== true) {
                            sendErrorMessage( 'execute payment went wrong', __LINE__ ); 
                        }
                    } else {
                        // IF NOT ENOUGH MONEY ON PRIMARY CARD SUSPEND THE ACCOUNT
                        $users->delete($oneUser->id);
                        $deletedUsers++;
                    }
                }
            }
        }
        echo '{"status": 1, "message": "all payments successfully done", "deletedUsers": '.$deletedUsers.'}';
    }
    catch(Exception $e) {
        sendErrorMessage( 'error trying to check logged user', __LINE__ ); 
    }
} 
else {
    sendErrorMessage( 'not autheticated with valid token', __LINE__ ); 
}



function sendErrorMessage($sErrorMessage, $iLineNumber){
    echo '{"status": 0, "message": "'.$sErrorMessage.'", "line": "'.$iLineNumber.'"}';
    exit;
}