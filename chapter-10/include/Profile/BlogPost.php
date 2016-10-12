<?php
    class Profile_BlogPost extends Profile
    {
        public function __construct($db, $post_id = null)
        {
            parent::__construct($db, 'blog_posts_profile');

            if ($post_id > 0)
                $this->setPostId($post_id);
        }

        public function setPostId($post_id)
        {
            $filters = array('post_id' => (int) $post_id);
            $this->_filters = $filters;
        }
    }
?>