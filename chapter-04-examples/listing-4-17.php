<?php
    require_once('Zend/Loader.php');
    Zend_Loader::registerAutoload();

    $mail = new Zend_Mail();
    $mail->setBodyText('E-mail body');
    $mail->setFrom('from@example.com');
    $mail->addTo('to@example.com');
    $mail->setSubject('E-mail Subject');
    $mail->send();
?>