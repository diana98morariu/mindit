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

        //VALIDATE card number
        if( empty( $_POST['card_number'] ) ){
            sendError( 'missing card number', __LINE__ );
        }
        if( strlen($_POST['card_number']) < 16 ){
            sendError( 'card number too short', __LINE__ );
        }
        if( strlen($_POST['card_number']) > 16 ){
            sendError( 'card number too long', __LINE__ );
        }
        if( !ctype_digit($_POST['card_number']) ){
            sendError( 'card number needs to be numberic', __LINE__ );
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

        // USER INFORMATION
        $user_id = $payload->userID;;

        // CARD INFORMATION
        $card_id = bin2hex(random_bytes(16));
        $card_expMonth = $_POST['card_expMonth'];
        $card_expYear = $_POST['card_expYear'];

        $card_IBAN = 'DK992000' . generateIBAN(10);
        $card_number = $_POST['card_number'];
        $card_expDate = $card_expMonth . '/' . $card_expYear;
        $card_CVV = $_POST['card_CVV'];
        $card_balance = rand(50000, 100000);
        $card_isPrimaryCard = $_POST['card_isPrimary'];
        $card_totalPaid = 0;

        $cardCreatedResult = createCreditCard($card_id, $card_IBAN, $card_number, $card_expDate, $card_CVV, $card_balance);
        if ( $cardCreatedResult === true) {

            if($card_isPrimaryCard == 1) {
                $creditCard = new Cards();
                $setAllCardsToNoPrimaryResult = $creditCard->set_user_cards_noPrimary($user_id);
    
                if($setAllCardsToNoPrimaryResult !== true) {
                    sendErrorMessage( 'set cards to no primary card went wrong', __LINE__ ); 
                } else {
                    $setPrimaryCardResult = $creditCard->set_primary_card($card_id, $user_id);
                    if($setPrimaryCardResult !== true) {
                        sendErrorMessage( 'get card went wrong', __LINE__ ); 
                    }
                }
            }

            createCardUserConnection($card_id, $user_id, $card_totalPaid, $card_isPrimaryCard);
            echo '{"status": 1, "message": "card registered succesfully"}';

        } else {
            sendError('card already registered', __LINE__ );
        }
    }
    catch(Exception $e) {
        sendError( 'error trying to check logged user', __LINE__ ); 
    }
} 
else {
    sendError( 'not autheticated with valid token', __LINE__ ); 
}


//**************************************** */
function sendError($sMessage, $iLineNumber){
    echo '{"status":0, "message":"'.$sMessage.'", "line":'.$iLineNumber.'}';
    exit;  
}

function createCreditCard($card_id, $card_IBAN, $card_number, $card_expDate, $card_CVV, $card_balance) {
    $cards = new Cards();

    /* Get a list of all cards in DB */
    $jCards = $cards->list();

    $totalCards = count((array)$jCards);
    
    if($totalCards === 0) {
        $addCardResponse = $cards->add($card_id, $card_IBAN, $card_number, $card_expDate, $card_CVV, $card_balance);  
        if( $addCardResponse !== true ){
            sendError('cannot create first credit card', __LINE__ );
        } 
        return true; 
    } else {
        $foundCard = false;
        foreach($jCards as $sKey => $jCard){
            if( $jCard->number === $card_number && $jCard->CVV === $card_CVV){
                $foundCard = true;
                return $jCard->id;
            }
        
            if($totalCards === 1 && $foundCard === false){  
                $addCardResponse = $cards->add($card_id, $card_IBAN, $card_number, $card_expDate, $card_CVV, $card_balance);  
        
                if( $addCardResponse !== true ){
                    sendError('cannot create new credit card', __LINE__ );
                } else {
                    return true;
                } 
            }    
            $totalCards--;
        }
    }
}

function generateIBAN($digits){
    $i = 0; 
    $IBAN = ""; 
    while($i < $digits){
        //generate a random number between 0 and 9.
        $IBAN .= mt_rand(0, 9);
        $i++;
    }
    return strval($IBAN);
}

function createCardUserConnection($id_card, $id_user, $amount, $isPrimaryCard) {
    $cards = new Cards();

    $connectionDoneResult = $cards->createCardUserConnection($id_card, $id_user, $amount, $isPrimaryCard);
    if( $connectionDoneResult !== true ){
        sendError('cannot create card user connection', __LINE__ );
    }
}