<?php

require_once("connection.php");

class Users
{
    function list()
    {

        $db = new DB();
        $con = $db->connect();
        if ($con) {
            $users = array();

            $stmt = $con->prepare("SELECT * FROM users ORDER BY user_email");
            $stmt->execute();

            while ($row = $stmt->fetch()) {
                $newUser = new stdClass();
                $newUser->id = $row["user_id"];
                $newUser->email = $row["user_email"];
                $newUser->firstName = $row["user_first_name"];
                $newUser->lastName = $row["user_last_name"];
                $newUser->username = $row["user_username"];
                $newUser->password = $row["user_password"];
                $newUser->active = $row["user_active"];
                $users[] = $newUser;
            }

            $stmt = null;
            $db->disconnect($con);

            return $users;
        } else
            return false;
    }

    function list_active_users()
    {

        $db = new DB();
        $con = $db->connect();
        if ($con) {
            $users = array();

            $stmt = $con->prepare("SELECT * FROM users WHERE user_active = 1 ORDER BY user_active DESC");
            $stmt->execute();

            while ($row = $stmt->fetch()) {
                $newUser = new stdClass();
                $newUser->id = $row["user_id"];
                $newUser->email = $row["user_email"];
                $newUser->lastName = $row["user_last_name"];
                $newUser->username = $row["user_username"];
                $newUser->password = $row["user_password"];
                $newUser->active = $row["user_active"];
                $users[] = $newUser;
            }

            $stmt = null;
            $db->disconnect($con);

            return $users;
        } else
            return false;
    }

    /**
     * Inserts a new course
     * 
     * @param title, start_date, ETCS, teacher
     * @return true if the insertion was correct, false if there was an error
     */
    function add($user_id, $user_firstName, $user_lastName, $user_email, $user_password, $user_username, $user_createdAt, $user_active, $monthly_subscription_amount)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {
            try {
                $stmt = $con->prepare("INSERT INTO users (user_id, user_first_name, user_last_name, user_email, user_password, user_username, user_createdAt, user_active, user_total_paid) 
                    VALUES (:user_id, :user_firstName, :user_lastName, :user_email, :user_password, :user_username, :user_createdAt, :user_active, :monthly_subscription_amount)");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':user_firstName', $user_firstName);
                $stmt->bindParam(':user_lastName', $user_lastName);
                $stmt->bindParam(':user_email', $user_email);
                $stmt->bindParam(':user_password', $user_password);
                $stmt->bindParam(':user_username', $user_username);
                $stmt->bindParam(':user_createdAt', $user_createdAt);
                $stmt->bindParam(':user_active', $user_active);
                $stmt->bindParam(':monthly_subscription_amount', $monthly_subscription_amount);
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

    function update($user_id, $user_first_name, $user_last_name, $user_username, $user_password, $user_address, $user_phoneNumber, $user_postalCode, $user_city)
    {
        $db = new DB();
        $con = $db->connect();

        if ($con) {
            try {
                $stmt = $con->prepare('UPDATE users SET user_first_name = :user_first_name, '. 
                'user_last_name = :user_last_name, user_username = :user_username, user_password = :user_password, '. 
                'user_address = :user_address, user_phone = :user_phoneNumber, user_postalCode = :user_postalCode, '.
                'user_city = :user_city WHERE user_id = :user_id');

                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':user_first_name', $user_first_name);
                $stmt->bindParam(':user_last_name', $user_last_name);
                $stmt->bindParam(':user_username', $user_username);
                $stmt->bindParam(':user_password', $user_password);
                $stmt->bindParam(':user_address', $user_address);
                $stmt->bindParam(':user_phoneNumber', $user_phoneNumber);
                $stmt->bindParam(':user_postalCode', $user_postalCode);
                $stmt->bindParam(':user_city', $user_city);
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

    function delete($user_id)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {

            $stmt = $con->prepare('UPDATE users SET user_active = 0 WHERE users.user_id = :user_id');
            $stmt->bindParam(':user_id', $user_id);
            $ok = $stmt->execute();

            $stmt = null;
            $db->disconnect($con);

            return ($ok);
        } else
            return false;
    }

    function get_user($user_id)
    {
        $db = new DB();
        $con = $db->connect();
        if ($con) {

            $stmt = $con->prepare("SELECT users.* FROM users" . 
            " WHERE users.user_id = :user_id"
            );
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            while ($row = $stmt->fetch()) {
                $newUser = new stdClass();
                $newUser->id = $row["user_id"];
                $newUser->lastName = $row["user_last_name"];
                $newUser->firstName = $row["user_first_name"];
                $newUser->username = $row["user_username"];
                $newUser->password = $row["user_password"];
                $newUser->address = $row["user_address"];
                $newUser->postalCode = $row["user_postalCode"];
                $newUser->city = $row["user_city"];
                $newUser->phone = $row["user_phone"];
                $newUser->active = $row["user_active"];
            }

            $stmt = null;
            $db->disconnect($con);

            return $newUser;
        } else { 
            return false; 
        }
    }
}
