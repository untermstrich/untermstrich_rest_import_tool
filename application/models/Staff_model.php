<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Staff access
 *
 * @author christiankoller
 */
class Staff_model extends CI_Model
{
    
    public function get_list(stdClass $config)
    {
        //Library
        $this->load->helper('url_post');
        
        $url = $config->url.'/rest/staff/all';
        $reply = url_req(
            'GET', 
            $url,
            '',
            'ACCEPT:application/vnd.php.serialized',
            $config->user,
            $config->pass
        );
        
        if ($reply===false) {
            $msg = 'Es war nicht möglich, die Mitarbeiter von "'.escapeshellcmd($url).'" einzulesen.';
            log_message('error', $msg);
            echo $msg."\n\n";
            die(81);
        }
        
        //Decode
        $data = @unserialize($reply);
        if ($data===false) {
            $msg = 'Es war nicht möglich, die Mitarbeiter von "'.escapeshellcmd($url).'" einzulesen. Server Rückmeldung: '. htmlspecialchars($reply);
            log_message('error', $msg);
            echo $msg."\n\n";
            die(82);
        }

        //If empty reply
        if (empty($data)) {
            return array();
        }
        
        return $data;
    }
    
}
