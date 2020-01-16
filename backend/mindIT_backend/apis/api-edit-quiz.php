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
        $aQuiz_removedQuestions = json_decode($_POST['removedQuestions']);

        $quiz_questionsAmount = count((array)$aQuiz_questions);

        $quiz_id = $_POST['id'];
        $quiz_name = $_POST['name'];
        $quiz_createdAt = date('Y-m-d H:i:s', time());

        $editQuizResult = $quizzes->edit_quizz($quiz_id, $quiz_name, $quiz_createdAt, $quiz_questionsAmount);
        if($editQuizResult !== true) {
            sendErrorMessage( 'smth went wrong editing the quiz', __LINE__ ); 
        }

        if(count((array)$aQuiz_removedQuestions) > 0){
            foreach($aQuiz_removedQuestions as $sDeletedQuestionID){
                $removeDeletedQuestionsResult = $questions->remove_deleted_questions($sDeletedQuestionID);
                if($removeDeletedQuestionsResult !== true) {
                    sendErrorMessage( 'smth went wrong deleting the deleted questions', __LINE__ ); 
                }
            }
        }

        $quizDifficulty = 0;

        foreach($aQuiz_questions as $jQuestion){
            $question_content = $jQuestion->questionContent;
            $question_answer = $jQuestion->questionAnswer;
            $question_difficulty = $jQuestion->questionDifficulty;
            $question_createdAt = date('Y-m-d H:i:s', time());
            
            if (isset($jQuestion->questionID)) {
                $question_id = $jQuestion->questionID;
                // call edit method in questions object
                $resultEditQuestion = $questions->update($question_id, $question_content, $question_answer, $question_difficulty, $quiz_id);
    
                if($resultEditQuestion !== true){
                    sendErrorMessage( 'smth went wrong editing questions', __LINE__ ); 
                } 
            } else {
                $question_id = bin2hex(random_bytes(16));
                $resultAddQuestion = $questions->add($question_id, $question_content, $question_answer, $question_difficulty, $question_createdAt, $quiz_id);

                if($resultAddQuestion !== true){
                    sendErrorMessage( 'smth went wrong', __LINE__ ); 
                } 
            }

            $quizDifficulty = (int)$quizDifficulty + (int)$question_difficulty;
        }

        $scoreToEstablishLevel = $quizDifficulty/count($aQuiz_questions);
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
            sendErrorMessage( 'smth went wrong updating quiz difficulty', __LINE__ ); 
        }

        echo '{"status": 1, "message": "quiz edited successfully"}';
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




