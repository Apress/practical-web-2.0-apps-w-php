<?php
    class BlogmanagerController extends CustomControllerAction
    {
        public function init()
        {
            parent::init();
            $this->breadcrumbs->addStep('Account', $this->getUrl(null, 'account'));
            $this->breadcrumbs->addStep('Blog Manager',
                                        $this->getUrl(null, 'blogmanager'));

            $this->identity = Zend_Auth::getInstance()->getIdentity();
        }

        public function indexAction()
        {
            // initialize the month
            $month = $this->getRequest()->getQuery('month');
            if (preg_match('/^(\d{4})-(\d{2})$/', $month, $matches)) {
                $y = $matches[1];
                $m = max(1, min(12, $matches[2]));
            }
            else {
                $y = date('Y'); // current year
                $m = date('n'); // current month
            }

            $from = mktime(0, 0, 0, $m, 1, $y);
            $to = mktime(0, 0, 0, $m + 1, 1, $y) - 1;

            $options = array(
                'user_id' => $this->identity->user_id,
                'from'    => date('Y-m-d H:i:s', $from),
                'to'      => date('Y-m-d H:i:s', $to),
                'order'   => 'p.ts_created desc'
            );

            $recentPosts = DatabaseObject_BlogPost::GetPosts($this->db,
                                                             $options);

            // get the total number of posts for this user
            $totalPosts = DatabaseObject_BlogPost::GetPostsCount(
                $this->db,
                array('user_id' => $this->identity->user_id)
            );

            $this->view->month = $from;
            $this->view->recentPosts = $recentPosts;
            $this->view->totalPosts = $totalPosts;
        }

        public function editAction()
        {
            $request = $this->getRequest();
            $post_id = (int) $this->getRequest()->getQuery('id');

            $fp = new FormProcessor_BlogPost($this->db,
                                             $this->identity->user_id,
                                             $post_id);

            if ($request->isPost()) {
                if ($fp->process($request)) {
                    $url = $this->getUrl('preview') . '?id=' . $fp->post->getId();
                    $this->_redirect($url);
                }
            }

            if ($fp->post->isSaved()) {
                $this->breadcrumbs->addStep(
                    'Preview Post: ' . $fp->post->profile->title,
                    $this->getUrl('preview') . '?id=' . $fp->post->getId()
                );
                $this->breadcrumbs->addStep('Edit Blog Post');
            }
            else
                $this->breadcrumbs->addStep('Create a New Blog Post');

            $this->view->fp = $fp;
        }

        public function previewAction()
        {
            $post_id = (int) $this->getRequest()->getQuery('id');

            $post = new DatabaseObject_BlogPost($this->db);
            if (!$post->loadForUser($this->identity->user_id, $post_id))
                $this->_redirect($this->getUrl());

            $this->breadcrumbs->addStep('Preview Post: ' . $post->profile->title);

            $this->view->post = $post;
        }

        public function setstatusAction()
        {
            $request = $this->getRequest();
            $post_id = (int) $request->getPost('id');

            $post = new DatabaseObject_BlogPost($this->db);
            if (!$post->loadForUser($this->identity->user_id, $post_id))
                $this->_redirect($this->getUrl());

            // URL to redirect back to
            $url = $this->getUrl('preview') . '?id=' . $post->getId();

            if ($request->getPost('edit')) {
                $this->_redirect($this->getUrl('edit') . '?id=' . $post->getId());
            }
            else if ($request->getPost('publish')) {
                $post->sendLive();
                $post->save();

                $this->messenger->addMessage('Post sent live');
            }
            else if ($request->getPost('unpublish')) {
                $post->sendBackToDraft();
                $post->save();

                $this->messenger->addMessage('Post unpublished');
            }
            else if ($request->getPost('delete')) {
                $post->delete();

                // Preview page no longer exists for this page so go back to index
                $url = $this->getUrl();

                $this->messenger->addMessage('Post deleted');
            }

            $this->_redirect($url);
        }

        public function tagsAction()
        {
            $request = $this->getRequest();

            $post_id = (int) $request->getPost('id');

            $post = new DatabaseObject_BlogPost($this->db);
            if (!$post->loadForUser($this->identity->user_id, $post_id))
                $this->_redirect($this->getUrl());

            $tag = $request->getPost('tag');

            if ($request->getPost('add')) {
                $post->addTags($tag);
                $this->messenger->addMessage('Tag added to post');
            }
            else if ($request->getPost('delete')) {
                $post->deleteTags($tag);
                $this->messenger->addMessage('Tag removed from post');
            }

            $this->_redirect($this->getUrl('preview') . '?id=' . $post->getId());
        }
    }
?>