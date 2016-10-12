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

    // Set their profile data
    $user->profile->email = 'user@example.com';
    $user->profile->country = 'Australia';

    // Save the user and their profile
    $user->save();

    // Load some other user and delete them
    $user2 = new DatabaseObject_User($db);
    if ($user2->load(1234))
        $user2->delete();
?>