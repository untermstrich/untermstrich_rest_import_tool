<?php

class StartEnd_model extends CI_Model {
    
    private $list = array();
    private $staff_ids = false;
    private $config = false;

    protected $group_key = 'staff_name';

    /**
     * Set group key
     * 
     * @param   string  $group_key      Either staff_name or unique
     */
    function setGroup_key($group_key)
    {
        $this->group_key = $group_key;
    }

    /**
     * Add a new item - With date, time and coming status
     * 
     * @param   string  $staff_name     Staff name
     * @param   string  $unique         Unique
     * @param   object  $date_time      DateTime
     * @param   boolean $coming         Coming (Else going)
     */
    public function add(string $staff_name, string $unique, DateTime $date_time, bool $coming)
    {
        $key = ${$this->group_key};
        
        //Ensure we have lists for all keys and save keys
        if (!array_key_exists($key, $this->list)) {
            $this->list[$key] = array();
        }
        
        $this->list[$key][$date_time->format('Y-m-d H:i:s')] = $coming;
    }
    
    /**
     * Add a new item - With date, time start, time end, and a deduct value
     * 
     * @param   string  $staff_name         Staff name
     * @param   string  $unique             Unique
     * @param   object  $date_time_start    DateTime
     * @param   object  $date_time_end      DateTime
     * @param   float   $time_deduct        Deduct in hours
     */
    public function add_set(string $staff_name, string $unique, DateTime $date_time_start, DateTime $date_time_end, float $time_deduct)
    {
        if ($date_time_end<$date_time_start) {
            $msg = 'Die End Zeit darf nicht vor er von Zeit liegen.';
            log_message('error', $msg);
            echo $msg."\n\n";
            return;
        }
        
        if ($time_deduct<=0.001) {
            $this->add($staff_name, $unique, $date_time_start, true);
            $this->add($staff_name, $unique, $date_time_end, false);
        } else {
            //Calculate breaks
            $date_time_break_start = clone $date_time_start;
            $date_time_break_start->modify('+1 Minutes');

            $date_time_break_end = clone $date_time_break_start;
            $date_time_break_end->modify('+'.($time_deduct*60).' Minutes');
            
            if ($date_time_end<$date_time_break_end) {
                $msg = 'Der Abzug darf nicht größer sein, als die gesamten Stunden des Tages.';
                log_message('error', $msg);
                echo $msg."\n\n";
                return;
            }
            
            $this->add($staff_name, $unique, $date_time_start, true);
            $this->add($staff_name, $unique, $date_time_break_start, false);
            $this->add($staff_name, $unique, $date_time_break_end, true);
            $this->add($staff_name, $unique, $date_time_end, false);
        }
    }
    
    /**
     * Get keys
     * 
     * @return  array
     */
    public function get_keys()
    {
        return array_keys($this->list);
    }
    
    /**
     * Get dates for key
     * 
     * @param   string      $key        Key
     * @return  array
     */
    public function get_dates(string $key)
    {
        $dates = array();
        foreach ($this->list[$key] as $date_time => $coming) {
            $date_time_obj = new DateTime($date_time);
            $date_time_obj->setTime(0, 0, 0);
            if (!in_array($date_time_obj, $dates)) {
                array_push($dates, $date_time_obj);
            }
        }
        
        //Sort the array
        sort($dates);
        
        return $dates;
    }
    
    /**
     * Get for date
     * 
     * @param   string      $key        Key
     * @param   object      $date       DateTime
     * @return  array
     */
    public function get_for_date(string $key, DateTime $date)
    {
        $date_str = $date->format('Y-m-d');
        
        $times = array();
        foreach ($this->list[$key] as $date_time => $coming) {
            $date_time_obj = new DateTime($date_time);
            
            //Does the date match
            if ($date_time_obj->format('Y-m-d')!==$date_str) {
                continue;
            }
            
            $time = $date_time_obj->format('H:i:s');
            if (!array_key_exists($time, $times)) {
                $times[$time] = $coming;
            }
        }
        
        //Sort the array
        ksort($times);
        
        return $times;
    }
    
