<?php
    require_once('items.php');

    if (!dbConnect())
        exit;

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    switch ($action) {
        case 'load':
            $items = getItems();
            $xmlItems = array();
            foreach ($items as $id => $title)
                $xmlItems[] = sprintf('<item id="%d" title="%s" />',
                                      $id,
                                      htmlSpecialChars($title));

            $xml = sprintf('<items>%s</items>',
                           join("\n", $xmlItems));
            header('Content-type: text/xml');
            echo $xml;
            exit;

        case 'save':
            echo (int) processItemsOrder('items');
            exit;
    }
?>