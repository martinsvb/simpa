<?php

namespace app\helpers;

use app\exception\excep;
use app\exception\excepFiles;

/**
 *  Application CSV files operations helper
 *
 *  @property $_excep, Exception handler
 */
class csv
{
    private
    $_excep;

    public function __construct()
    {
        $this->_excep = new excep();
    }
    
    /**
     *  Check if proper csv file path is set
     */
    private function _checkFile(string $file)
    {
        try {
            if (!preg_match('/\.csv/', $file)) {
                throw new excepFiles("Inserted file isn't proper csv.");
            }
        } catch (excepFiles $e) {
            $this->_excep->handle($e);
        }
    }
    
    /**
     *  Add data to the end of selected CSV file
     *
     *  @param string $file, Filename with folder relative to document root
     *  @param array $data
     */
    public function addData(string $file, array $data)
    {
        $this->_checkFile($file);
        $csvFile = new \SplFileObject($file, 'a');
        $csvFile->fputcsv($data);
        $csvFile = null;
    }
    
    /**
     *  Add array of data to the end of selected CSV file
     *
     *  @param string $file, Filename with folder relative to document root
     *  @param array(array) $data, Multiline data array
     */
    public function addMultilineData(string $file, array $data)
    {
        $this->_checkFile($file);
        $csvFile = new \SplFileObject($file, 'a');
        foreach ($data as $dataItem) {
            $csvFile->fputcsv(is_array($dataItem) ? $dataItem : [$dataItem]);
        }
        $csvFile = null;
    }
    
    /**
     *  Read csv data into an array
     */
    public function readCsvData(string $file): array
    {
        $this->_checkFile($file);
        $csvData = [];
        $csvFile = new \SplFileObject($file);
        $csvFile->setFlags(\SplFileObject::READ_CSV);
        foreach ($csvFile as $rowData) {
            if (reset($rowData)) {
                $csvData[] = $rowData;
            }
        }
        $csvFile = null;
        
        return $csvData;
    }
    
    /**
     *  Read csv data into an array indexed by first csv's data row
     */
    public function readCsvDataWithHeader(string $file): array
    {
        $csvData = $this->readCsvData($file);
        $csvHeaderData = array_shift($csvData);
        foreach ($csvData as $rowIndex => $rowData) {
            foreach ($csvHeaderData as $rowItemIndex => $headerItem) {
                $csvData[$rowIndex][$headerItem] = $rowData[$rowItemIndex];
                unset($csvData[$rowIndex][$rowItemIndex]);
            }
        }
        
        return $csvData;
    }
}
