<?php

class Config
{
    public static $FILES_DIR_PATH     = "/home/muka/ftp/default/";
    public static $DONE_PATH          = "done/";
    public static $MAX_TRIES          = 3;
    public static $YT_CHUNK_SIZE      = 1048576; // 1MB in bytes
    public static $DR_CHUNK_SIZE      = 1048576;
    public static $INCLUDE_DIRS       = array('lib/', 
                                              'lib/google-api/src/', 
                                              'lib/google-api/src/contrib/');
    
    public static $DEFAULT_MIME_TYPE = 'video/avi';
    public static $MIME_TYPES = array('avi'  => 'video/avi',
                                      'wmv'  => 'video/x-ms-wmv',
                                      'mpg'  => 'video/mpeg',
                                      'mpeg' => 'video/mpeg',
                                      'mov'  => 'video/quicktime',
                                      'flv'  => 'video/x-flv',
                                      'mp4'  => 'video/mp4',
                                      '3gp'  => 'video/3gpp');
        
    /**
     * Handles class including.
     * @param string Class name.
     */
    public static function autoload($className)
    {
        foreach(Config::$INCLUDE_DIRS as $path)
        {
            $classPath = $path . $className . '.php';
            if(!class_exists($className) && file_exists($classPath))
            {
                require_once $classPath;
            }
        }
    }
}
