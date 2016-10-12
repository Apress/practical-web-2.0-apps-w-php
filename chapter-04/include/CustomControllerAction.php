<?php
    class CustomControllerAction extends Zend_Controller_Action
    {
        public $db;

        function init()
        {
            $this->db = Zend_Registry::get('db');
        }

        public function preDispatch()
        {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $this->view->authenticated = true;
                $this->view->identity = $auth->getIdentity();
            }
            else
                $this->view->authenticated = false;
        }
    }
?>