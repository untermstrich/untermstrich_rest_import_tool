<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Config access
 *
 * @author christiankoller
 */
class Config_ini_model extends CI_Model
{
    
    public function get_list($config_filename)
    {
        $reply = new stdClass();
        
        //Read from file
        try {
            $config = parse_ini_file($config_filename, false);
        } catch (Exception $exc) {
            $msg = 'Es war nicht möglich, die Config Datei "'.escapeshellcmd($config_filename).'" zu öffnen.';
            log_message('error', $msg.' '.$exc->getMessage());
            echo $msg."\n\n";
            die(91);
        }

        $this->_config_has_field($config, 'url');
        $this->_config_has_field($config, 'user');
        $this->_config_has_field($config, 'pass');
        $this->_config_has_field($config, 'match');
                
        $reply->url = trim($config['url'], '/ ');
        $reply->user = trim($config['user']);
        $reply->pass = trim($config['pass']);
        $reply->match = trim($config['match']);
        
        if (substr($reply->match, 0, 2)==='f_') {
            $reply->match_addi = true;
        } else {
            $reply->match_addi = false;
        }
        return $reply;
    }
    
    private function _config_has_field($config, $item)
    {
        if (!array_key_exists($item, $config)) {
            $msg = 'Der Parameter "'.escapeshellcmd($item).'" fehlt in der Config Datei.';
            log_message('error', $msg);
            echo $msg."\n\n";
            die(92);
        }
    }
    
}
