<?php
    class DatabaseObject_BlogPostImage extends DatabaseObject
    {
        protected $_uploadedFile;

        public function __construct($db)
        {
            parent::__construct($db, 'blog_posts_images', 'image_id');

            $this->add('filename');
            $this->add('post_id');
            $this->add('ranking');
        }

        public function preInsert()
        {
            // first check that we can write the upload directory
            $path = self::GetUploadPath();
            if (!file_exists($path) || !is_dir($path))
                throw new Exception('Upload path ' . $path . ' not found');

            if (!is_writable($path))
                throw new Exception('Unable to write to upload path ' . $path);

            // now determine the ranking of the new image
            $query = sprintf(
                'select coalesce(max(ranking), 0) + 1 from %s where post_id = %d',
                $this->_table,
                $this->post_id
            );

            $this->ranking = $this->_db->fetchOne($query);
            return true;
        }

        public function preDelete()
        {
            unlink($this->getFullPath());

            $pattern = sprintf('%s/%d.*',
                               self::GetThumbnailPath(),
                               $this->getId());

            foreach (glob($pattern) as $thumbnail) {
                unlink($thumbnail);
            }

            return true;
        }

        public function postInsert()
        {
            if (strlen($this->_uploadedFile) > 0)
                return move_uploaded_file($this->_uploadedFile,
                                          $this->getFullPath());

            return false;
        }

        public function loadForPost($post_id, $image_id)
        {
            $post_id = (int) $post_id;
            $image_id = (int) $image_id;

            if ($post_id <= 0 || $image_id <= 0)
                return false;

            $query = sprintf(
                'select %s from %s where post_id = %d and image_id = %d',
                join(', ', $this->getSelectFields()),
                $this->_table,
                $post_id,
                $image_id
            );

            return $this->_load($query);
        }

        public function uploadFile($path)
        {
            if (!file_exists($path) || !is_file($path))
                throw new Exception('Unable to find uploaded file');

            if (!is_readable($path))
                throw new Exception('Unable to read uploaded file');

            $this->_uploadedFile = $path;
        }

        public function getFullPath()
        {
            return sprintf('%s/%d', self::GetUploadPath(), $this->getId());
        }

        public function createThumbnail($maxW, $maxH)
        {
            $fullpath = $this->getFullpath();

            $ts = (int) filemtime($fullpath);
            $info = getImageSize($fullpath);

            $w = $info[0];          // original width
            $h = $info[1];          // original height

            $ratio = $w / $h;       // width:height ratio

            $maxW = min($w, $maxW); // new width can't be more than $maxW
            if ($maxW == 0)         // check if only max height has been specified
                $maxW = $w;

            $maxH = min($h, $maxH); // new height can't be more than $maxH
            if ($maxH == 0)         // check if only max width has been specified
                $maxH = $h;

            $newW = $maxW;          // first use the max width to determine new
            $newH = $newW / $ratio; // height by using original image w:h ratio

            if ($newH > $maxH) {        // check if new height is too big, and if
                $newH = $maxH;          // so determine the new width based on the
                $newW = $newH * $ratio; // max height
            }

            if ($w == $newW && $h == $newH) {
                // no thumbnail required, just return the original path
                return $fullpath;
            }

            switch ($info[2]) {
                case IMAGETYPE_GIF:
                    $infunc = 'ImageCreateFromGif';
                    $outfunc = 'ImageGif';
                    break;

                case IMAGETYPE_JPEG:
                    $infunc = 'ImageCreateFromJpeg';
                    $outfunc = 'ImageJpeg';
                    break;

                case IMAGETYPE_PNG:
                    $infunc = 'ImageCreateFromPng';
                    $outfunc = 'ImagePng';
                    break;

                default;
                    throw new Exception('Invalid image type');
            }

            // create a unique filename based on the specified options
            $filename = sprintf('%d.%dx%d.%d',
                                $this->getId(),
                                $newW,
                                $newH,
                                $ts);

            // autocreate the directory for storing thumbnails
            $path = self::GetThumbnailPath();
            if (!file_exists($path))
                mkdir($path, 0777);

            if (!is_writable($path))
                throw new Exception('Unable to write to thumbnail dir');

            // determine the full path for the new thumbnail
            $thumbPath = sprintf('%s/%s', $path, $filename);

            if (!file_exists($thumbPath)) {

                // read the image in to GD
                $im = @$infunc($fullpath);
                if (!$im)
                    throw new Exception('Unable to read image file');

                // create the output image
                $thumb = ImageCreateTrueColor($newW, $newH);

                // now resample the original image to the new image
                ImageCopyResampled($thumb, $im, 0, 0, 0, 0, $newW, $newH, $w, $h);

                $outfunc($thumb, $thumbPath);
            }

            if (!file_exists($thumbPath))
                throw new Exception('Unknown error occurred creating thumbnail');
            if (!is_readable($thumbPath))
                throw new Exception('Unable to read thumbnail');

            return $thumbPath;
        }

        public static function GetImageHash($id, $w, $h)
        {
            $id = (int) $id;
            $w  = (int) $w;
            $h  = (int) $h;

            return md5(sprintf('%s,%s,%s', $id, $w, $h));
        }

        public static function GetUploadPath()
        {
            $config = Zend_Registry::get('config');

            return sprintf('%s/uploaded-files', $config->paths->data);
        }

        public static function GetThumbnailPath()
        {
            $config = Zend_Registry::get('config');

            return sprintf('%s/tmp/thumbnails', $config->paths->data);
        }

        public static function GetImages($db, $options = array())
        {
            // initialize the options
            $defaults = array('post_id' => array());

            foreach ($defaults as $k => $v) {
                $options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
            }

            $select = $db->select();
            $select->from(array('i' => 'blog_posts_images'), array('i.*'));

            // filter results on specified post ids (if any)
            if (count($options['post_id']) > 0)
                $select->where('i.post_id in (?)', $options['post_id']);

            $select->order('i.ranking');

            // fetch post data from database
            $data = $db->fetchAll($select);

            // turn data into array of DatabaseObject_BlogPostImage objects
            $images = parent::BuildMultiple($db, __CLASS__, $data);

            return $images;
        }
    }
?>