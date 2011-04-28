<?php 
foreach ($images as $image) {
    echo '<div style="border: 1px solid #ccc; padding: 20px; margin: 10px; width: 720px;">';
    echo '<h1>'.$image['prefix'] . $newFileName.'</h1>';
    echo '<img src="../../gfx/'.$image['prefix'] . $newFileName.'" alt="'.$image['prefix'] . $newFileName.'" />';
    echo '</div>';
}