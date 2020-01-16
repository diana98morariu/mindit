<?php

session_start();
if(!$_SESSION){
    sendErrorMessage( 'not autheticated', __LINE__ ); 
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

require_once('../Credit_cards_class.php');

$creditCard = new Cards();

// USER INFORMATION
$card_id = $_GET['cardID'];
$user_id = $_SESSION['userID'];

$retrievedCreditCard = $creditCard->get_card($card_id, $user_id);

if($retrievedCreditCard === false) {
    sendErrorMessage( 'get card went wrong', __LINE__ ); 
} else {
    echo json_encode($retrievedCreditCard);
}

// ******************************* FUNCTIONS ************************************

function sendErrorMessage($sErrorMessage, $iLineNumber){
    echo '{"status": 0, "message": "'.$sErrorMessage.'", "line": "'.$iLineNumber.'"}';
    exit;
}