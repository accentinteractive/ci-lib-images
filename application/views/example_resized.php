<?php 
$this->load->helper('directory');
$files = directory_map('./gfx/');

foreach ($files as $file) {
    echo '<div style="border: 1px solid #ccc; padding: 20px; margin: 10px; width: 720px;">';
    echo '<h1>'.$file.'</h1>';
    echo '<img src="../../gfx/'.$file.'" alt="'.$file.'" />';
    echo '</div>';
}