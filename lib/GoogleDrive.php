<?php

/**
 * This class handles Google Drive upload.
 */
class GoogleDrive
{
    /**
     * @var Google_DriveService Google Drive API service.
     */
    private $service = NULL;
    
    public function __construct()
    {
        $client = $this->createClient();
        $this->service = new Google_DriveService($client);
    }
    
    /**
     * Upload file to Google Drive
     * @param string Path of file to be uploaded.
     * @throws Exception
     */
    public function upload($filePath)
    {
        if (!file_exists($filePath))
            throw new UploaderException(Messages::get('fileNotExists'));
        
        $chunkSize = Config::$DR_CHUNK_SIZE;
	$mimeType = $this->getMime($filePath);
        
        $file = new Google_DriveFile();
        $file->setTitle(basename($filePath));
        $file->setMimeType($mimeType);

        $media = new Google_MediaFileUpload($mimeType, null, true, $chunkSize);
        $media->setFileSize(filesize($filePath));

        // create insert request
        $requestOptions = array('mimeType' => $mimeType, 'mediaUpload' => $media);
        $request = $this->service->files->insert($file, $requestOptions);

        $handle = fopen($filePath, "rb");
        $status = false;
        $read = 0;
        
        while(!$status && !feof($handle))
        {
            $chunk = fread($handle, $chunkSize);
            $read += strlen($chunk);
            $status = $media->nextChunk($request, $chunk);
            $progress = floor($read / $media->size * 100);
            Messages::show('operationStatus', $progress, false);
        }
        
        // show new line because we use carriage return in operationStatus
        echo PHP_EOL;
        fclose($handle);
    }
    
    /**
     * Return list of uploaded files.
     * @return array
     */
    public function listFiles()
    {
        $result = $this->service->files->listFiles(array('q'=>'trashed = false'));
        return $result['items'];
    }
    
    /**
     * Creates Google API client.
     * @return Google_Client Google client object.
     */
    private function createClient()
    {
        $client = new Google_Client();
        $client->setClientId('');
        $client->setClientSecret('');
        $client->refreshToken(''); # Drive Token
        $client->setRedirectUri('');
        $client->setScopes(array('https://www.googleapis.com/auth/drive'));
        $client->setAccessType('offline');
        
        return $client;
    }
    
    /**
     * Returns mime type for given file. If extension is not present in 
     * Config class then the default mime type is used.
     * @param string File path.
     * @return string Mime type as string.
     */
    private function getMime($filePath)
    {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        
        if(array_key_exists($ext, Config::$MIME_TYPES))
        {
            return Config::$MIME_TYPES[$ext];
        }
        else
        {
            return Config::$DEFAULT_MIME_TYPE;
        }
    }
}

?>
