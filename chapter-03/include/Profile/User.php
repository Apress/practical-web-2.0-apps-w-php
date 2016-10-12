<?php
    class Profile_User extends Profile
    {
        public function __construct($db, $user_id = null)
        {
            parent::__construct($db, 'users_profile');

            if ($user_id > 0)
                $this->setUserId($user_id);
        }

        public function setUserId($user_id)
        {
            $filters = array('user_id' => (int) $user_id);
            $this->_filters = $filters;
        }
    }
?>