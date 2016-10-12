<?php
    class UserController extends CustomControllerAction
    {
        protected $user = null;

        public function preDispatch()
        {
            // call parent method to perform standard predispatch tasks
            parent::preDispatch();

            // retrieve request object so we can access requested user and action
            $request = $this->getRequest();

            // check if already dispatching the user not found action. if we are
            // then we don't want to execute the remainder of this method
            if (strtolower($request->getActionName()) == 'usernotfound')
                return;

            // retrieve username from request and clean the string
            $username = trim($request->getUserParam('username'));

            // if no username is present, redirect to site home page
            if (strlen($username) == 0)
                $this->_redirect($this->getUrl('index', 'index'));

            // load the user, based on username in request. if the user record
            // is not loaded then forward to notFoundAction so a 'user not found'
            // message can be shown to the user.

            $this->user = new DatabaseObject_User($this->db);

            if (!$this->user->loadByUsername($username)) {
                $this->_forward('userNotFound');
                return;
            }

            // Add a link to the breadcrumbs so all actions in this controller
            // link back to the user home page
            $this->breadcrumbs->addStep(
                $this->user->username . "'s Blog",
                $this->getCustomUrl(
                    array('username' => $this->user->username,
                          'action'   => 'index'),
                    'user'
                )
            );

            // Make the user data available to all templates in this controller
            $this->view->user = $this->user;
        }

        public function userNotFoundAction()
        {
            $username = trim($this->getRequest()->getUserParam('username'));

            $this->breadcrumbs->addStep('User Not Found');
            $this->view->requestedUsername = $username;
        }

        public function indexAction()
        {
            if (isset($this->user->profile->num_posts))
                $limit = max(1, (int) $this->user->profile->num_posts);
            else
                $limit = 10;

            $options = array(
                'user_id' => $this->user->getId(),
                'status'  => DatabaseObject_BlogPost::STATUS_LIVE,
                'limit'   => $limit,
                'order'   => 'p.ts_created desc'
            );

            $posts = DatabaseObject_BlogPost::GetPosts($this->db,
                                                       $options);

            $this->view->posts = $posts;
        }

        public function viewAction()
        {
            $request = $this->getRequest();
            $url = trim($request->getUserParam('url'));

            // if no URL was specified, return to the user home page
            if (strlen($url) == 0) {
                $this->_redirect($this->getCustomUrl(
                    array('username' => $this->user->username,
                          'action'   => 'index'),
                    'user'
                ));
            }

            // try and load the post
            $post = new DatabaseObject_BlogPost($this->db);
            $post->loadLivePost($this->user->getId(), $url);

            // if the post wasn't loaded redirect to postNotFound
            if (!$post->isSaved()) {
                $this->_forward('postNotFound');
                return;
            }

            // build options for the archive breadcrumbs link
            $archiveOptions = array(
                'username' => $this->user->username,
                'year'     => date('Y', $post->ts_created),
                'month'    => date('m', $post->ts_created)
            );

            $this->breadcrumbs->addStep(
                date('F Y', $post->ts_created),
                $this->getCustomUrl($archiveOptions, 'archive')
            );
            $this->breadcrumbs->addStep($post->profile->title);

            // make the post available to the template
            $this->view->post = $post;
        }

        public function postNotFoundAction()
        {
            $this->breadcrumbs->addStep('Post Not Found');
        }

        public function archiveAction()
        {
            $request = $this->getRequest();

            // initialize requested date or month
            $m = (int) trim($request->getUserParam('month'));
            $y = (int) trim($request->getUserParam('year'));

            // ensure month is in range 1-12
            $m = max(1, min(12, $m));

            // generate start and finish timestamp for the given month/year
            $from = mktime(0, 0, 0, $m,     1, $y);
            $to   = mktime(0, 0, 0, $m + 1, 1, $y) - 1;


            // get live posts based on timestamp with newest posts listed first
            $options = array(
                'user_id' => $this->user->getId(),
                'from'    => date('Y-m-d H:i:s', $from),
                'to'      => date('Y-m-d H:i:s', $to),
                'status'  => DatabaseObject_BlogPost::STATUS_LIVE,
                'order'   => 'p.ts_created desc'
            );
            $posts = DatabaseObject_BlogPost::GetPosts($this->db,
                                                       $options);

            $this->breadcrumbs->addStep(date('F Y', $from));

            // assign the requested month and the posts found to the template
            $this->view->month = $from;
            $this->view->posts = $posts;
        }

        public function tagAction()
        {
            $request = $this->getRequest();

            $tag = trim($request->getUserParam('tag'));
            if (strlen($tag) == 0) {
                $this->_redirect($this->getCustomUrl(
                    array('username' => $this->user->username,
                          'action' => 'index'),
                    'user'
                ));
            }

            $options = array(
                'user_id' => $this->user->getId(),
                'tag'     => $tag,
                'status'  => DatabaseObject_BlogPost::STATUS_LIVE,
                'order'   => 'p.ts_created desc'
            );
            $posts = DatabaseObject_BlogPost::GetPosts($this->db,
                                                       $options);

            $this->breadcrumbs->addStep('Tag: ' . $tag);
            $this->view->tag = $tag;
            $this->view->posts = $posts;
        }

        public function feedAction()
        {
            // first retrieve all recent posts
            $options = array(
                'user_id' => $this->user->getId(),
                'status'  => DatabaseObject_BlogPost::STATUS_LIVE,
                'limit'   => 10,
                'order'   => 'p.ts_created desc'
            );

            $recentPosts = DatabaseObject_BlogPost::GetPosts($this->db,
                                                             $options);

            // base URL for generated links
            $domain = 'http://' . $this->getRequest()->getServer('HTTP_HOST');

            // url for web feed
            $url = $this->getCustomUrl(
                array('username' => $this->user->username,
                      'action' => 'index'),
                'user'
            );

            $feedData = array(
                'title'   => sprintf("%s's Blog", $this->user->username),
                'link'    => $domain . $url,
                'charset' => 'UTF-8',
                'entries' => array()
            );

            // build feed entries based on returned posts
            foreach ($recentPosts as $post) {
                $url = $this->getCustomUrl(
                    array('username' => $this->user->username,
                          'url' => $post->url),
                    'post'
                );

                $entry = array(
                    'title'       => $post->profile->title,
                    'link'        => $domain . $url,
                    'description' => $post->getTeaser(200),
                    'lastUpdate'  => $post->ts_created,
                    'category'    => array()
                );

                // attach tags to each entry
                foreach ($post->getTags() as $tag) {
                    $entry['category'][] = array('term' => $tag);
                }

                $feedData['entries'][] = $entry;
            }

            // create feed based on created data
            $feed = Zend_Feed::importArray($feedData, 'atom');

            // disable auto-rendering since we're outputting an image
            $this->_helper->viewRenderer->setNoRender();

            // output the feed to the browser
            $feed->send();
        }
    }
?>