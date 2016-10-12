<?php
    class SearchController extends CustomControllerAction
    {
        public function indexAction()
        {
            $request = $this->getRequest();

            $q = trim($request->getQuery('q'));

            $search = array(
                'performed' => false,
                'limit'     => 5,
                'total'     => 0,
                'start'     => 0,
                'finish'    => 0,
                'page'      => (int) $request->getQuery('p'),
                'pages'     => 1,
                'results'   => array()
            );

            try {
                if (strlen($q) == 0)
                    throw new Exception('No search term specified');

                $path = DatabaseObject_BlogPost::getIndexFullpath();
                $index = Zend_Search_Lucene::open($path);
                $hits = $index->find($q);

                $search['performed'] = true;
                $search['total'] = count($hits);
                $search['pages'] = ceil($search['total'] / $search['limit']);
                $search['page'] = max(1, min($search['pages'], $search['page']));

                $offset = ($search['page'] - 1) * $search['limit'];

                $search['start'] = $offset + 1;
                $search['finish'] = min($search['total'],
                                        $search['start'] + $search['limit'] - 1);

                $hits = array_slice($hits, $offset, $search['limit']);
                $post_ids = array();
                foreach ($hits as $hit)
                    $post_ids[] = (int) $hit->post_id;

                $options = array('status' => DatabaseObject_BlogPost::STATUS_LIVE,
                                 'post_id' => $post_ids);

                $posts = DatabaseObject_BlogPost::GetPosts($this->db,
                                                           $options);

                foreach ($post_ids as $post_id) {
                    if (array_key_exists($post_id, $posts))
                        $search['results'][$post_id] = $posts[$post_id];
                }

                // determine which users' posts were retrieved
                $user_ids = array();
                foreach ($posts as $post)
                    $user_ids[$post->user_id] = $post->user_id;

                // load the user records
                if (count($user_ids) > 0) {
                    $options = array(
                        'user_id' => $user_ids
                    );

                    $users = DatabaseObject_User::GetUsers($this->db, $options);
                }
                else
                    $users = array();
            }
            catch (Exception $ex) {
                // no search performed or an error occurred
            }

            if ($search['performed'])
                $this->breadcrumbs->addStep('Search Results for ' . $q);
            else
                $this->breadcrumbs->addStep('Search');

            $this->view->q = $q;
            $this->view->search = $search;
            $this->view->users = $users;
        }

        public function suggestionAction()
        {
            $q = trim($this->getRequest()->getPost('q'));

            $suggestions = DatabaseObject_BlogPost::GetTagSuggestions($this->db,
                                                                      $q,
                                                                      10);
            $this->sendJson($suggestions);
        }
    }
?>