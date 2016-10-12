<?php
    require_once('Zend/Loader.php');
    Zend_Loader::registerAutoload();

    // connect to the database
    $params = array('host'     => 'localhost',
                    'username' => 'phpweb20',
                    'password' => 'myPassword',
                    'dbname'   => 'phpweb20');

    $db = Zend_Db::factory('pdo_mysql', $params);

    // Create a new user
    $user = new DatabaseObject_User($db);
    $user->username = 'someUser';
    $user->password = 'myPassword';
    $user->save();

    // Now update that user and save new details
    $user->user_type = 'admin';
    $user->ts_last_login = time();
    $user->save();

    // Find a user with user_id of 5 and delete them
    $user2 = new DatabaseObject_User($db);
    if ($user2->load(5)) {
        $user2->delete();
    }
?>