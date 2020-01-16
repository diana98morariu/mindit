<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

session_start();
if(!$_SESSION){
    sendErrorMessage( 'not autheticated', __LINE__ ); 
}

require_once('../Payments_class.php');

//VALIDATE card id
if( empty( $_GET['id'] ) ){
    sendErrorMessage( 'missing card id', __LINE__ );
}
if( strlen($_GET['id']) < 5 ){
    sendErrorMessage( 'card id too short', __LINE__ );
}
if( strlen($_GET['id']) > 55 ){
    sendErrorMessage( 'card id too long', __LINE__ );
}

$card_id = $_GET['id'];

$transaction = new Transaction();

// transfer 1K from from account 1 to bank
$transactionResult = $transaction->transfer($card_id, 20);

if($transactionResult !== true) {
    sendErrorMessage( 'transaction went wrong', __LINE__ ); 
} else {
    echo '{"status": 1, "message": "transaction was successfully"}';
}

// ******************************* FUNCTIONS ************************************

function sendErrorMessage($sErrorMessage, $iLineNumber){
    echo '{"status": 0, "message": "'.$sErrorMessage.'", "line": "'.$iLineNumber.'"}';
    exit;
}
