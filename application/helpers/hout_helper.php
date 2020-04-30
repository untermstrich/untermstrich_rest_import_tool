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

if ( ! function_exists('h1out')) {
    /**
     * Post a string to an url
     *
     * @param   string  $title      Message
     */
    function h1out($title)
    {
        $CI = get_instance();
        echo $CI->load->view('h1_message', array(
            'title' => $title
        ), true);
    }
}

if ( ! function_exists('h2out')) {
    /**
     * Post a string to an url
     *
     * @param   string  $title      Message
     */
    function h2out($title)
    {
        $CI = get_instance();
        echo $CI->load->view('h2_message', array(
            'title' => $title
        ), true);
    }
}

if ( ! function_exists('h3out')) {
    /**
     * Post a string to an url
     *
     * @param   string  $title      Message
     */
    function h3out($title)
    {
        $CI = get_instance();
        echo $CI->load->view('h3_message', array(
            'title' => $title
        ), true);
    }
}