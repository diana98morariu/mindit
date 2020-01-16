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
      
        require_once('../Quizzes_class.php');

        $quizzes = new Quizzes();

        // USER INFORMATION
        $user_id = $payload->userID;

        // GET QUIZ ID
        $quiz_id = $_GET['quizID'];

        $quizDeleteResult = $quizzes->delete($quiz_id, $user_id);

        if($quizDeleteResult !== true) {
            sendError( 'delete went wrong', __LINE__ ); 
        } else {
            echo '{"status": 1, "message": "quiz deleted successfully"}';
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



