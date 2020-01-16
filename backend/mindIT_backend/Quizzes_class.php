<?php


require_once("connection.php");

class Quizzes
{
    function list($quizzes_amount)
    {

        $db = new DB();
        $con = $db->connect();
        if ($con) {
            $results = array();

            $stmt = $con->prepare("SELECT quizzes.*, users.* FROM quizzes" .
            " LEFT JOIN users ON users.user_id = quizzes.user_id" . 
            " order by quiz_createdAt desc limit :quizzes_amount ");
            $stmt->bindParam(':quizzes_amount', $quizzes_amount);
            $stmt->execute();

            while ($row = $stmt->fetch()){
                $quiz = new stdClass();
                $quiz->id = $row["quiz_id"];
                $quiz->name = $row["quiz_name"];
                $quiz->questionsAmount = $row["quiz_questionsAmount"];
                $quiz->difficulty = $row["quiz_difficulty"];
                $quiz->user_first_name = $row["user_first_name"];
                $quiz->user_last_name = $row["user_last_name"];
                $quiz->user_id = $row["user_id"];
                $results[] = $quiz;
            }
            $stmt = null;
            $db->disconnect($con);

            return $results;
        } else
            return false;
    }

    function count_quiz_questions($quiz_id)
    {

        $db = new DB();
        $con = $db->connect();
        if ($con) {
            $results = array();

            $stmt = $con->prepare("SELECT questions FROM questions" .
            " WHERE quiz_id = :quiz_id");
            $stmt->bindParam(':quiz_id', $quiz_id);
            $stmt->execute();

            while ($row = $stmt->fetch())
                $results[] = [$row];

            $stmt = null;
            $db->disconnect($con);

            return count((array)$results);
        } else
            return false;
    }

    function get_user_quizzes($user_id, $quizzes_amount)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {

            $results = array();
            $stmt = $con->prepare("SELECT users.*, quizzes.* FROM quizzes".
            " LEFT JOIN users ON users.user_id = quizzes.user_id" . 
            " WHERE quizzes.user_id = :user_id order by quiz_createdAt desc limit :quizzes_amount");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':quizzes_amount', $quizzes_amount);

            $stmt->execute();

            while ($row = $stmt->fetch()){
                $quiz = new stdClass();
                $quiz->id = $row["quiz_id"];
                $quiz->name = $row["quiz_name"];
                $quiz->createdAt = $row["quiz_createdAt"];
                $quiz->questionsAmount = $row["quiz_questionsAmount"];
                $quiz->difficulty = $row["quiz_difficulty"];
                $quiz->user_first_name = $row["user_first_name"];
                $quiz->user_last_name = $row["user_last_name"];
                $results[] = $quiz;
            }

            $stmt = null;
            $db->disconnect($con);

