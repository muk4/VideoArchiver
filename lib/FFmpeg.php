<?php

class FFmpeg
{
    private $stdoutFile;
    private $pidFile;
    private $outputFile;
    private $tempDirPath;
    
    public function __construct()
    {
        $this->tempDirPath = Config::$TMP_PATH;
        $this->stdoutFile = $this->tempDirPath . 'ffmpeg.out';
        $this->pidFile = $this->tempDirPath . 'ffmpeg.pid';
        $this->outputFile = NULL;
    }
    
    
    public function encode($filePath)
    {
        $this->outputFile = $this->tempDirPath . pathinfo($filePath, PATHINFO_FILENAME) . '.avi';
        $this->initTemp();
        
        $cmd = sprintf("ffmpeg -y -i %s -s %s -qscale 0.1 -r 29.97 -acodec libmp3lame -ab 129k %s", $filePath, $this->getFinalResolution($filePath), $this->outputFile);
        exec(sprintf("%s > %s 2>&1 & echo $! > %s", $cmd, $this->stdoutFile, $this->pidFile));
        usleep(200);
        
        $pid = file_get_contents($this->pidFile);
        if (empty($pid))
            throw new Exception('Empty FFmpeg pid file.');
            
        sleep(1);
        while($this->isRunning($pid))
        {
            sleep(1);
            $arr = explode("\r", file_get_contents($this->stdoutFile));
            $lastLine = trim($arr[count($arr)-2]);
            echo "\rL: ".$lastLine;
        }
        
        echo PHP_EOL;
        return $this->outputFile;
    }
    
    private function getFinalResolution($inputFileName)
    {
        $result = shell_exec(sprintf('ffprobe -show_streams -i %s 2>/dev/null', $inputFileName));
        $result = explode(PHP_EOL, $result);
        
        $width = 0;
        $height = 0;
        
        foreach($result as $line)
        {
            if(strpos($line, 'width') === 0)
                $width = array_pop(explode('=', $line));
            if(strpos($line, 'height') === 0)
                $height = array_pop(explode('=', $line)); 
        }
        
        if ($height >= 720)
            return $width . 'x' . $height;
        
        $width = 720 * $width / $height;
        $width = (int)$width;
        $width+=(10-($width%10)); # zaokraglanie do 10 w gore
        return $width . 'x' . 720;
    }
    
    private function isRunning($pid)
    {
        try
        {
            $result = shell_exec(sprintf("ps %d", $pid));
            if( count(preg_split("/\n/", $result)) > 2)
            {
                return true;
            }
        }
        catch(Exception $e)
        {
            return false;    
        }
    }
    
    private function initTemp()
    {
        if(!file_exists($this->tempDirPath))
        {
            mkdir($this->tempDirPath);
        }
        else if(!is_dir($this->tempDirPath))
        {
            throw new UploaderException(Messages::get('ffmpegTempDirInvalid'));
        }
    }
    
    public function cleanup()
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tempDirPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) 
        {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            echo $fileinfo->getRealPath();
            //$todo($fileinfo->getRealPath());
        }

        rmdir($this->tempDirPath);
    }
}

//try
//{
//    if(count($argv)<2)
//        throw new Exception('Not enough parameters.');
//        
//    echo "\033[92m[#]\033[0m Enkodowanie..." . PHP_EOL;
//    $ffmpeg = new FFmpeg();
//    $ffmpeg->encode($argv[1]);
//}
//catch(Exception $e)
//{
//    echo 'FFmpegAPI error...' . PHP_EOL;
//    exit(1);
//}
?>