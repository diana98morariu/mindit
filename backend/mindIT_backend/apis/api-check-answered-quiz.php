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

        /* New object of questions() */
        require_once('../Questions_class.php');
        require_once('../Quizzes_class.php');
        
        $aQuiz_questions = json_decode($_GET['questions']);
        $quiz_id = $_GET['id'];

        // USER INFORMATION
        $user_id = $payload->userID;

        $quizzes = new Quizzes();

        $resultUserCompletedBefore = $quizzes->check_user_has_completed_quiz_before($quiz_id, $user_id);
        if($resultUserCompletedBefore === true) {
            sendErrorMessage( 'user completed quiz before', __LINE__ ); 
        } 

        $questions = new Questions();

        $quiz_questionsAmount = count((array)$aQuiz_questions);

        $quizResults = new stdClass();
        $quizResults->correctAnswers = array();
        $quizResults->wrongAnswers = array();

        foreach($aQuiz_questions as $jQuestion){
            $question_id = $jQuestion->questionID;
            $question_user_answer = $jQuestion->questionUserAnswer;

            $rawCorrectAnswer = $questions->get_question_answer($question_id);
            $parsedCorrectAnswer = trim(strtolower($rawCorrectAnswer->answer));
            $parsedUserAnswer = trim(strtolower($question_user_answer));

            if($parsedCorrectAnswer === $parsedUserAnswer) {
                $correctQuestion = new stdClass();
                $correctQuestion->id = $rawCorrectAnswer->id;
                $correctQuestion->content = $rawCorrectAnswer->content;
                $correctQuestion->correctAnswer = $parsedUserAnswer;
                array_push($quizResults->correctAnswers, $correctQuestion);
            } else {
                $wrongQuestion = new stdClass();
                $wrongQuestion->id = $rawCorrectAnswer->id;
                $wrongQuestion->content = $rawCorrectAnswer->content;
                $wrongQuestion->userAnswer = $parsedUserAnswer;
                $wrongQuestion->correctAnswer = $parsedCorrectAnswer;
                array_push($quizResults->wrongAnswers, $wrongQuestion);
            }
        }

        $completedQuizResult = $quizzes->add_completed_quiz_to_user($quiz_id, $user_id);

        if($completedQuizResult !== true) {
            sendErrorMessage( 'add user to completed quiz failed', __LINE__ ); 
        } else {
            echo json_encode($quizResults);
        }
      
        
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