            return $results;
        } else { }
    }

    function add($quiz_id, $quiz_name, $quiz_createdAt, $questionsAmount, $user_id)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {
            try {
                $stmt = $con->prepare("INSERT INTO quizzes (quiz_id, quiz_name, quiz_createdAt, quiz_questionsAmount, user_id) 
                    VALUES (:quiz_id, :quiz_name, :quiz_createdAt, :questionsAmount, :user_id)");
                $stmt->bindParam(':quiz_id', $quiz_id);
                $stmt->bindParam(':quiz_name', $quiz_name);
                $stmt->bindParam(':quiz_createdAt', $quiz_createdAt);
                $stmt->bindParam(':questionsAmount', $questionsAmount);
                $stmt->bindParam(':user_id', $user_id);
                $ok = $stmt->execute();

                $stmt = null;
                $db->disconnect($con);

                return ($ok);
            } catch (PDOException $e) {
                echo $e;
            }
        } else {
            $stmt = null;
            $db->disconnect($con);
            return false;
        }
    }

    function check_user_has_completed_quiz_before($quiz_id, $user_id)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {
            $results = array();

            $stmt = $con->prepare("SELECT * FROM `completed-quizzes` WHERE quiz_id = :quiz_id AND user_id = :user_id");
                $stmt->bindParam(':quiz_id', $quiz_id);
                $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            while ($row = $stmt->fetch())
                $results[] = [$row];

            $stmt = null;
            $db->disconnect($con);

            if(count($results) !== 0) {
                return true;
            } else {
                return false;
            }
        } else
            return false;
    }

    function add_completed_quiz_to_user($quiz_id, $user_id)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {
            try {
                $stmt = $con->prepare("INSERT INTO `completed-quizzes` (quiz_id, user_id) 
                    VALUES (:quiz_id, :user_id)");
                $stmt->bindParam(':quiz_id', $quiz_id);
                $stmt->bindParam(':user_id', $user_id);
                $ok = $stmt->execute();

                $stmt = null;
                $db->disconnect($con);

                return ($ok);
            } catch (PDOException $e) {
            }
        } else {
            $stmt = null;
            $db->disconnect($con);
            return false;
        }
    }

    function updateDifficulty($difficulty, $quiz_id)
    {
        $db = new DB();
        $con = $db->connect();

        if ($con) {
            try {
                $stmt = $con->prepare('UPDATE quizzes SET quiz_difficulty = :difficulty WHERE quiz_id = :quiz_id');
                $stmt->bindParam(':quiz_id', $quiz_id);
                $stmt->bindParam(':difficulty', $difficulty);
                $ok = $stmt->execute();

                $stmt = null;
                $db->disconnect($con);
                
                return ($ok);

            } catch (PDOException $e) {
                echo $e;
            }

        } else {
            $stmt = null;
            $db->disconnect($con);
            return false;
        }
    }

    function edit_quizz($quiz_id, $quiz_name, $quiz_createdAt, $quiz_questionsAmount)
    {
        $db = new DB();
        $con = $db->connect();

        if ($con) {
            try {
                $stmt = $con->prepare('UPDATE quizzes SET quiz_name = :quiz_name, quiz_createdAt = :quiz_createdAt, quiz_questionsAmount = :quiz_questionsAmount WHERE quiz_id = :quiz_id');
                $stmt->bindParam(':quiz_id', $quiz_id);
                $stmt->bindParam(':quiz_name', $quiz_name);
                $stmt->bindParam(':quiz_createdAt', $quiz_createdAt);
                $stmt->bindParam(':quiz_questionsAmount', $quiz_questionsAmount);
                $ok = $stmt->execute();

                $stmt = null;
                $db->disconnect($con);
                
                return ($ok);

            } catch (PDOException $e) {
                echo $e;
            }

        } else {
            $stmt = null;
            $db->disconnect($con);
            return false;
        }
    }

    function delete($quiz_id, $user_id)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {

            $stmt = $con->prepare('DELETE FROM quizzes WHERE quiz_id = :quiz_id AND user_id = :user_id');
            $stmt->bindParam(':quiz_id', $quiz_id);
            $stmt->bindParam(':user_id', $user_id);
            $ok = $stmt->execute();

            $stmt = null;
            $db->disconnect($con);

            return ($ok);
        } else
            return false;
    }

    // GET ONE QUIZ
    function get_quiz($quiz_id)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {

            $stmt = $con->prepare("SELECT quizzes.*, users.* FROM quizzes" .
            " LEFT JOIN users ON users.user_id = quizzes.user_id" . 
            " WHERE quizzes.quiz_id = :quiz_id"
            );
            $stmt->bindParam(':quiz_id', $quiz_id);
            $stmt->execute();

            while ($row = $stmt->fetch()) {
                $newQuiz = new stdClass();
                $newQuiz->id = $row["quiz_id"];
                $newQuiz->name = $row["quiz_name"];
                $newQuiz->createdAt = $row["quiz_createdAt"];
                $newQuiz->difficulty = $row["quiz_difficulty"];
                $newQuiz->firstName = $row["user_first_name"];
                $newQuiz->lastName = $row["user_last_name"];
            }

            $stmt = null;
            $db->disconnect($con);

            return $newQuiz;
        } else { 
            return false; 
        }
    }

    function get_quiz_questions($quiz_id)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {
            $results = array();

            $stmt = $con->prepare("SELECT questions.*, quizzes.* FROM questions". 
            " LEFT JOIN quizzes ON quizzes.quiz_id = questions.quiz_id" . 
            " WHERE questions.quiz_id = :quiz_id order by questions.question_createdAt asc");
            $stmt->bindParam(':quiz_id', $quiz_id);
            $stmt->execute();

            $quiz = new stdClass();
            $quiz->questions = array();

            while ($row = $stmt->fetch()) {
                $quiz->quizName = $row["quiz_name"];
                $quiz->quizDifficulty = $row["quiz_difficulty"];
                
                $question = new stdClass();
                $question->questionID = $row["question_id"];
                $question->questionContent = $row["question_content"];
                $question->questionAnswer = $row["question_answer"];
                $question->questionDifficulty = $row["question_difficulty"];

                array_push($quiz->questions, $question);

            }
            $results = $quiz;

            $stmt = null;
            $db->disconnect($con);

            return $results;
        } else
            return false;
    }
}
