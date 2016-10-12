<?php
    require_once('Zend/Loader.php');
    Zend_Loader::registerAutoload();

    // connect to the database
    $params = array('host'     => 'localhost',
                    'username' => 'phpweb20',
                    'password' => 'myPassword',
                    'dbname'   => 'phpweb20');

    $db = Zend_Db::factory('pdo_mysql', $params);

    $profile = new Profile_User($db);
    $profile->setUserId(1234);
    $profile->load();

    $profile->email = 'user@example.com';
    $profile->country = 'Australia';
    $profile->save();

    if (isset($profile->country))
        echo sprintf('Your country is %s', $profile->country);
?>