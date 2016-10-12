<?php
    class Templater extends Zend_View_Abstract
    {
        protected $_path;
        protected $_engine;

        public function __construct()
        {
            $config = Zend_Registry::get('config');

            require_once('Smarty/Smarty.class.php');

            $this->_engine = new Smarty();
            $this->_engine->template_dir = $config->paths->templates;
            $this->_engine->compile_dir = sprintf('%s/tmp/templates_c',
                                                  $config->paths->data);

            $this->_engine->plugins_dir = array($config->paths->base .
                                                '/include/Templater/plugins',
                                                'plugins');
        }

        public function getEngine()
        {
            return $this->_engine;
        }

        public function __set($key, $val)
        {
            $this->_engine->assign($key, $val);
        }

        public function __get($key)
        {
            return $this->_engine->get_template_vars($key);
        }

        public function __isset($key)
        {
            return $this->_engine->get_template_vars($key) !== null;
        }

        public function __unset($key)
        {
            $this->_engine->clear_assign($key);
        }

        public function assign($spec, $value = null)
        {
            if (is_array($spec)) {
                $this->_engine->assign($spec);
                return;
            }

            $this->_engine->assign($spec, $value);
        }

        public function clearVars()
        {
            $this->_engine->clear_all_assign();
        }

        public function render($name)
        {
            return $this->_engine->fetch(strtolower($name));
        }

        public function _run()
        { }
    }
?>