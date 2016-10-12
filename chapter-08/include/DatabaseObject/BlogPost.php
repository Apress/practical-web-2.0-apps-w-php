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

        public function getTeaser($length)
        {
            require_once('Smarty/plugins/modifier.truncate.php');

            return smarty_modifier_truncate(strip_tags($this->profile->content),
                                            $length);
        }

        public static function GetPostsCount($db, $options)
        {
            $select = self::_GetBaseQuery($db, $options);
            $select->from(null, 'count(*)');

            return $db->fetchOne($select);
        }

        public static function GetPosts($db, $options = array())
        {
            // initialize the options
            $defaults = array(
                'offset' => 0,
                'limit' => 0,
                'order' => 'p.ts_created'
            );

            foreach ($defaults as $k => $v) {
                $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
            }

            $select = self::_GetBaseQuery($db, $options);

            // set the fields to select
            $select->from(null, 'p.*');

            // set the offset, limit, and ordering of results
            if ($options['limit'] > 0)
                $select->limit($options['limit'], $options['offset']);

            $select->order($options['order']);

            // fetch post data from database
            $data = $db->fetchAll($select);

            // turn data into array of DatabaseObject_BlogPost objects
            $posts = self::BuildMultiple($db, __CLASS__, $data);
            $post_ids = array_keys($posts);

            if (count($post_ids) == 0)
                return array();

            // load the profile data for loaded posts
            $profiles = Profile::BuildMultiple(
                $db,
                'Profile_BlogPost',
                array('post_id' => $post_ids)
            );

            foreach ($posts as $post_id => $post) {
                if (array_key_exists($post_id, $profiles)
                        && $profiles[$post_id] instanceof Profile_BlogPost) {

                    $posts[$post_id]->profile = $profiles[$post_id];
                }
                else {
                    $posts[$post_id]->profile->setPostId($post_id);
                }
            }

            return $posts;
        }

        public static function GetMonthlySummary($db, $options)
        {
            if ($db instanceof Zend_Db_Adapter_Pdo_Mysql)
                $dateString = "date_format(p.ts_created, '%Y-%m')";
            else
                $dateString = "to_char(p.ts_created, 'yyyy-mm')";

            // initialize the options
            $defaults = array(
                'offset' => 0,
                'limit' => 0,
                'order' => $dateString . ' desc'
            );
            foreach ($defaults as $k => $v) {
                $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
            }

            $select = self::_GetBaseQuery($db, $options);
            $select->from(null,
                          array($dateString . ' as month',
                                'count(*) as num_posts'));

            $select->group($dateString);

            $select->order($options['order']);

            return $db->fetchPairs($select);
        }

        private static function _GetBaseQuery($db, $options)
        {
            // initialize the options
            $defaults = array(
                'user_id' => array(),
                'from'    => '',
                'to'      => ''
            );

            foreach ($defaults as $k => $v) {
                $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
            }

            // create a query that selects from the blog_posts table
            $select = $db->select();
            $select->from(array('p' => 'blog_posts'), array());

            // filter the records based on the start and finish dates
            if (strlen($options['from']) > 0) {
                $ts = strtotime($options['from']);
                $select->where('p.ts_created >= ?', date('Y-m-d H:i:s', $ts));
            }

            if (strlen($options['to']) > 0) {
                $ts = strtotime($options['to']);
                $select->where('p.ts_created <= ?', date('Y-m-d H:i:s', $ts));
            }

            // filter results on specified user ids (if any)
            if (count($options['user_id']) > 0)
                $select->where('p.user_id in (?)', $options['user_id']);

            return $select;
        }
    }
?>