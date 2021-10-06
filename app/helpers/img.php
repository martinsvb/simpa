<?php

namespace app\helpers;

use app\exception\excepFiles;

/**
 *  Application images operations helper
 *  
 *  @param string $ds, Data storage instance
 *  @param string $excep, Exception handler instance
 */
class img
{
    public function __construct($ds, $excep)
    {
        $this->_ds = $ds;
        $this->_excep = $excep;
    }
    
    /**
     *  resize images
     *
     *  @param string $file, image full filename
     *  @param array $options, newHeightThumb - create thumbmail image with set height, newHeight, newWidth
     *
     *  @return array $files, processed files info
     */
    public function resize($file, $options)
    {
        $files = [];

        if (file_exists($file)) {
            try {
                $info = getimagesize($file);
                list($width, $height) = $info;
                
                $fileSize = $width * $height * $info['bits'];
                if ($fileSize > $this->_ds->memLimit) {
                    throw new excepFiles("Memory limit is exhausted, try to allocate: $fileSize");
                }
                
                $saveFunc = preg_replace("#\/#", "", $info['mime']);
                $ext = mb_substr(strstr($info['mime'], '/'), 1);
                $createFunc = "imagecreatefrom" . $ext;
                if ($ext == "jpeg") {
                    $ext = "jpg";
                }
                
                $files['result']['fileName'] = $file;
                
                if ($options['newHeightThumb']) {
                    $thumbName = mb_substr($file, 0, mb_strrpos($file, ".")) . "-thumb.$ext";
                    if (copy($file, $thumbName)) {
                        $files['result']['thumbName'] = $thumbName;
                        $files['process'][$thumbName]['newWidth'] = ($width / $height) * $options['newHeightThumb'];
                        $files['process'][$thumbName]['newHeight'] = $options['newHeightThumb'];
                    }
                }
                
                if ($options['newHeight']) {
                    $files['process'][$file]['newWidth'] = ($width / $height) * $options['newHeight'];
                    $files['process'][$file]['newHeight'] = $options['newHeight'];
                }
                
                if ($options['newWidth']) {
                    $files['process'][$file]['newWidth'] = $options['newWidth'];
                    $files['process'][$file]['newHeight'] = ($height / $width) * $options['newWidth'];
                }
                
                foreach ($files['process'] as $rf => $rfOpt) {
                    $img = $createFunc($rf);
                    $tmp = imagecreatetruecolor($rfOpt['newWidth'], $rfOpt['newHeight']);
                    imagecopyresampled($tmp, $img, 0, 0, 0, 0, $rfOpt['newWidth'], $rfOpt['newHeight'], $width, $height);
                    
                    unlink($rf);
                    $files['process'][$rf]['result'] = $saveFunc($tmp, $rf);
                }
            } catch (excepFiles $e) {
                $this->_excep->handle($e);
            }
        }
        
        return $files['result'];
    }
}
