<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'controllers/Base_import_controller.php');

class Import3 extends Base_import_controller
{
    protected $debug = false;

    /**
     * Read the file
     * 
     * @param   resource    $fn     A file pointer resource
     */
    protected function read_file($fn)
    {
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // File format configuration
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        $delimiter = ';';
        $enclosure = '"';
        $escape = "\\";
        
        $header_format = array(
            'unique'        => 'PersNr',
            'date'          => 'Datum',
            'time_start'    => 'K',
            'time_end'      => 'G',
            'time_deduct'   => 'Abzug'
        );
        
        $date_format = 'd.m.Y';
        $time_format = 'H:i';
        
        $this->startend->setGroup_key('unique');
        
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Reading Header
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        if (feof($fn)) {
            $msg = 'Die Datei beinhaltet weder einen Header noch Daten.';
            log_message('error', $msg);
            echo $msg."\n\n";
            die(5);
        }
        
        //Get row1
        $header_string = trim(fgets($fn));
        $header = str_getcsv($header_string, $delimiter, $enclosure, $escape);
        
        h2out('Header Format');
        $header_match = array();
        foreach ($header_format as $format_key => $format_value) {
            $found = false;
            foreach ($header as $header_index => $header_value) {
                if ($format_value===$header_value) {
                    $header_match[$format_key] = $header_index;
                    $found = $header_index;
                }
            }
            if ($found===false) {
                $msg = 'Der Datei fehlt der Header "'.$format_key.'"';
                log_message('error', $msg);
                echo $msg."\n\n";
                die(6);
            }
            echo $found.': '.$format_key.' = '.$format_value."\n";
        }
        
        $this->debug($header_match, 'Header match');
        
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Reading Data
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        h2out('Lese Daten');
        while (!feof($fn))  {
            //Get row
            $row_string = trim(fgets($fn));
            $row = str_getcsv($row_string, $delimiter, $enclosure, $escape);
            
            $this->debug($row, 'Row');
            
            if (count($header)!==count($row)) {
                $msg = 'Ungültiges Zeilenformat: Die Feldanzahl stimmt nicht mit dem Header überein';
                log_message('error', $msg.': '.print_r($row, true));
                echo "\n".$msg."\n";
                continue;
            }
            
            //Get values
            $staff = $this->value_from_row($header_match, 'staff', $row);
            $unique = $this->value_from_row($header_match, 'unique', $row);
            $date = $this->value_from_row($header_match, 'date', $row);
            $time_start = $this->value_from_row($header_match, 'time_start', $row);
            $time_end = $this->value_from_row($header_match, 'time_end', $row);
            $time_deduct = str_replace(',', '.', $this->value_from_row($header_match, 'time_deduct', $row));
            
            if ($time_start==='' || $time_end==='') {
                $msg = 'Ungültiges Zeilenformat: Zeitangaben fehlen: "'.$time_start.'" - "'.$time_end.'"';
                log_message('error', $staff.' - '.$msg);
                echo "\n".$msg."\n";
                continue;
            }
            
            $date_time_start = DateTime::createFromFormat($date_format.' '.$time_format, $date.' '.$time_start);
            $date_time_end = DateTime::createFromFormat($date_format.' '.$time_format, $date.' '.$time_end);
            
            if ($date_time_end<$date_time_start) {
                $msg = 'Ungültiges Zeilenformat: Die bis Zeit muss nach der von Zeit liegen: '.$date.' von '.$time_start;
                log_message('error', $staff.' - '.$msg);
                echo "\n".$msg."\n";
                continue;
            }

            $this->startend->add_set(
                $staff ?? '',
                $unique ?? '',
                $date_time_start,
                $date_time_end,
                abs(floatval($time_deduct))
            );
            echo 'X';
        }
        echo "\n";
    }
    

}
