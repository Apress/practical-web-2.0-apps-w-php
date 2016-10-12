<?php
    function dbConnect()
    {
        $link = mysql_connect('localhost', 'phpweb20', 'myPassword');
        if (!$link)
            return false;

        if (!mysql_select_db('ch05_example')) {
            mysql_close($link);
            return false;
        }
        return true;
    }

    function getItems()
    {
        $query = 'select item_id, title from items order by ranking, lower(title)';
        $result = mysql_query($query);

        $items = array();
        while ($row = mysql_fetch_object($result)) {
            $items[$row->item_id] = $row->title;
        }

        return $items;
    }

    function processItemsOrder($key)
    {
        if (!isset($_POST[$key]) || !is_array($_POST[$key]))
            return false;

        $items = getItems();

        $ranking = 1;
        foreach ($_POST[$key] as $id) {
            if (!array_key_exists($id, $items))
                continue;

            $query = sprintf('update items set ranking = %d where item_id = %d',
                             $ranking,
                             $id);
            mysql_query($query);
            $ranking++;
        }
        return true;
    }
?>