<?

namespace app\base;

use app\dbConnection\db;
use app\response\resp;
use app\helpers\storage;
use app\exception\excep;
use app\helpers\{csv, folders, files, img};
use app\mail\mailSender;

/**
 * Base application controller
 *
 * Parent for application modules, provide them application features
 * Never override this controller properties anywhere in application!
 *
 * @property $db, Database connection
 * @property $resp, Response object
 * @property $ds, Data storage instance
 * @property $excep, Exception handler
 * @property $csv, Application CSV helper
 * @property $folders, Application folders helper
 * @property $files, Application files helper
 * @property $img, Application img helper
 * @property $mail, Application E-mails sender
 */
class controller
{
    protected
    $db,
    $resp,
    $ds,
    $excep,
    $csv,
    $folders,
    $files,
    $img,
    $mail;
    
    /**
     *  Load application settings
     *  Instantiate current dbConnection connection
     *  Initialize response object
     */
    public function __construct()
    {
        $this->ds = storage::getInstance();

        [ 'dbConnection' => $dbConnection ] = parse_ini_file ($this->ds->apiSettings . "settings.ini", true);

        $this->db = new db(
            $dbConnection['host'],
            $dbConnection['user'],
            $dbConnection['password'],
            $dbConnection['database']
        );
        
        $this->resp = new resp();
        
        $this->excep = new excep();
        
        $this->csv = new csv();
        
        $this->folders = new folders($this->ds);
        
        $this->files = new files($this->ds, $this->excep);
        
        $this->img = new img($this->ds, $this->excep);
        
        $this->mail = new mailSender($this->ds, $this->db);
    }
}
