<?php
    class FormProcessor_UserRegistration extends FormProcessor
    {
        protected $db = null;
        public $user = null;
        protected $_validateOnly = false;

        public function __construct($db)
        {
            parent::__construct();
            $this->db = $db;
            $this->user = new DatabaseObject_User($db);
            $this->user->type = 'member';
        }

        public function validateOnly($flag)
        {
            $this->_validateOnly = (bool) $flag;
        }

        public function process(Zend_Controller_Request_Abstract $request)
        {
            // validate the username
            $this->username = trim($request->getPost('username'));

            if (strlen($this->username) == 0)
                $this->addError('username', 'Please enter a username');
            else if (!DatabaseObject_User::IsValidUsername($this->username))
                $this->addError('username', 'Please enter a valid username');
            else if ($this->user->usernameExists($this->username))
                $this->addError('username', 'The selected username already exists');
            else
                $this->user->username = $this->username;

            // validate the user's name
            $this->first_name = $this->sanitize($request->getPost('first_name'));
            if (strlen($this->first_name) == 0)
                $this->addError('first_name', 'Please enter your first name');
            else
                $this->user->profile->first_name = $this->first_name;

            $this->last_name = $this->sanitize($request->getPost('last_name'));
            if (strlen($this->last_name) == 0)
                $this->addError('last_name', 'Please enter your last name');
            else
                $this->user->profile->last_name = $this->last_name;

            // validate the e-mail address
            $this->email = $this->sanitize($request->getPost('email'));
            $validator = new Zend_Validate_EmailAddress();

            if (strlen($this->email) == 0)
                $this->addError('email', 'Please enter your e-mail address');
            else if (!$validator->isValid($this->email))
                $this->addError('email', 'Please enter a valid e-mail address');
            else
                $this->user->profile->email = $this->email;

            // validate CAPTCHA phrase

            $session = new Zend_Session_Namespace('captcha');
            $this->captcha = $this->sanitize($request->getPost('captcha'));

            if ($this->captcha != $session->phrase)
                $this->addError('captcha', 'Please enter the correct phrase');

            // if no errors have occurred, save the user
            if (!$this->_validateOnly && !$this->hasError()) {
                $this->user->save();
                unset($session->phrase);
            }

            // return true if no errors have occurred
            return !$this->hasError();
        }
    }
?>