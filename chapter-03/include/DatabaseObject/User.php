<?php
    class DatabaseObject_User extends DatabaseObject
    {
        static $userTypes = array('member'        => 'Member',
                                  'administrator' => 'Administrator');

        public $profile = null;

        public function __construct($db)
        {
            parent::__construct($db, 'users', 'user_id');

            $this->add('username');
            $this->add('password');
            $this->add('user_type', 'member');
            $this->add('ts_created', time(), self::TYPE_TIMESTAMP);
            $this->add('ts_last_login', null, self::TYPE_TIMESTAMP);

            $this->profile = new Profile_User($db);
        }

        protected function preInsert()
        {
            $this->password = uniqid();
            return true;
        }

        protected function postLoad()
        {
            $this->profile->setUserId($this->getId());
            $this->profile->load();
        }

        protected function postInsert()
        {
            $this->profile->setUserId($this->getId());
            $this->profile->save(false);
            return true;
        }

        protected function postUpdate()
        {
            $this->profile->save(false);
            return true;
        }

        protected function preDelete()
        {
            $this->profile->delete();
            return true;
        }

        public function __set($name, $value)
        {
            switch ($name) {
                case 'password':
                    $value = md5($value);
                    break;

                case 'user_type':
                    if (!array_key_exists($value, self::$userTypes))
                        $value = 'member';
                    break;
            }

            return parent::__set($name, $value);
        }
    }
?>