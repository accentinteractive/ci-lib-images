<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Status: final
 * 
 * @category Accent_Interactive
 * @package Core
 * @subpackage Helpers
 * @version 3.0
 * @author Joost van Veen
 * @copyright Accent Interactive
 */


/**
 * Return or echo a nicely formatted var_dump.
 * @param mixed $var The variable to dump
 * @param string $label (Optional) Label for the dump
 * @param boolean $echo Whether to echo to screen or return as string
 * @return string The HTML string
 */
function dump ($var, $label = 'Dump', $echo = TRUE)
{
    // Store dump in variable 
    ob_start();
    var_dump($var);
    $output = ob_get_clean();
    
    // Add formatting
    $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
    $output = '<pre style="background: #FFFEEF; color: #000; border: 1px dotted #000; padding: 10px; margin: 10px 0">' . $label . ' => ' . $output . '</pre>';
    
    // Output
    if ($echo == TRUE) {
        echo $output;
    }
    else {
        return $output;
    }
}

