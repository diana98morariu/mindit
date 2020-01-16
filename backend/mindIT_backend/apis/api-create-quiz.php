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
      
        /* New object of questions() */
        require_once('../Questions_class.php');
        require_once('../Quizzes_class.php');

        $questions = new Questions();
        $quizzes = new Quizzes();

        $aQuiz_questions = json_decode($_POST['questions']);

        $questionsAmount = count((array)$aQuiz_questions);

        // USER INFORMATION
        $sUserID = $payload->userID;

        $quiz_id = bin2hex(random_bytes(16));
        $quiz_name = $_POST['name'];
        $quiz_createdAt = date('Y-m-d H:i:s', time());

        $addQuizResult = $quizzes->add($quiz_id, $quiz_name, $quiz_createdAt, $questionsAmount, $sUserID);
        if($addQuizResult !== true) {
            sendErrorMessage( 'smth went wrong', __LINE__ ); 
        }

        $averageDiff = 0;

        foreach($aQuiz_questions as $jQuestion){
            $question_id = bin2hex(random_bytes(16));
            $question_content = $jQuestion->questionContentValue;
            $question_answer = $jQuestion->questionAnswerValue;
            $question_difficulty = $jQuestion->questionDifficultyValue;
            $question_createdAt = date('Y-m-d H:i:s', time());

            // call add method in questions object
            $resultAddQuestion = $questions->add($question_id, $question_content, $question_answer, $question_difficulty, $question_createdAt, $quiz_id);

            if($resultAddQuestion !== true){
                sendErrorMessage( 'smth went wrong', __LINE__ ); 
            } 
            $averageDiff = (int)$averageDiff + (int)$jQuestion->questionDifficultyValue;
        }

        $scoreToEstablishLevel = $averageDiff/count($aQuiz_questions);
        $difficultyGotten = null;

        if($scoreToEstablishLevel < 1.4) {
            $difficultyGotten = 'Very Easy';
        } else if($scoreToEstablishLevel >= 1.4 && $scoreToEstablishLevel < 1.8) {
            $difficultyGotten = 'Easy';
        } else if($scoreToEstablishLevel >= 1.8 && $scoreToEstablishLevel < 2.2) {
            $difficultyGotten = 'Medium';
        } else if($scoreToEstablishLevel >= 2.2 && $scoreToEstablishLevel < 2.6) {
            $difficultyGotten = 'Hard';
        } else if($scoreToEstablishLevel >= 2.6 ) {
            $difficultyGotten = 'Very Hard';
        }

        $updateQuizDiffResult = $quizzes->updateDifficulty($difficultyGotten, $quiz_id);
        if($updateQuizDiffResult !== true) {
            sendErrorMessage( 'smth went wrong', __LINE__ ); 
        }

        echo '{"status": 1, "message": "quiz registered successfully"}';        
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




