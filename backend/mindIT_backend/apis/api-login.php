<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

if (!isset($_POST['user_emailOrUsername'])) {sendError( 'missing emailOrUsername', __LINE__ );}
if (!isset($_POST['user_password'])) {sendError( 'missing userPassword', __LINE__ );}

//VALIDATE usernameOrEmail
if( empty( $_POST['user_emailOrUsername'] ) ){
    sendError( 'missing emailOrUsername', __LINE__ );
}
if( strlen($_POST['user_emailOrUsername']) < 5 ){
    sendError( 'emailOrUsername too short', __LINE__ );
}
if( strlen($_POST['user_emailOrUsername']) > 50 ){
    sendError( 'emailOrUsername too long', __LINE__ );
}

//VALIDATE password
if( empty( $_POST['user_password'] ) ){
    sendError( 'missing userPassword', __LINE__ );
}
if( strlen($_POST['user_password']) < 5 ){
    sendError( 'userPassword too short', __LINE__ );
}
if( strlen($_POST['user_password']) > 50 ){
    sendError( 'userPassword too long', __LINE__ );
}

require_once('../Users_class.php');

$users = new Users();

/* Get a list of all users in DB */
$jUsers = $users->list();

$user_emailOrUsername = $_POST['user_emailOrUsername'];
$user_password = $_POST['user_password'];

$total = count((array)$jUsers);

foreach($jUsers as $index => $joneUser){
    if((strtolower($joneUser->email) === strtolower($user_emailOrUsername) || strtolower($joneUser->username) === strtolower($user_emailOrUsername)) && $joneUser->password === $user_password){
        if($joneUser->active == 1){
            require_once('jwt.php');

            $userID = $joneUser->id;
            $serverKey = '5f2b5cdbe5194f10b3241568fe4e2b24';

            $payloadArray = array();
            $payloadArray['userID'] = $userID;

            // create a token
            $token = JWT::encode($payloadArray, $serverKey);

            unset($joneUser->password); // No password

            $loggedUser_name = $joneUser->firstName;

            echo '{"status":1, "message": "logged in", "token": "'.$token.'", "userID": "'.$userID.'", "userName": "'.$loggedUser_name.'"}';

            exit;
        } else if($joneUser->active == 0) {
            sendError( 'your account is not active anymore', __LINE__ );           
        }
    }
    if($total === 1){       
        sendError( 'incorrect credentials', __LINE__ );           
    }
    $total--;
}

//**************************************** */
function sendError($sMessage, $iLineNumber){
    echo '{"status":0, "message":"'.$sMessage.'", "line":'.$iLineNumber.'}';
    exit;  
}

 