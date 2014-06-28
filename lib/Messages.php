<?php

/**
 * This class keeps all messages used in program in one place.
 */
class Messages 
{
    private static $messages = array();
            
    /**
     * Initializes class with messages.
     */
    public static function init()
    {
        Messages::$messages = array(
            'titleMain'       => Color::hash().'VideoArchivist v0.1 @ Muka <muk4.muk4@gmail.com>',
            'titleUpload'     => Color::hash().'Upload pliku '.Color::BLD.'%s'.Color::END,
            'titleSettings'   => Color::hash().'Ustawienia:',
            'titleDone'        => Color::hash().'Zakończono',
            'driveUpload'     => Color::hash().'Upload pliku na Google Drive (próba %s)',
            'youtubeUpload'   => Color::hash().'Upload pliku na YouTube (próba %s)',
            'settingsDrive'   => "    [1] Czy chcesz wrzucić plik na Google Drive? [T/n]: ",
            'settingsYouTube' => "    [2] Czy chcesz wrzucić plik na YouTube? [T/n]: ",
            'settingsMove'    => "    [3] Czy chcesz przenieść ukończone pliki do folderu %s? [T/n]: ",
            'operationStatus' => "Wykonano: %d%%\r",
            'fileNotExists'   => 'Plik '.Color::BLD.'%s'.Color::END.' nie istnieje.',
            'noFiles'         => 'Brak plików do wysłania.',
            'simpleError'     => 'Błąd: %s',
            );
    }

    /**
     * Displays message with given index in console.
     * @param string Index of message to display.
     * @param array Optional arguments for the message.
     * @param boolean Set to true if you want to add new line at the end.
     */
    public static function show($index, $args = array(), $newLine = true)
    {
        $str = '';
        if($newLine)
        {
            $str = PHP_EOL;
        }
        
        vprintf(Messages::$messages[$index] . $str, $args);
    }
    
    /**
     * Returns message with given index.
     * @param string Index of message to return.
     * @param array Optional arguments for the message.
     */
    public static function get($index, $args = array())
    {
        return vsprintf(Messages::$messages[$index], $args);
    }
}

