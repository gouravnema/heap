<?php

class heap
{
    
    protected $_bucket;

    protected $_db_path;

    protected $_dirObj;

    public function __construct($bucket = "globals", $dbPath = "./buckets")
    {
        if (is_dir($dbPath."/".$bucket)) {       
        $this->_db_path = $dbPath;
        $this->_bucket = $bucket;
        $this->_dirObj = dir($this->_db_path . "/" . $this->_bucket);
        }
        else {
        	$this->createBucket($bucket);
        	$this->swapBucket($bucket);
        }
    }

    public function createBucket($bucket_name = "")
    {
        if ($bucket_name != "") {
            if (self::directoryExist($bucket_name, $this->_db_path) === false) {
                mkdir($this->_db_path . "/" . $bucket_name);
            }
            $this->swapBucket($bucket_name);
            return true;
        }
        return false;
    }

    public function swapBucket($bucket_name)
    {
        if ($this->directoryExist($bucket_name, $this->_db_path)) {
            $this->_bucket = $bucket_name;
            $this->_dirObj = dir($this->_db_path . "/" . $this->_bucket);
            return true;
        }
        return false;
    }

    public function dropBucket($bucket_name)
    {   
        if(!empty($this->_bucket)&& !empty($this->_db_path)) return false;
        self::rrmdir($this->_db_path . "/" . $bucket_name);
        if ($this->_bucket == $bucket_name) {
            $this->swapBucket("globals");
        }
    }

    public function set($key, $value)
    {
        if(empty($this->_bucket) || empty($this->_db_path)) return false;
        
        if (file_put_contents($this->_db_path . "/" . $this->_bucket . "/" . $key, $value) !== false) {
            return true;
        }
        return false;
    }

    public function get($key)
    {
        $data = @file_get_contents($this->_db_path . "/" . $this->_bucket . "/" . $key,false);
        if ($data !== false) {
            return $data;
        }
        return false;
    }

    public function drop($key)
    {
        unlink($this->_db_path . "/" . $this->_bucket . "/" . $key);
    }

    public function append($key, $value)
    {
        $fpa = fopen($this->_db_path . "/" . $this->_bucket . "/" . $key, 'a');
        fwrite($fpa, $value);
        fclose($fpa);
    }

    public function install($path = "./")
    {
      
        if (!$this->directoryExist("buckets", $path)) {
            chdir($path);
            mkdir("./buckets");
            mkdir("./buckets/globals");
            $this->swapBucket('globals');
        }
    }

    public static function directoryExist($dirName, $path)
    {
        $dirObj = dir($path);
        if (is_a($dirObj, "Directory",true) == false) return false;
        $bucket_exist = false;
        while ($entry = $dirObj->read()) {
            if ($entry == $dirName and is_dir($entry)) {
                $bucket_exist = true;
                break;
            }
        }
        $dirObj->close();
        return $bucket_exist;
    }

    public static function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        rrmdir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
?>
