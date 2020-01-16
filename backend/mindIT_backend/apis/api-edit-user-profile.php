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

        //VALIDATE first name
        if( empty( $_POST['user_firstName'] ) ){
            sendErrorMessage( 'missing firstName', __LINE__ );
        }

        if( strlen($_POST['user_firstName']) < 1 ){
            sendErrorMessage( 'firstName too short', __LINE__ );
        }

        if( strlen($_POST['user_firstName']) > 20 ){
            sendErrorMessage( 'firstName too long', __LINE__ );
        }

        //VALIDATE last name
        if( empty( $_POST['user_lastName'] ) ){
            sendErrorMessage( 'missing lastName', __LINE__ );
        }

        if( strlen($_POST['user_lastName']) < 1 ){
            sendErrorMessage( 'lastName too short', __LINE__ );
        }

        if( strlen($_POST['user_lastName']) > 20 ){
            sendErrorMessage( 'lastName too long', __LINE__ );
        }

        //VALIDATE address
        if( isset($_POST['user_address']) ){
            if( strlen($_POST['user_address']) < 5 ){
                sendErrorMessage( 'address too short', __LINE__ );
            }
    
            if( strlen($_POST['user_address']) > 40 ){
                sendErrorMessage( 'address too long', __LINE__ );
            }
        }


        //VALIDATE phone number
        if( isset($_POST['user_phoneNumber']) ){
            if( strlen($_POST['user_phoneNumber']) < 8 ){
                sendErrorMessage( 'phoneNumber too short', __LINE__ );
            }
    
            if( strlen($_POST['user_phoneNumber']) > 12 ){
                sendErrorMessage( 'phoneNumber too long', __LINE__ );
            }
        }


        //VALIDATE postalCode
        if( isset($_POST['user_postalCode'])  ){
            if( strlen($_POST['user_postalCode']) < 4 ){
                sendErrorMessage( 'postalCode too short', __LINE__ );
            }
            if( strlen($_POST['user_postalCode']) > 4 ){
                sendErrorMessage( 'postalCode too long', __LINE__ );
            }
            if( !ctype_digit($_POST['user_postalCode']) ){
                sendErrorMessage( 'postalCode needs to be numberic', __LINE__ );
            }
        }

        //VALIDATE city
        if( isset($_POST['user_city'])  ){
            if( strlen($_POST['user_city']) < 2 ){
                sendErrorMessage( 'usercity too short', __LINE__ );
            }
            if( strlen($_POST['user_city']) > 50 ){
                sendErrorMessage( 'usercity too long', __LINE__ );
            }
        }
        
        //VALIDATE password
        if( empty( $_POST['user_password'] ) ){
            sendErrorMessage( 'missing userPassword', __LINE__ );
        }
        if( strlen($_POST['user_password']) < 5 ){
            sendErrorMessage( 'userPassword too short', __LINE__ );
        }
        if( strlen($_POST['user_password']) > 50 ){
            sendErrorMessage( 'userPassword too long', __LINE__ );
        }

        //VALIDATE username
        if( empty( $_POST['user_username'] ) ){
            sendErrorMessage( 'missing userPassword', __LINE__ );
        }
        if( strlen($_POST['user_username']) < 5 ){
            sendErrorMessage( 'userPassword too short', __LINE__ );
        }
        if( strlen($_POST['user_username']) > 50 ){
            sendErrorMessage( 'userPassword too long', __LINE__ );
        }

        require_once('../Users_class.php');
        require_once('../Credit_cards_class.php');

        $users = new Users();

        // USER INFORMATION
        $user_id = $payload->userID;
        $user_firstName = $_POST['user_firstName'];
        $user_lastName = $_POST['user_lastName'];
        $user_username = $_POST['user_username'];
        $user_password = $_POST['user_password'];
        $user_address = (isset($_POST['user_address'])) === true ? $_POST['user_address'] : '';
        $user_phoneNumber = (isset($_POST['user_phoneNumber'])) === true ? $_POST['user_phoneNumber'] : '';
        $user_postalCode = (isset($_POST['user_postalCode'])) === true ? $_POST['user_postalCode'] : '';
        $user_city = (isset($_POST['user_city'])) === true ? $_POST['user_city'] : '';

        /* Get a list of all users in DB */
        $updateResult = $users->update($user_id, $user_firstName, $user_lastName, $user_username, $user_password, $user_address, $user_phoneNumber, $user_postalCode, $user_city);

        if($updateResult !== true) {
            sendErrorMessage( 'update went wrong', __LINE__ ); 
        } else {
            echo '{"status": 1, "message": "user updated successfully"}';
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



