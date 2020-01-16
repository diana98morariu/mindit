<?php

    $quizzesData = array(
        array(
            'id' => '1', 
            'name' => 'Quiz 1'
        ),
        array(
            'id' => '2', 
            'name' => 'Quiz 2'
        ),
        array(
            'id' => '3', 
            'name' => 'Quiz 3'
        ),
    );

    // connect to mongodb
    $DB_CONNECTION_STRING="mongodb://mindit_user:mindit123@ds157383.mlab.com:57383/heroku_1tj0x3m6";
    require '../vendor/autoload.php';
    $manager = new MongoDB\Driver\Manager( $DB_CONNECTION_STRING );

    $bulkWrite = new MongoDB\Driver\BulkWrite;
    echo 'ok';
    // $doc = ['_id' => '2'];
    // $bulkWrite->insert($doc);
    // $manager->executeBulkWrite('db.quizzes', $bulkWrite);