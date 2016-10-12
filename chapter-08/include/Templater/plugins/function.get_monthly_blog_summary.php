<?php
    function smarty_function_get_monthly_blog_summary($params, $smarty)
    {
        $options = array();

        if (isset($params['user_id']))
            $options['user_id'] = (int) $params['user_id'];

        $db = Zend_Registry::get('db');

        $summary = DatabaseObject_BlogPost::GetMonthlySummary($db, $options);

        if (isset($params['assign']) && strlen($params['assign']) > 0)
            $smarty->assign($params['assign'], $summary);
    }
?>