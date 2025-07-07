<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter helper
 *
 * PHP version 5
 *
 * @category CodeIgniter
 * @author Christian Koller
 * @copyright 2011 untermStrich
 * @package helpers
 * @version $Id$
 */

if ( ! function_exists('url_req')) {
    /**
     * Post a string to an url
     *
     * @param   string  $mode       Either POST or GET
     * @param   string  $url        Url to get
     * @param   string  $data       Data to post
     * @param   string  $header     Optional Add header
     * @param   string  $user       Optional Basic auth user
     * @param   string  $pass       Optional Basic auth password
     * @return  object
     */
    function url_req($mode, $url, $data, $header=false, $user=false, $pass=false)
    {
        $mode = strtoupper($mode);
        
        //Parse url
        $parsedUrl = parse_url($url);
        echo "\033[32m" . json_encode($parsedUrl, JSON_PRETTY_PRINT) . "\033[0m\n";

        //Get host
        $host = $parsedUrl['host'];

        //Get path
        if (isset($parsedUrl['path'])) {
            $path = $parsedUrl['path'];
        } else {
            $path = '/';
        }

        //Get port
        if (isset($parsedUrl['port'])) {
            $port = $parsedUrl['port'];
        } else {
            $port = '80';
        }
        
        //SSL mode
        $fsockhost = $host;
        if (isset($parsedUrl['scheme'])) {
            if ($parsedUrl['scheme']==='https') {
                $fsockhost = 'ssl://'.$host;
            }
        } elseif ($port>400) {
            $fsockhost = 'ssl://'.$host;
        }

        //Connect to target file
        $file = @fsockopen($fsockhost, $port, $errno, $errstr, 8);
        /*#*/log_message('error', 'fsockopen '.$host.':'.$port);
        if (!$file) {
            log_message('error', __METHOD__.' failed: Cannot fsockopen '.$host.':'.$port);
            return false;
        }

        //Send header
        $header1 = $mode.' '.$path;
        /*#*/log_message('error', 'header 1: '.$header1);
        if (!@fputs($file, $header1." HTTP/1.1\r\n")) {
            log_message('error', __METHOD__.' failed: Cannot fsockopen '.$mode.' header '.$host.':'.$port);
            return false;
        }
        
        //Send header - auth
        if ($user!=false && $pass!=false) {
            $auth = base64_encode($user.":".$pass);
            /*#*/log_message('error', 'auth '.$auth);
            @fputs($file, "Authorization: Basic ".$auth."\r\n");
        }

        @fputs($file, "Host: $host\r\n");
        @fputs($file, "Content-type: application/x-www-form-urlencoded\r\n");
        if ($mode==='POST') {
            @fputs($file, 'Content-Length: '.strlen($data)."\r\n");
        }
        if ($header!==false) {
            @fputs($file, $header."\r\n");
        }
        @fputs($file, "Connection: close\r\n\r\n");
        if ($mode==='POST') {
            if (!@fputs($file, $data)) {
                log_message('error', __METHOD__.' failed: Cannot fsockopen '.$mode.' data '.$host.':'.$port);
                return false;
            }
        }

        //Read lines - download
        $reply = '';
        $already_length = 0; $last_length=0;
        while ($line = @fgets($file, 1024)) {
            $already_length += strlen($line);

            //On error
            if (false===$line) {
                log_message('error', __METHOD__.' failed: Cannot read (@fgets) '.$url);
                return false;
            }

            $reply .= $line;
        }

        //Close source file
        @fclose($file);
        
        //Chunked?
        $chunked = (strpos(strtolower($reply), "transfer-encoding: chunked")!==false);
        
        //Remove header
        $pos = strpos($reply, "\r\n\r\n");
        $reply = substr($reply, $pos + 4);
        
        if ($chunked) {
            return http_chunked_decode($reply);
        }
        return $reply;
    }
}

if ( ! function_exists('http_chunked_decode')) {
    /**
     * Decode chunked encoded data if PHP module HTTP is missing
     *
     * @param type $encoded
     * @return type 
     */
    function http_chunked_decode($encoded)
    {
        $len = strlen($encoded);
        $pos = 0;
        $reply = "";
        while ($pos < $len) {
            //Get length
            $rawnum = substr($encoded, $pos, strpos(substr($encoded, $pos), "\r\n") + 2);
            $pos += strlen($rawnum);
            $length_chunk = hexdec(trim($rawnum));
            
            //Get chunk
            $chunk = substr($encoded, $pos, $length_chunk);
            $reply .= $chunk;
            $pos += strlen($chunk);
        }
        return $reply;
    }
}

/* End of file url_get_contents_helper.php */
