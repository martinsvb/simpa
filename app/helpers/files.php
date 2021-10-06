<?

namespace app\helpers;

use app\helpers\storage;
use app\exception\excepFiles;

/**
 *  File management class
 *
 *  @property $_actFile, Data storage instance
 *  @property $_ds, Data storage instance
 *  @property $_excep, Exception handler instance
 *  @property $_file, Processed file
 *  @property $_images, List of allowed images extensions
 *  @property $openingMode, List of files opening modes
 */
class files
{
    private
    $_file,
    $_actFile,
    $_images = [
        "png",
        "jpg",
        "jpeg",
        "gif"
    ];

    public $openingMode = [

        /**
         *  Open for reading only;
         *  place the file pointer at the beginning of the file.
         */
        'readOnly' => 'r',
        
        /**
         *  Open for reading and writing;
         *  place the file pointer at the beginning of the file.
         */
        'readAndWrite' => 'r+',

        /**
         *  Open for writing only;
         *  place the file pointer at the beginning of the file and truncate the file to zero length.
         *  If the file does not exist, attempt to create it.
         */
        'writeOnly' => 'w',

        /**
         *  Open for reading and writing;
         *  otherwise it has the same behavior as 'w'.
         */
        'writeAndRead' => 'w+',

        /**
         *  Open for writing only;
         *  place the file pointer at the end of the file. If the file does not exist, attempt to create it.
         *  In this mode, fseek() has no effect, writes are always appended.
         */
        'addToEnd' => 'a',

        /**
         *  Open for reading and writing;
         *  place the file pointer at the end of the file. If the file does not exist, attempt to create it.
         *  In this mode, fseek() only affects the reading position, writes are always appended.
         */
        'addToEndWithRead' => 'a+',

        /**
         *  Create and open for writing only;
         *  place the file pointer at the beginning of the file.
         *  If the file already exists, the fopen() call will fail by returning false and generating an error
         *  of level E_WARNING. If the file does not exist, attempt to create it.
         *  This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying open(2) system call.
         */
        'createForWriteOnly' => 'x',

        /**
         *  Create and open for reading and writing;
         *  otherwise it has the same behavior as 'x'.
         */
        'createForReadAndWrite' => 'x+',

        /**
         *  Open the file for writing only.
         *  If the file does not exist, it is created.
         *  If it exists, it is neither truncated (as opposed to 'w'), nor the call to this function fails
         *  (as is the case with 'x'). The file pointer is positioned on the beginning of the file.
         *  This may be useful if it's desired to get an advisory lock (see flock()) before attempting
         *  to modify the file, as using 'w' could truncate the file before the lock was obtained
         *  (if truncation is desired, ftruncate() can be used after the lock is requested).
         */
        'createOrEditForWriteOnly' => 'c',

        /**
         *  Open the file for reading and writing;
         *  otherwise it has the same behavior as 'c'.
         */
        'createOrEditForReadAndWrite' => 'c+',

        /**
         *  Set close-on-exec flag on the opened file descriptor.
         *  Only available in PHP compiled on POSIX.1-2008 conform systems.
         */
        'closeOnExec' => 'e'
    ];

    public function __construct(private $ds, private $excep) {}
    
    /**
     *  Create file
     */
    public function openFile($file, $mode)
    {
        $this->_file = "$file";

        if (in_array($mode, $this->openingMode)) {
            $this->_actFile = fopen($this->_file, $mode);
        }
        else {
            throw new excepFiles("Ivalid file's opening mode: $mode set.");
        }
        
        return $this;
    }
    
    /**
     *  Write data to file
     */
    public function write($data, $jsn = NULL)
    {
        if ($jsn) {
            $data = json_encode($data);
        }
        fwrite($this->_actFile, $data);
        fclose($this->_actFile);
    }
    
    /**
     *  Read data from file
     */
    public function read()
    {
        $ext = pathinfo($this->_file, PATHINFO_EXTENSION);

        $data = null;

        if ($ext=="ini") {
            $data = parse_ini_file($this->_file, true);
        }
        elseif (in_array($ext, $this->_images)) {
            $data = "picture";
        }
        else if ($fileSize = filesize($this->_file)) {
            $data = fread($this->_actFile, $fileSize);
        }

        fclose($this->_actFile);
        
        return $data;
    }
    
    /**
     *  Retrieve file's extension
     */
    public function getFileExtension(string $file): string
    {
        $fileNameParts = explode('.', $file);
        return array_pop($fileNameParts);
    }
    
    /**
     *  Retrieve file's extension equal to desired value
     */
    public function isExtension(string $file, array $ext): bool
    {
        return in_array($this->getFileExtension($file), $ext);
    }

    /**
     *  Check if file is image
     */
    public function isImage(string $file): bool
    {
        return in_array($this->getFileExtension($file), $this->_images);
    }
    
    /**
     *  Upload file
     *
     *  @param string $upDir, Name of files upload directory
     *
     *  @return array $upFiles, Array of full uploaded files names
     */
    public function upload($upDir)
    {
        try {
            $upFiles = [];
            
            $count = 0;
            foreach ($_FILES as $file) {
                $upFileName = $this->ds->time['timeStamp'] ."-". basename($file['name']);
                $upFile = $upDir . $upFileName;
                
                if (move_uploaded_file($file['tmp_name'],  $upFile)) {
                    $upFiles[$count]['fileName'] = preg_replace(
                        "#".$this->ds->docRoot."#",
                        $this->ds->web,
                        $upFile
                    );
                    $upFiles[$count]['name'] = $upFileName;
                    $count++;
                }
                else {
                    throw new excepFiles("Files upload failed");
                }
            }
        } catch (excepFiles $e) {
            $this->_excep->handle($e);
        }
        
        return $upFiles;
    }
    
    /**
     *  Delete file
     */
    public function del($delFile)
    {
        $delFileName = preg_replace("#".$this->ds->web."#", $this->ds->docRoot, $delFile);
        
        try {
            if (file_exists($delFileName)) {
                unlink($delFileName);
            }
            else {
                throw new excepFiles("Deleting file: $delFile doesn't exists");
            }
        } catch (excepFiles $e) {
            $this->_excep->handle($e);
        }
        
        $result = ['deletedFile' => $delFile];
        
        return $result;
    }
}
