<?php

require_once("connection.php");

class Transaction {
 
    const DB_HOST = 'gp96xszpzlqupw4k.cbetxkdyhwsb.us-east-1.rds.amazonaws.com';
    const DB_NAME = 'byzcrgjswtp2l3ir';
    const DB_USER = 'lqici344up05jq4p';
    const DB_PASSWORD = 'afqrn9uiqvycj9zq';
 

    public function __construct() {
        // open database connection
        $conStr = sprintf("mysql:host=%s;dbname=%s", self::DB_HOST, self::DB_NAME);
        try {
            $this->pdo = new PDO($conStr, self::DB_USER, self::DB_PASSWORD);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
 
    private $pdo = null;
 
    public function transfer($from, $amount) {
 
        try {
            $this->pdo->beginTransaction();
 
            // get available amount of the transferer account
            $sql = 'SELECT card_balance FROM `credit-cards` WHERE card_id=:from';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(":from" => $from));
            $availableAmount = (int) $stmt->fetchColumn();
            $stmt->closeCursor();
 
            if ($availableAmount < $amount) {
                return false;
            }
            // deduct from the transferred account
            $sql_update_from = 'UPDATE `credit-cards`
                SET card_balance = card_balance - :amount
                WHERE card_id = :from';
            $stmt = $this->pdo->prepare($sql_update_from);
            $stmt->execute(array(":from" => $from, ":amount" => $amount));
            $stmt->closeCursor();
 
            // add to the receiving account
            $sql_update_to = 'UPDATE `credit-cards`
                                SET card_balance = card_balance + :amount
                                WHERE card_id = 1';
            $stmt = $this->pdo->prepare($sql_update_to);
            $stmt->execute(array(":amount" => $amount));
 
            // commit the transaction
            $this->pdo->commit();
  
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }

    public function add($payment_id, $payment_amount, $payment_createdAt, $card_id, $user_id)
    {
        $db = new DB();
        $con = $db->connect();

        if ($con) {
            try {
                $stmt = $con->prepare("INSERT INTO `payments` (payment_id, payment_amount, payment_createdAt, card_id, user_id)
                    VALUES (:payment_id, :payment_amount, :payment_createdAt, :card_id, :user_id)");
                $stmt->bindParam(':payment_id', $payment_id);
                $stmt->bindParam(':payment_amount', $payment_amount);
                $stmt->bindParam(':payment_createdAt', $payment_createdAt);
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
 

    public function __destruct() {
        // close the database connection
        $this->pdo = null;
    }

    /** FUNCTION CONNECTING TO STORED PROCEDURE (total_amount_payed) */
    public function execute_payment($user_id, $card_id)
    {
        $db = new DB();
        $con = $db->connect();
    
        if ($con) {
            try {
                $sql = "CALL byzcrgjswtp2l3ir.new_payment_procedure(:user_id, :card_id)";
                $stmt = $con->prepare($sql);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':card_id', $card_id);
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
 
}



