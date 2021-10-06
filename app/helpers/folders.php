<?

namespace app\helpers;

use DirectoryIterator;
use app\exception\excepfiles;

/**
 *  Folders management class
 *
 *  @property $_ds, Data storage instance
 *  @property $allowedPermissions, Allowed permissions for creating folders
 */
class folders
{
    public
    $allowedPermissions = [
        'Owner_read_write_Others_nothing' => 0600,
        'Owner_read_write_Others_read' => 0644,
        'Owner_all_Owner_s_groups_read_execute' => 0750,
        'Owner_all_Others_read_execute' => 0755,
    ];
    
    public function __construct(private $ds) {}

    /**
     *  Create folder in set location
     */
    public function createFolder(string $folder, $permissions, bool $recursive)
    {
        $folderCreated = false;
        $folderExists = is_dir($folder);
        try {
            if (!$folderExists) {
                mkdir($folder, $permissions, $recursive);
                $folderCreated = true;
            }
        } catch (\Exception $e) {
            throw new excepfiles("Folder creating error", $e->getcode(), $e);
        }
        
        return [
            $folderCreated,
            $folderExists,
        ];
    }

    /**
     *  Read content (folders and files) of folder
     */
    public function readFolder(string $folder)
    {
        $cFo = $cFi = 1;
        $folders = [];
        $files = [];
        try {
            $foldersIterator = new DirectoryIterator($this->ds->documents."/$folder");
            foreach ($foldersIterator as $fileinfo) {
                // Before directory (../)
                if ($fileinfo->isDot() && mb_strlen($fileinfo->getBasename()) == 2) {
                    
                    // Root directory
                    if ($fileinfo->getPath()==$this->ds->documents) $folders[0] = "Root";
                    
                    // Subdirectory
                    else {
                        $folders[$cFo]['name'] = "<-";
                        $folders[$cFo]['path'] = $folders[0] = preg_replace(
                            "#".$this->ds->documents."/#",
                            NULL,
                            $fileinfo->getPath()
                        );
                        // First subdirectory (doesn't have /) or next subdirectories
                        $folders[$cFo]['path'] = mb_strpos($folders[$cFo]['path'] ,"/")
                            ? preg_replace("#/([^/]+)$#", NULL, $folders[$cFo]['path'])
                            : NULL;
                        $folders[$cFo]['count'] = iterator_count(
                            new DirectoryIterator($this->ds->documents."/".$folders[$cFo]['path'])
                        ) - 2;
                        $cFo++;
                    }
                }
                
                elseif ($fileinfo->isDir() && !$fileinfo->isDot()) {
                    $folders[$cFo]['name'] = $fileinfo->getBasename();
                    $folders[$cFo]['path'] = preg_replace(
                        "#".$this->ds->documents."/#",
                        NULL,
                        $fileinfo->getPathName()
                    );
                    $folders[$cFo]['count'] = iterator_count(
                        new DirectoryIterator($this->ds->documents."/".$folders[$cFo]['path'])
                    ) - 2;
                    $cFo++;
                }
                
                elseif ($fileinfo->isFile()) {
                    $files[$cFi]['name'] = $fileinfo->getFilename();
                    $files[$cFi]['path'] = preg_replace(
                        "#".$this->ds->documents."/#",
                        NULL,
                        $fileinfo->getPath()
                    );
                    $files[$cFi]['ext'] = pathinfo($files[$cFi]['name'], PATHINFO_EXTENSION);
                    // $files[$cFi]['ext'] = $fileinfo->getExtension();
                    $files[$cFi]['size'] = round($fileinfo->getSize() / 1024, 2);
                    $files[$cFi]['size'] = $files[$cFi]['size'] < 1000
                        ? $files[$cFi]['size']."kB"
                        : mb_substr($files[$cFi]['size'], 0, -6) . "," . mb_substr(
                            round("0.".mb_substr($files[$cFi]['size'], -6), 1), 2
                        ) . " MB";
                    $files[$cFi]['date_cr'] =  date('d. m. Y h:m', $fileinfo->getMTime());
                    $files[$cFi]['date_mod'] =  date('d. m. Y h:m', $fileinfo->getCTime());
                    $cFi++;
                }
            }
        } catch (\Exception $e) {
            throw new excepfiles("Folder reading error", $e->getcode(), $e);
        }
        
        return [
            'folders' => sort($folders),
            'files' => sort($files)
        ];
    }

    /**
     *  Read content (folders and files) of folder
     */
    public function readFullFolder(string $folder)
    {
        $folders = [];
        $files = [];
        try {
            $foldersIterator = new DirectoryIterator($folder);
            foreach ($foldersIterator as $fileinfo) {
                if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                    $folders[] = $fileinfo->getBasename();
                }
                if ($fileinfo->isFile()) {
                    $files[] = $fileinfo->getFilename();
                }
            }
        } catch (\Exception $e) {
            throw new excepfiles("Folder reading error", $e->getcode(), $e);
        }
        
        return [
            'folders' => $folders,
            'files' => $files
        ];
    }

    /**
     *  Pack folder into .zip file
     */
    public function zipFolder(string $folder, string $zipfileName, array | null $delItems)
    {
        $zip = new \ZipArchive();
        $zip->open("$folder/$zipfileName.zip", \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $delFolders = [];
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder),
            \RecursiveIteratorIterator::LEAVES_ONLY,
        );

        foreach ($files as $name => $file) {
            $relativePath = substr($file->getPathname(), strlen($folder));
            if (!$file->isDir()) {
                $zip->addFile($file->getRealPath(), $relativePath);
            }

            if ($delItems && count($delItems) && in_array($relativePath, $delItems)) {
                $delFolders[] = $file;
            }
        }

        $zip->close();

        if (count($delFolders)) {
            $this->delFolders($delFolders);
        }
    }

    /**
     *  Delete content of folders recursively
     */
    public function delFolders(array | \RecursiveIteratorIterator $folders)
    {
        foreach ($folders as $delFile) {
            if ($delFile->isDir()) {
                $dirContent = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $delFile->getRealPath(),
                        \RecursiveDirectoryIterator::SKIP_DOTS
                    ),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                if (iterator_count($dirContent)) {
                    $this->delFolders($dirContent);
                }
                rmdir($delFile->getRealPath());
            }
            if ($delFile->isFile()) {
                unlink($delFile->getRealPath());
            }
        }
    }
}