    /**
     * Get for date
     * 
     * @param   string      $key        Key
     * @param   object      $date       DateTime
     * @return  array
     */
    public function get_date_info(string $key, DateTime $date)
    {
        $times = $this->get_for_date($key, $date);
        
        $first_time_str = array_key_first($times);
        $last_time_str = array_key_last($times);
        
        //None
        if ($first_time_str===null && $last_time_str===null) {
            return false;
        }
        
        //Validate first and last time
        if ($times[$first_time_str]===false) {
            $first_time_str = '00:00:00';
        }
        if ($times[$last_time_str]===true) {
            $today = new DateTime('today');
            if ($date>=$today) {
                $now = new DateTime('now');
                $first_time_str = $now->format('H:i:s');
            } else {
                $first_time_str = '23:59:59';
            }
        }
        
        //Create time deduct for the day
        $time_deduct = 0;
        $last_was_gooing = true;
        $last_gooing_time = false;
        foreach ($times as $time_str => $coming) {
            $time = DateTime::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d').' '.$time_str);
            
            if ($coming) {
                echo ' - EIN '.$time->format('H:i:s');
                if ($last_was_gooing && $last_gooing_time!==false) {
                    $time_deduct_add = ($time->getTimestamp() - $last_gooing_time->getTimestamp());
                    $time_deduct += $time_deduct_add;
                    echo ' - Pause '.gmdate("H:i:s", $time_deduct_add);
                }
                $last_was_gooing = false;
            } else {
                echo ' - AUS '.$time->format('H:i:s');
                $last_gooing_time = $time;
                $last_was_gooing = true;
            }
            echo "\n";
        }
        
        return array(
            'start_time'    => $first_time_str, 
            'end_time'      => $last_time_str, 
            'time_deduct'   => $time_deduct/3600
        );
    }
    
    /**
     * Init before rest calls
     * 
     * @param   object  $config         The config
     * @param   array   $staff_list     The staff list
     */
    public function init_for_rest(stdClass $config, array $staff_list)
    {
        //Library
        $this->load->helper('url_post');
        
        $staffs = $this->get_keys();
        
        $this->staff_ids = array();
        foreach ($staffs as $staff) {
            foreach ($staff_list as $staff_item) {
                if (trim($staff_item->{$config->match})===trim($staff)) {
                    $this->staff_ids[trim($staff)] = $staff_item->id;
                }
            }
        }
        
        $this->config = $config;
    }
    
    /**
     * Write for date
     * 
     * @param   string      $key            Key
     * @param   object      $date           DateTime
     * @return  success
     */
    public function write_for_date(string $key, DateTime $date)
    {
        if ($this->staff_ids===false || $this->config===false) {
            $msg = 'Rufen Sie write_for_date nicht auf, ohne zuerst init_for_rest aufzurufen.';
            log_message('error', $msg);
            echo $msg."\n\n";
            die(71);
        }
        
        //Get staff ID
        if (!isset($this->staff_ids[$key])) {
            $msg = 'Kann "'.escapeshellcmd($key).'" unter den MitarbeiterInnen nicht finden.';
            log_message('error', $msg);
            echo $msg."\n\n";
            return false;
        }
        $staff_id = $this->staff_ids[$key];
        
        //Get info for date
        $date_info = $this->get_date_info($key, $date);
        
        //Create url
        $url = $this->config->url.'/rest/startend/staff/'.intval($staff_id).'/'.$date->format('Y/m/d');
        
        //Send info
        $reply = url_req(
            'POST', 
            $url,
            http_build_query($date_info),
            'ACCEPT:application/vnd.php.serialized',
            $this->config->user,
            $this->config->pass
        );
        
        if ($reply===false) {
            $msg = 'Es war nicht möglich, die Beginn/Ende Zeiten zu schreiben "'.escapeshellcmd($url).'" = '.str_replace(PHP_EOL, '', print_r($date_info, true));
            log_message('error', $msg);
            echo $msg."\n\n";
            return false;
        }
        
        //Decode
        $data = unserialize($reply);
        
        //If empty reply
        if (empty($data) || !isset($data['url'])) {
            $msg = 'Fehlerhafte Antwort beim Beginn/Ende Zeiten Schreiben "'.escapeshellcmd($url).'" = '.str_replace(PHP_EOL, '', print_r($date_info, true));
            log_message('error', $msg);
            echo $msg."\n\n";
            return false;
        }
        
        log_message('info', 'Send '.$url.' = '.str_replace(PHP_EOL, '', print_r($date_info, true)).' | Reply = '.$data['url']);
        
        return true;
    }

}