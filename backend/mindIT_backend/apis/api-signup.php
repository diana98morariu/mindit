<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

//VALIDATE email
if( empty( $_POST['user_email'] ) ){
    sendError( 'missing userEmail', __LINE__ );
}
if( strlen($_POST['user_email']) < 5 ){
    sendError( 'userEmail too short', __LINE__ );
}
if( !filter_var( $_POST['user_email'], FILTER_VALIDATE_EMAIL) ){
    sendError( 'invalid userEmail', __LINE__ );
}

//VALIDATE first name
if( empty( $_POST['user_firstName'] ) ){
    sendError( 'missing firstName', __LINE__ );
}

if( strlen($_POST['user_firstName']) < 1 ){
    sendError( 'firstName too short', __LINE__ );
}

if( strlen($_POST['user_firstName']) > 20 ){
    sendError( 'firstName too long', __LINE__ );
}

//VALIDATE last name
if( empty( $_POST['user_lastName'] ) ){
    sendError( 'missing lastName', __LINE__ );
}

if( strlen($_POST['user_lastName']) < 1 ){
    sendError( 'lastName too short', __LINE__ );
}

if( strlen($_POST['user_lastName']) > 20 ){
    sendError( 'lastName too long', __LINE__ );
}
 
//VALIDATE password
if( empty( $_POST['user_password'] ) ){
    sendError( 'missing userPassword', __LINE__ );
}
if( strlen($_POST['user_password']) < 5 ){
    sendError( 'userPassword too short', __LINE__ );
}
if( strlen($_POST['user_password']) > 50 ){
    sendError( 'userPassword too long', __LINE__ );
}

//VALIDATE username
if( empty( $_POST['user_username'] ) ){
    sendError( 'missing userPassword', __LINE__ );
}
if( strlen($_POST['user_username']) < 5 ){
    sendError( 'userPassword too short', __LINE__ );
}
if( strlen($_POST['user_username']) > 50 ){
    sendError( 'userPassword too long', __LINE__ );
}

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

require_once('../Users_class.php');
require_once('../Credit_cards_class.php');
require_once('../Payments_class.php');

$users = new Users();

/* Get a list of all users in DB */
$jUsers = $users->list();

// USER INFORMATION
$user_id = bin2hex(random_bytes(16));
$user_firstName = $_POST['user_firstName'];
$user_lastName = $_POST['user_lastName'];
$user_email = $_POST['user_email'];
$user_password = $_POST['user_password'];
$user_username = $_POST['user_username'];
$user_createdAt = date('Y-m-d H:i:s', time() );

// CARD INFORMATION
$card_id = bin2hex(random_bytes(16));
$card_expMonth = $_POST['card_expMonth'];
$card_expYear = $_POST['card_expYear'];

$card_IBAN = 'DK992000' . generateIBAN(10);
$card_number = $_POST['card_number'];
$card_expDate = $card_expMonth . '/' . $card_expYear;
$card_CVV = $_POST['card_CVV'];
$card_balance = rand(50000, 100000);

// FIRST PAYMENT INFO
$payment_id = bin2hex(random_bytes(16));
$monthly_subscription_amount = 20;
$isPrimaryCard = 1;

$total = count((array)$jUsers);

if($total === 0) {
    // call add method in users object
    $user_active = 1;
    $addUserResponse = $users->add($user_id, $user_firstName, $user_lastName, $user_email, $user_password, $user_username, $user_createdAt, $user_active, $monthly_subscription_amount);   
    if( $addUserResponse === true ){
        $cardCreatedResult = createCreditCard($card_id, $card_IBAN, $card_number, $card_expDate, $card_CVV, $card_balance);
        if ( $cardCreatedResult === true) {
            createCardUserConnection($card_id, $user_id, $monthly_subscription_amount, $isPrimaryCard);
            createFirstPayment($card_id, $user_id, $monthly_subscription_amount);
        } else {
            createCardUserConnection($cardCreatedResult, $user_id, $monthly_subscription_amount, $isPrimaryCard);
            createFirstPayment($cardCreatedResult, $user_id, $monthly_subscription_amount);
        }
        echo '{"status": 1, "message": "user registered successfully"}';
        exit;
    } else {
        sendError('cannot create first user', __LINE__ );
    }
}

foreach($jUsers as $sKey => $jUserRegular){
    // echo json_encode($jUserRegular);
    if($jUserRegular->email === $user_email || $jUserRegular->username === $user_username){
        if($jUserRegular->active == 0) {
            sendError('your account was deactivated some time ago', __LINE__ );
        }
        sendError('user already existent', __LINE__ );
    }

    if($total === 1){  
        // call add method in users object
        $user_active = 1;
        $addUserResponse = $users->add($user_id, $user_firstName, $user_lastName, $user_email, $user_password, $user_username, $user_createdAt, $user_active, $monthly_subscription_amount);   
        if( $addUserResponse === true ){
            $cardCreatedResult = createCreditCard($card_id, $card_IBAN, $card_number, $card_expDate, $card_CVV, $card_balance);
            if ( $cardCreatedResult === true) {
                createCardUserConnection($card_id, $user_id, $monthly_subscription_amount, $isPrimaryCard);
                createFirstPayment($card_id, $user_id, $monthly_subscription_amount);
            } else {
                createCardUserConnection($cardCreatedResult, $user_id, $monthly_subscription_amount, $isPrimaryCard);
                createFirstPayment($cardCreatedResult, $user_id, $monthly_subscription_amount);
            }
            echo '{"status": 1, "message": "user registered successfully"}';
            exit;
        } else {
            sendError('cannot create new user', __LINE__ );
        }
    }

    $total--;
}


//**************************************** */
function sendError($sMessage, $iLineNumber){
    echo '{"status":0, "message":"'.$sMessage.'", "line":'.$iLineNumber.'}';
    exit;  
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

function createCardUserConnection($id_card, $id_user, $amount, $isPrimaryCard) {
    $cards = new Cards();

    $connectionDoneResult = $cards->createCardUserConnection($id_card, $id_user, $amount, $isPrimaryCard);
    if( $connectionDoneResult !== true ){
        sendError('cannot create card user connection', __LINE__ );
    }
}

function createFirstPayment($id_card, $id_user, $amount) {
    $transaction = new Transaction();

    // CREATE TRANSFER
    $transactionResult = $transaction->transfer($id_card, $amount);

    if( $transactionResult !== true ){
        sendError('Insufficient amount to transfer - cannot create first payment', __LINE__ );
    } else {
        // ADD TO PAYMENTS
        $payment_id = bin2hex(random_bytes(16));
        $payment_createdAt = date('Y-m-d H:i:s', time() );
        $paymentAddedResult = $transaction->add($payment_id, $amount, $payment_createdAt, $id_card, $id_user);

        if( $paymentAddedResult !== true ){
            sendError('Error adding the first payment - cannot add first payment', __LINE__ );
        }
    }
}


 



