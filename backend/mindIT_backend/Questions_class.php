<?php

require_once("connection.php");

class Questions
{
    // Retrieves questions information
    function list($question_amount)
    {

        $db = new DB();
        $con = $db->connect();
        if ($con) {
            $results = array();

            $stmt = $con->prepare("SELECT * FROM questions order by question_createdAt desc limit :question_amount");
            $stmt->bindParam(':question_amount', $question_amount);
            $stmt->execute();

            while ($row = $stmt->fetch())
                $results[] = [$row["question_id"], $row["question_content"], $row["question_answer"], $row["question_difficulty"], $row["question_createdAt"], $row["fk_user_id"]];

            $stmt = null;
            $db->disconnect($con);

            return $results;
        } else
            return false;
    }

    function get_question_answer($question_id)
    {

        $db = new DB();
        $con = $db->connect();
        if ($con) {
            $results = new stdClass();

            $stmt = $con->prepare("SELECT question_id, questions.question_answer, questions.question_content FROM questions WHERE question_id = :question_id");
            $stmt->bindParam(':question_id', $question_id);
            $stmt->execute();

            while ($row = $stmt->fetch()){
                $results->id = $row['question_id'];
                $results->answer = $row['question_answer'];
                $results->content = $row['question_content'];
            }

            $stmt = null;
            $db->disconnect($con);

            return $results;
        } else
            return false;
    }

    function add($question_id, $question_content, $question_answer, $question_difficulty, $question_createdAt, $quiz_id)
    {
        $db = new DB();
        $con = $db->connect();

        if ($con) {
            try {
                $stmt = $con->prepare("INSERT INTO questions (question_id, question_content, question_answer, question_difficulty, question_createdAt, quiz_id)
                    VALUES (:question_id, :question_content, :question_answer, :question_difficulty, :question_createdAt, :quiz_id)");
                $stmt->bindParam(':question_id', $question_id);
                $stmt->bindParam(':question_content', $question_content);
                $stmt->bindParam(':question_answer', $question_answer);
                $stmt->bindParam(':question_difficulty', $question_difficulty);
                $stmt->bindParam(':question_createdAt', $question_createdAt);
                $stmt->bindParam(':quiz_id', $quiz_id);
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

    function update($question_id, $question_content, $question_answer, $question_difficulty, $quiz_id)
    {
        $db = new DB();
        $con = $db->connect();

        if ($con) {
            try {
                $stmt = $con->prepare('UPDATE questions SET question_content = :question_content, question_answer = :question_answer, question_difficulty = :question_difficulty WHERE quiz_id = :quiz_id AND question_id = :question_id');
                $stmt->bindParam(':question_id', $question_id);
                $stmt->bindParam(':question_content', $question_content);
                $stmt->bindParam(':question_answer', $question_answer);
                $stmt->bindParam(':question_difficulty', $question_difficulty);
                $stmt->bindParam(':quiz_id', $quiz_id);
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

    function remove_deleted_questions($id)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {

            $stmt = $con->prepare('DELETE questions FROM questions WHERE question_id = :id');
            $stmt->bindParam(':id', $id);
            $ok = $stmt->execute();

            $stmt = null;
            $db->disconnect($con);

            return ($ok);
        } else
            return false;
    }

}
