<?php
include 'lib/Config.php';
spl_autoload_register('Config::autoload');

// Initialize class because PHP doesn't allow string concatenation in properties.
Messages::init();

Messages::show('titleMain');

try
{
    $uploader = new Uploader();

    if ($argc == 1)
    {
        $uploader->uploadAll();
    }
    else
    {
        $uploader->upload($argv[1]);
    }
}
catch(UploaderException $e)
{
    Messages::show('simpleError', $e->getMessage());
}

Messages::show('titleDone');
?>
