<?php
    class CustomControllerAction extends Zend_Controller_Action
    {
        public $db;

        function init()
        {
            $this->db = Zend_Registry::get('db');
        }
    }
?>