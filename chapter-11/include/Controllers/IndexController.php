<?php
    class IndexController extends CustomControllerAction
    {
        public function indexAction()
        {
            // define the options for retrieving blog posts
            $options = array(
                'status'  => DatabaseObject_BlogPost::STATUS_LIVE,
                'limit'   => 2,
                'order'   => 'p.ts_created desc',
                'public_only' => true
            );

            // retrieve the blog posts
            $posts = DatabaseObject_BlogPost::GetPosts($this->db, $options);

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

            // assign posts and users to the template
            $this->view->posts = $posts;
            $this->view->users = $users;
        }
    }
?>