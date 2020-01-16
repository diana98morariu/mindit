<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

if(!isset($_GET['search'])){
    echo '[]';
    exit;
}

$token = null;
        
if (isset($_GET['token'])) {$token = $_GET['token'];}

if (!is_null($token)) {

    require_once('jwt.php');

    // Get our server-side secret key from a secure location.
    $serverKey = '5f2b5cdbe5194f10b3241568fe4e2b24';

    try {
        $payload = JWT::decode($token, $serverKey, array('HS256'));
      
        // USER INFORMATION
        $user_id = $payload->userID;

        $sSearchFor = $_GET['search'];
        $sSearchFor = strtolower($sSearchFor);

        $quizzes_amount = $_GET['limit'];
        $quizzes_filter = $_GET['filter'];

        /* New object of Quizzes() */
        require_once('../Quizzes_class.php');
        $quizzes = new Quizzes();

        if (isset($_GET['myQuizzes'])) {
            $allQuizzes = $quizzes->get_user_quizzes($user_id, $quizzes_amount);

            foreach($allQuizzes as $oneQuiz) {
                if(str_replace(' ', '', $oneQuiz->difficulty) !== str_replace(' ', '', $quizzes_filter) && $quizzes_filter !== 'unset') {
                    $quizIndex = array_search($oneQuiz, $allQuizzes);
                    array_splice($allQuizzes, $quizIndex, 1); 
                }
            }
            
        } else {
            $allQuizzes = $quizzes->list($quizzes_amount);
    
            foreach($allQuizzes as $oneQuiz) {
                $userCompletedQuizzes = $quizzes->check_user_has_completed_quiz_before($oneQuiz->id, $user_id);
                if($userCompletedQuizzes === true) {
                    $quizIndex = array_search($oneQuiz, $allQuizzes);
                    array_splice($allQuizzes, $quizIndex, 1); 
                } else if ($oneQuiz->user_id === $user_id) {
                    $quizIndex = array_search($oneQuiz, $allQuizzes);
                    array_splice($allQuizzes, $quizIndex, 1); 
                } else if(str_replace(' ', '', $oneQuiz->difficulty) !== str_replace(' ', '', $quizzes_filter) && $quizzes_filter !== 'unset') {
                    $quizIndex = array_search($oneQuiz, $allQuizzes);
                    array_splice($allQuizzes, $quizIndex, 1); 
                }
            }
        }

        $aQuizesFiltered = [];

        foreach($allQuizzes as $jQuiz){
            array_push($aQuizesFiltered, $jQuiz);
        }

        $matches = [];

        if(strlen($sSearchFor) === 0) {
            $matches = $aQuizesFiltered;
        } else {
            foreach($aQuizesFiltered as $quiz){
                if(strpos(strtolower($quiz->name), $sSearchFor) !== false){
                    array_push($matches, $quiz);
                }
            }
        }

        echo json_encode($matches);
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



