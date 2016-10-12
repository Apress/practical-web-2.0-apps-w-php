<?php
    class DatabaseObject_BlogPost extends DatabaseObject
    {
        public $images = array();
        public $locations = array();
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

            $options = array(
                'post_id' => $this->getId()
            );
            $this->images = DatabaseObject_BlogPostImage::GetImages($this->getDb(),
                                                                    $options);

            $this->locations = DatabaseObject_BlogPostLocation::GetLocations(
                $this->getDb(),
                $options
            );
        }

        protected function postInsert()
        {
            $this->profile->setPostId($this->getId());
            $this->profile->save(false);

            $this->addToIndex();

            return true;
        }

        protected function postUpdate()
        {
            $this->profile->save(false);

            $this->addToIndex();

            return true;
        }

        protected function preDelete()
        {
            $this->profile->delete();
            $this->deleteAllTags();

            foreach ($this->images as $image)
                $image->delete(false);

            $this->deleteFromIndex();

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

        public function loadLivePost($user_id, $url)
        {
            $user_id = (int) $user_id;
            $url     = trim($url);

            if ($user_id <= 0 || strlen($url) == 0)
                return false;

            $select = $this->_db->select();

            $select->from($this->_table, $this->getSelectFields())
                   ->where('user_id = ?', $user_id)
                   ->where('url = ?', $url)
                   ->where('status = ?', self::STATUS_LIVE);

            return $this->_load($select);
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

        public function getTags()
        {
            if (!$this->isSaved())
                return array();

            $query = 'select tag from blog_posts_tags where post_id = ?';

            // sort tags alphabetically
            $query .= ' order by lower(tag)';

            return $this->_db->fetchCol($query, $this->getId());
        }

        public function hasTag($tag)
        {
            if (!$this->isSaved())
                return false;

            $select = $this->_db->select();
            $select->from('blog_posts_tags', 'count(*)')
                   ->where('post_id = ?', $this->getId())
                   ->where('lower(tag) = lower(?)', trim($tag));

            return $this->_db->fetchOne($select) > 0;
        }

        public function addTags($tags)
        {
            if (!$this->isSaved())
                return;

            if (!is_array($tags))
                $tags = array($tags);

            // first create a clean list of tags
            $_tags = array();
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (strlen($tag) == 0)
                    continue;

                $_tags[strtolower($tag)] = $tag;
            }

            // now insert each into the database, first ensuring
            // it doesn't already exist for the current post
            $existingTags = array_map('strtolower', $this->getTags());

            foreach ($_tags as $lower => $tag) {
                if (in_array($lower, $existingTags))
                    continue;

                $data = array('post_id' => $this->getId(),
                              'tag' => $tag);

                $this->_db->insert('blog_posts_tags', $data);
            }

            $this->addToIndex();
        }

        public function deleteTags($tags)
        {
            if (!$this->isSaved())
                return;

            if (!is_array($tags))
                $tags = array($tags);

            $_tags = array();
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (strlen($tag) > 0)
                    $_tags[] = strtolower($tag);
            }

            if (count($_tags) == 0)
                return;

            $where = array('post_id = ' . $this->getId(),
                           $this->_db->quoteInto('lower(tag) in (?)', $tags));

            $this->_db->delete('blog_posts_tags', $where);

            $this->addToIndex();
        }

        public function deleteAllTags()
        {
            if (!$this->isSaved())
                return;

            $this->_db->delete('blog_posts_tags', 'post_id = ' . $this->getId());
        }

        public static function GetTagSuggestions($db, $partialTag, $limit = 0)
        {
            $partialTag = trim($partialTag);
            if (strlen($partialTag) == 0)
                return array();

            $select = $db->select();
            $select->distinct();
            $select->from(array('t' => 'blog_posts_tags'), 'lower(tag)')
                   ->joinInner(array('p' => 'blog_posts'),
                                     't.post_id = p.post_id',
                                     array())
                   ->where('lower(t.tag) like lower(?)', $partialTag . '%')
                   ->where('p.status = ?', self::STATUS_LIVE)
                   ->order('lower(t.tag)');

            if ($limit > 0)
                $select->limit($limit);

            return $db->fetchCol($select);
        }

        public function setImageOrder($order)
        {
            // sanitize the image IDs
            if (!is_array($order))
                return;

            $newOrder = array();
            foreach ($order as $image_id) {
                if (array_key_exists($image_id, $this->images))
                    $newOrder[] = $image_id;
            }

            // ensure the correct number of IDs were passed in
            $newOrder = array_unique($newOrder);
            if (count($newOrder) != count($this->images)) {
                return;
            }

            // now update the database
            $rank = 1;
            foreach ($newOrder as $image_id) {
                $this->_db->update('blog_posts_images',
                                   array('ranking' => $rank),
                                   'image_id = ' . $image_id);

                $rank++;
            }
        }

        public function getIndexableDocument()
        {
            $doc = new Zend_Search_Lucene_Document();
            $doc->addField(Zend_Search_Lucene_Field::Keyword('post_id',
                                                             $this->getId()));

            $fields = array(
                'title'     => $this->profile->title,
                'content'   => strip_tags($this->profile->content),
                'published' => $this->profile->ts_published,
                'tags'      => join(' ' , $this->getTags())
            );

            foreach ($fields as $name => $field) {
                $doc->addField(
                    Zend_Search_Lucene_Field::UnStored($name, $field)
                );
            }

            return $doc;
        }

        public static function getIndexFullpath()
        {
            $config = Zend_Registry::get('config');

            return sprintf('%s/search-index',
                           $config->paths->data);
        }

        protected function addToIndex()
        {
            try {
                $index = Zend_Search_Lucene::open(self::getIndexFullpath());
            }
            catch (Exception $ex) {
                self::RebuildIndex();
                return;
            }

            try {
                $query = new Zend_Search_Lucene_Search_Query_Term(
                    new Zend_Search_Lucene_Index_Term($this->getId(), 'post_id')
                );

                $hits = $index->find($query);
                foreach ($hits as $hit)
                    $index->delete($hit->id);

                if ($this->status == self::STATUS_LIVE)
                    $index->addDocument($this->getIndexableDocument());

                $index->commit();
            }
            catch (Exception $ex) {
                $logger = Zend_Registry::get('logger');
                $logger->warn('Error updating document in search index: ' .
                              $ex->getMessage());
            }
        }

        protected function deleteFromIndex()
        {
            try {
                $index = Zend_Search_Lucene::open(self::getIndexFullpath());
                $query = new Zend_Search_Lucene_Search_Query_Term(
                    new Zend_Search_Lucene_Index_Term($this->getId(), 'post_id')
                );

                $hits = $index->find($query);
                foreach ($hits as $hit)
                    $index->delete($hit->id);

                $index->commit();
            }
            catch (Exception $ex) {
                $logger = Zend_Registry::get('logger');
                $logger->warn('Error removing document from search index: ' .
                              $ex->getMessage());
            }
        }

        public static function RebuildIndex()
        {
            try {
                $index = Zend_Search_Lucene::create(self::getIndexFullpath());
                $options = array('status' => self::STATUS_LIVE);
                                 $posts = self::GetPosts(Zend_Registry::get('db'),
                                 $options);

                foreach ($posts as $post) {
                    $index->addDocument($post->getIndexableDocument());
                }
                $index->commit();
            }
            catch (Exception $ex) {
                $logger = Zend_Registry::get('logger');
                $logger->warn('Error rebuilding search index: ' .
                              $ex->getMessage());
            }
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

            // load the images for each post
            $options = array('post_id' => $post_ids);
            $images = DatabaseObject_BlogPostImage::GetImages($db, $options);

            foreach ($images as $image) {
                $posts[$image->post_id]->images[$image->getId()] = $image;
            }

            // load the locations for each post
            $locations = DatabaseObject_BlogPostLocation::GetLocations($db,
                                                                       $options);

            foreach ($locations as $l)
                $posts[$l->post_id]->locations[$l->getId()] = $l;

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
                'public_only' => false,
                'status'  => '',
                'tag'     => '',
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

            // filter results based on post status
            if (strlen($options['status']) > 0)
                $select->where('status = ?', $options['status']);

            if ($options['public_only']) {
                $select->joinInner(array('up' => 'users_profile'),
                                   'p.user_id = up.user_id',
                                   array())
                       ->where("profile_key = 'blog_public'")
                       ->where('profile_value = 1');
            }

            $options['tag'] = trim($options['tag']);
            if (strlen($options['tag']) > 0) {
                $select->joinInner(array('t' => 'blog_posts_tags'),
                                   't.post_id = p.post_id',
                                   array())
                       ->where('t.tag = ?', $options['tag']);
            }

            return $select;
        }

        public static function GetTagSummary($db, $user_id)
        {
            $select = $db->select();
            $select->from(array('t' => 'blog_posts_tags'),
                          array('count(*) as count', 't.tag'))
                   ->joinInner(array('p' => 'blog_posts'),
                               'p.post_id = t.post_id',
                               array())
                   ->where('p.user_id = ?', $user_id)
                   ->where('p.status = ?', self::STATUS_LIVE)
                   ->group('t.tag');

            $result = $db->query($select);
            $tags = $result->fetchAll();

            $summary = array();

            foreach ($tags as $tag) {
                $_tag = strtolower($tag['tag']);

                if (array_key_exists($_tag, $summary))
                    $summary[$_tag]['count'] += $tag['count'];
                else
                    $summary[$_tag] = $tag;
            }

            return $summary;
        }
    }
?>