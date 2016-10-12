<?php
    class DatabaseObject_BlogPost extends DatabaseObject
    {
        public $profile = null;

        const STATUS_DRAFT = 'D';
        const STATUS_LIVE  = 'L';

        public function __construct($db)
        {
            parent::__construct($db, 'blog_posts', 'post_id');

            $this->add('user_id');
            $this->add('url');
            $this->add('ts_created', time(), self::TYPE_TIMESTAMP);
            $this->add('status', self::STATUS_DRAFT);

            $this->profile = new Profile_BlogPost($db);
        }

        protected function preInsert()
        {
            $this->url = $this->generateUniqueUrl($this->profile->title);
            return true;
        }

        protected function postLoad()
        {
            $this->profile->setPostId($this->getId());
            $this->profile->load();
        }

        protected function postInsert()
        {
            $this->profile->setPostId($this->getId());
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

        public function loadForUser($user_id, $post_id)
        {
            $post_id = (int) $post_id;
            $user_id = (int) $user_id;

            if ($post_id <= 0 || $user_id <= 0)
                return false;

            $query = sprintf(
                'select %s from %s where user_id = %d and post_id = %d',
                join(', ', $this->getSelectFields()),
                $this->_table,
                $user_id,
                $post_id
            );

            return $this->_load($query);
        }

        public function sendLive()
        {
            if ($this->status != self::STATUS_LIVE) {
                $this->status = self::STATUS_LIVE;
                $this->profile->ts_published = time();
            }
        }

        public function isLive()
        {
            return $this->isSaved() && $this->status == self::STATUS_LIVE;
        }

        public function sendBackToDraft()
        {
            $this->status = self::STATUS_DRAFT;
        }

        protected function generateUniqueUrl($title)
        {
            $url = strtolower($title);

            $filters = array(
                // replace & with 'and' for readability
                '/&+/' => 'and',

                // replace non-alphanumeric characters with a hyphen
                '/[^a-z0-9]+/i' => '-',

                // replace multiple hyphens with a single hyphen
                '/-+/'          => '-'
            );


            // apply each replacement
            foreach ($filters as $regex => $replacement)
                $url = preg_replace($regex, $replacement, $url);

            // remove hyphens from the start and end of string
            $url = trim($url, '-');

            // restrict the length of the URL
            $url = trim(substr($url, 0, 30));

            // set a default value just in case
            if (strlen($url) == 0)
                $url = 'post';


            // find similar URLs
            $query = sprintf("select url from %s where user_id = %d and url like ?",
                             $this->_table,
                             $this->user_id);

            $query = $this->_db->quoteInto($query, $url . '%');
            $result = $this->_db->fetchCol($query);


            // if no matching URLs then return the current URL
            if (count($result) == 0 || !in_array($url, $result))
                return $url;

            // generate a unique URL
            $i = 2;
            do {
                $_url = $url . '-' . $i++;
            } while (in_array($_url, $result));

            return $_url;
        }
    }
?>