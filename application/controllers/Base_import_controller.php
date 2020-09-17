<?php

defined('BASEPATH') OR exit('No direct script access allowed');

abstract class Base_import_controller extends CI_Controller
{
    protected $debug = false;
    
    /**
     * Run import
     */
    public function index()
    {
        h1out(' . . . . . . . . . . . . . . .   Willkommen bei '.__CLASS__.'   . . . . . . . . . . . . . . . ');
        
        $this->load->model('StartEnd_model', 'startend');
        $this->load->model('Staff_model', 'staff');
        $this->load->model('Config_ini_model', 'config_ini');
        $this->load->helper('url_post');
        
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Reading file
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        $config_filename = 'config.ini';
        h1out('Lese Konfiguration '.$config_filename);
        
        $config = $this->config_ini->get_list($config_filename);
        
        h3out('Dateiname: '.$config->filename);
        h3out('URL: '.$config->url);
        h3out('Benutzer: '.$config->user);
        h3out('Passwortlänge: '.strlen($config->pass));
        if ($config->match_addi) {
            h3out('Zuordnung über das Zusatzdatenfeld: '.$config->match);
        } else {
            h3out('Zuordnung über das Feld: '.$config->match);
        }
        
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Reading file
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        h1out('Lese Mitarbeiter Liste (REST)');
        
        $staff_list = $this->staff->get_list($config);
        
        if (empty($staff_list)) {
            $msg = 'Keine Mitarbeiter gefunden.';
            log_message('error', $msg);
            echo $msg."\n\n";
            die(2);
        }
        
        $staff_item = $staff_list[0];
        if (!property_exists($staff_item, $config->match)) {
            $msg = 'Das Feld "'.escapeshellcmd($config->match).'" existiert nicht in der Mitarbeiter Information.';
            log_message('error', $msg);
            echo $msg."\n\n";
            die(3);
        }
        
        $this->debug(array_keys(get_object_vars($staff_item)), 'Staff keys');
        
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Reading file
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        $filename = $config->filename;
        h1out('Lese Datei '.$filename);
        
        if (!($fn = fopen($filename, 'r'))) {
            $msg = 'Es war nicht möglich, die Datei "'.escapeshellcmd($filename).'" zu öffnen.';
            log_message('error', $msg);
            echo $msg."\n\n";
            die(4);
        }

        $this->read_file($fn);
        
        fclose($fn);
        
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Writing information
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        h1out('Sende via REST ');
        
        $this->startend->init_for_rest($config, $staff_list);
        
        $keys = $this->startend->get_keys();
        foreach ($keys as $key) {
            h2out($key);
            
            $dates = $this->startend->get_dates($key);
            foreach ($dates as $date) {
                h3out($date->format('d.m.Y'));
                
                $this->startend->write_for_date($key, $date);
            }
        }
        
        h1out('Fertig ');
    }
    
    /**
     * Get value from row
     * 
     * @param   array   $header_match       A header match array (field => index)
     * @param   string  $field              The field to get
     * @param   array   $row                The data array
     * @return  string  Value
     */
    protected function value_from_row(array $header_match, string $field, array $row)
    {
        if (!isset($header_match[$field])) {
            return null;
        }
        
        $index = $header_match[$field];
        
        return $row[$index] ?? null;
    }
    
    /**
     * Debug value, if debug mode active
     * 
     * @param   mixed   $value      The value to print
     * @param   string  $name       Optional name
     */
    protected function debug($value, string $name=null)
    {
        if ($this->debug) {
            echo "\n";
            echo isset($name) ? $name.': ' : '';
            print_r($value);
            echo "\n";
        }
    }


    /**
     * Read the file
     * 
     * @param   resource    $fn     A file pointer resource
     */
    abstract protected function read_file($fn);
    
}