<?php

require_once("connection.php");

class Cards
{
    function list()
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {
            $results = array();

            $stmt = $con->prepare("SELECT * FROM `credit-cards` order by card_id");
            $stmt->bindParam(':question_amount', $question_amount);
            $stmt->execute();

            while ($row = $stmt->fetch()){
                $newCard = new stdClass();
                $newCard->id = $row["card_id"];
                $newCard->IBAN_code = $row["card_IBAN_code"];
                $newCard->number = $row["card_number"];
                $newCard->expiration_date = $row["card_expiration_date"];
                $newCard->CVV = $row["card_CVV"];
                $results[] = $newCard;
            }
            $stmt = null;
            $db->disconnect($con);

            return $results;
        } else
            return false;
    }

    // GET USER'S CARDS
    function get_user_cards($user_id)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {
            $results = array();

            $stmt = $con->prepare("SELECT `users-cards`.*, `credit-cards`.* FROM `users-cards`" .
            " LEFT JOIN `credit-cards` ON `credit-cards`.card_id = `users-cards`.card_id" .
            " WHERE `users-cards`.user_id = :user_id order by card_isPrimary DESC");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            while ($row = $stmt->fetch()){
                $newCard = new stdClass();
                $newCard->id = $row["card_id"];
                $newCard->number = $row["card_number"];
                $newCard->expDate = $row["card_expiration_date"];
                $newCard->CVV = $row["card_CVV"];
                $newCard->isPrimary = $row["card_isPrimary"];
                $newCard->user = $row["user_id"];
                $results[] = $newCard;
            }
            $stmt = null;
            $db->disconnect($con);

            return $results;
        } else
            return false;
    }

    function get_card($card_id, $user_id)
    {

        $db = new DB();
        $con = $db->connect();
        if ($con) {

            $results = array();
            $stmt = $con->prepare("SELECT `users-cards`.*, `credit-cards`.* FROM `users-cards`" .
                " LEFT JOIN `credit-cards` ON `credit-cards`.card_id = `users-cards`.card_id" .
                " WHERE `users-cards`.card_id = :card_id AND `users-cards`.user_id = :user_id");
            $stmt->bindParam(':card_id', $card_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            while ($row = $stmt->fetch()) {
                $results[] = [$row["card_id"], $row["card_number"], $row["card_expiration_date"], $row["card_CVV"], $row["card_isPrimary"]];
            }

            $stmt = null;
            $db->disconnect($con);
            return $results;
        } else { return false; }
    }

    function createCardUserConnection($id_card, $id_user, $amountCharged, $isPrimaryCard)
    {
        $db = new DB();
        $con = $db->connect();

        if ($con) {

            try {
                $stmt = $con->prepare("INSERT INTO `users-cards` (card_id, user_id, card_total_amount_paid, card_isPrimary)
                        VALUES (:id_card, :id_user, :amountCharged, :isPrimaryCard)");
                $stmt->bindParam(':id_card', $id_card);
                $stmt->bindParam(':id_user', $id_user);
                $stmt->bindParam(':amountCharged', $amountCharged);
                $stmt->bindParam(':isPrimaryCard', $isPrimaryCard);
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

    function add($card_id, $card_IBAN, $card_number, $card_expDate, $card_CVV, $card_balance)
    {
        $db = new DB();
        $con = $db->connect();

        if ($con) {
            try {
                $stmt = $con->prepare("INSERT INTO `credit-cards` (card_id, card_IBAN_code, card_number, card_expiration_date, card_CVV, card_balance)
                    VALUES (:card_id, :card_IBAN, :card_number, :card_expDate, :card_CVV, :card_balance)");
                $stmt->bindParam(':card_id', $card_id);
                $stmt->bindParam(':card_IBAN', $card_IBAN);
                $stmt->bindParam(':card_number', $card_number);
                $stmt->bindParam(':card_expDate', $card_expDate);
                $stmt->bindParam(':card_CVV', $card_CVV);
                $stmt->bindParam(':card_balance', $card_balance);
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

    function update($card_id, $card_expiration_date, $card_CVV)
    {
        $db = new DB();
        $con = $db->connect();

        if ($con) {
            try {
                $stmt = $con->prepare('UPDATE `credit-cards` SET card_expiration_date = :card_expiration_date, card_CVV = :card_CVV WHERE card_id = :card_id');

                $stmt->bindParam(':card_id', $card_id);
                $stmt->bindParam(':card_expiration_date', $card_expiration_date);
                $stmt->bindParam(':card_CVV', $card_CVV);
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

    function delete($card_id)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {

            $stmt = $con->prepare('DELETE `credit-cards` FROM `credit-cards` WHERE card_id = :card_id');
            $stmt->bindParam(':card_id', $card_id);
            $ok = $stmt->execute();

            $stmt = null;
            $db->disconnect($con);

            return ($ok);
        } else
            return false;
    }


    function set_user_cards_noPrimary($user_id)
    {
        $db = new DB();
        $con = $db->connect();

        if ($con) {
            try {
                $stmt = $con->prepare('UPDATE `users-cards` SET card_isPrimary = 0 WHERE user_id = :user_id');

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

    function set_primary_card($card_id, $user_id)
    {
        $db = new DB();
        $con = $db->connect();

        if ($con) {
            try {
                $stmt = $con->prepare('UPDATE `users-cards` SET card_isPrimary = 1 WHERE card_id = :card_id AND user_id = :user_id');

                $stmt->bindParam(':card_id', $card_id);
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

    function get_primary_card($user_id)
    {
        $db = new DB();
        $con = $db->connect();

        if ($con) {

            $result = new stdClass();
            $stmt = $con->prepare("SELECT `users-cards`.*, `credit-cards`.* FROM `users-cards`" . 
            " LEFT JOIN `credit-cards` ON `credit-cards`.card_id = `users-cards`.card_id" .
            " WHERE `users-cards`.card_isPrimary = 1 AND `users-cards`.user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            while ($row = $stmt->fetch()) {
                $result->card_id = $row["card_id"];
                $result->card_balance = $row["card_balance"];
            }

            $stmt = null;
            $db->disconnect($con);
            return $result;
        } else { return false; }
    }
}
