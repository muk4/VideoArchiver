<?php

/**
 * This class handles upload settings, Google Drive uploads and YouTube uploads.
 */
class Uploader
{
    /**
     * @var array Stores upload settings in associative array.
     */
    private $settings = array();
    
    /**
     * Uploads all files from default directory.
     * @throws UploaderException
     */
    public function uploadAll()
    {
        $filePaths = glob(Config::$FILES_DIR_PATH . '*.???');
        
        if(empty($filePaths))
        {
            Messages::show('noFiles');
            return;
        }
        
        Messages::show('titleSettings');
        $this->setSettings();
        
        foreach($filePaths as $filePath)
        {
            $this->upload($filePath);
        }
    }
    
    /**
     * Uploads single file.
     * @param string Path of file to be uploaded.
     * @throws UploaderException
     */
    public function upload($filePath)
    {
        if(!file_exists($filePath))
        {
            throw new UploaderException(Messages::get('fileNotExists', $filePath));
        }
        
        Messages::show('titleUpload', $filePath);
        
        if(empty($this->settings))
        {
            Messages::show('titleSettings');
            $this->setSettings();
        }
        
        if($this->settings['drive'])
        {
            $this->handleGoogleDrive($filePath);
        }

        if($this->settings['youtube'])
        {
            $this->handleYouTube($filePath);
        }
        
        if($this->settings['moveToDoneDir'])
        {
            $this->moveToDoneDir($filePath);
        }
    }
    
    /**
     * Uploads file to Google Drive.
     * @param string Path of file to be uploaded.
     */
    private function handleGoogleDrive($filePath)
    {
        for($i = 1; $i <= Config::$MAX_TRIES; $i++)
        {
            try
            {
                Messages::show('driveUpload', $i);
                $drive = new GoogleDrive();
                $drive->upload($filePath);
                break;
            }
            catch(Exception $e)
            {
                Messages::show('simpleError', $e->getMessage());
            }
        }
    }
    
    /**
     * Uploades file to YouTube.
     * @param string Path of file to be uploaded.
     */
    private function handleYouTube($filePath)
    {
        for($i = 1; $i <= Config::$MAX_TRIES; $i++)
        {
            try
            {
                Messages::show('youtubeUpload', $i);
                $yt = new YouTube();
                $yt->upload($filePath);
                break;
            }
            catch(Exception $e)
            {
                Messages::show('simpleError', $e->getMessage());
            }
        }
    }
    
    /**
     * Moves file that was processed to 'done' directory.
     * @param string Path of file to be moved.
     */
    private function moveToDoneDir($filePath)
    {
        if(!file_exists(Config::$DONE_PATH))
            mkdir(Config::$DONE_PATH);
        
        $newPath = Config::$DONE_PATH . pathinfo($filePath, PATHINFO_BASENAME);
        rename($filePath, $newPath);
    }
    
    /**
     * Configures upload settings.
     */
    private function setSettings()
    {
        Messages::show('settingsDrive', null, false);
        $this->settings['drive'] = $this->processInput($this->readline());
        
        Messages::show('settingsYouTube', null, false);
        $this->settings['youtube'] = $this->processInput($this->readline());
        
        Messages::show('settingsMove', Color::text(Config::$DONE_PATH, Color::BLD), false);
        $this->settings['moveToDoneDir'] = $this->processInput($this->readline());
    }
    
    /**
     * Returns true if the argument is an empty string or a string consisting of 'T' or 't'.
     * @param string Input string
     * @return boolean
     */
    private function processInput($text)
    {
        if(empty($text) || strtolower($text) == 't')
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Readline alternative.
     * @return string User input string.
     */
    private function readline()
    {
        $f = fopen("php://stdin","r");
        $input = fgets($f, 1024);
        $input = rtrim($input);
        fclose($f);
        return $input;
    }
}
