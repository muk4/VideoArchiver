<?php

/**
 * This class handles YouTube upload.
 */
class YouTube
{
    /**
     * YouTube service object.
     */
    private $service = NULL;
    
    public function __construct()
    {
        $client = $this->createClient();
        $this->service = new Google_YouTubeService($client);
    }
    
    /**
     * Uploads file to YouTube.
     * @param string Path of file for upload.
     */
    public function upload($filePath)
    {
        $snippet = new Google_VideoSnippet();
        $snippet->setTitle(basename($filePath));

        // set video as private
        $status = new Google_VideoStatus();
        $status->privacyStatus = "private";

        $video = new Google_Video();
        $video->setSnippet($snippet);
        $video->setStatus($status);

        $chunkSize = Config::$YT_CHUNK_SIZE;
        $mimeType = $this->getMime($filePath);
        $media = new Google_MediaFileUpload($mimeType, null, true, $chunkSize);
        $media->setFileSize(filesize($filePath));

        // create insert request
        $request = $this->service->videos->insert("status,snippet", $video, array('mediaUpload' => $media));

        $handle = fopen($filePath, "rb");
        $read = 0;
        $status = false;
        
        // upload video file
        while(!$status && !feof($handle)) 
        {
            $chunk = fread($handle, $chunkSize);
            $read += strlen($chunk);
            $status = $media->nextChunk($request, $chunk);
            $progress = floor($read/ $media->size * 100);
            Messages::show('operationStatus', $progress, false);
        }
        
        // show new line because we use carriage return in operationStatus
        echo PHP_EOL;
        fclose($handle);
    }
    
    /**
     * Returns a list of all uploaded YouTube videos.
     * @param boolean True if you want to return video titles only, false returns complete video information.
     * @return array List of videos.
     */
    public function listVideos($titlesOnly = false)
    {
        $channelsResponse = $this->service->channels->listChannels('contentDetails', array('mine' => 'true'));
        $videoList = array();
        foreach ($channelsResponse['items'] as $channel) 
        {
            $playlistOptions = array('playlistId' => $channel['contentDetails']['relatedPlaylists']['uploads'], 
                                     'maxResults' => 50, 
                                     'pageToken' => NULL);
            
            do
            {
                $playlistItemsResponse = $this->service->playlistItems->listPlaylistItems('snippet', $playlistOptions);
                $playlistOptions['pageToken'] = $playlistItemsResponse['nextPageToken'];
                $videoList = array_merge($videoList, $playlistItemsResponse['items']);
                
            }while($playlistOptions['pageToken'] != NULL);
            
            if($titlesOnly)
            {
                $titleList = array();
                foreach ($videoList as $playlistItem)
                {
                    $titleList[] = $playlistItem['snippet']['title'];
                }
                return $titleList;
                
            }
            else
            {
                return $videoList;
            }
        }
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
        $client->refreshToken(''); # Youtube Token
        $client->setRedirectUri('');
        $client->setScopes(array('https://www.googleapis.com/auth/youtube'));
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
