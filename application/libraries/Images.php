<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Status: final
 * 
 * @category Accent_Interactive
 * @version 2.0
 * @author Joost van Veen
 * @copyright Accent Interactive
 */

/**
 * Handles common image manipulation using the CI image_lib class.
 * @package Core
 * @subpackage Libraries
 */
class Images
{

    /**
     * The CI super object
     * @var object
     */
    private $_ci;

    /**
     * Default image library. I highly recommend changing this to imagemagick, 
     * because its performance is *way* better than GD2.
     * 
     * If you set $config['image_library'] in a config file, it will override 
     * the default value for this variable.
     * @var string
     */
    public $image_library = 'GD2';

    /**
     * Path for deault image library. 
     * If you use image magick on Linux, this is probabaly /usr/bin/
     * If you use XAMPP this is probably sth like c:\xampp\imagemagick-6.2.8-q16
     * Defaults to null, for GD2
     * 
     * If you set $config['library_path'] in a config file, it will override 
     * the default value for this variable.
     * @var mixed
     */
    public $library_path = null;

    public function __construct ()
    {
        // Instantiate the CI libraries so we can work with them
        $this->_ci = & get_instance();
        
        // Load image library if necessary
        if (! isset($this->_ci->image_lib)) {
            $this->_ci->load->library('image_lib');
        }
        
        // Set image library and path from settings in configuration file.
        // If you did not set these settings in a config file the settings remain default.
        config_item('image_library') === FALSE || $this->image_library = config_item('image_library');
        config_item('library_path') === FALSE || $this->library_path = config_item('library_path');
    }

    /**
     * Calls $this->square and $this->resize. The squared image is always a copy 
     * of the original, since it is a thumbnail.
     * 
     * @param string $originalFile Full path and filename of original image
     * @param string $newFile The full destination path and filename
     * @param integer $newSize (optional) The new size of the squared image, defaults to 120
     * @param boolean $enlarge (optional) Whether to enlarge if img is too smnall, defaults to FALSE
     * @param integer $offset (optional) Offset for either x or y axis, defaults to 0
     * @return void
     * @author Joost van Veen
     */
    function squareThumb ($originalFile, $newFile, $newSize = 120, $enlarge = FALSE, $offset = 0)
    {
        $this->square($originalFile, $newFile, $offset);
        $this->resize($newFile, $newFile, $newSize, $newSize, $enlarge);
    }

    /**
     * 
     * @param $originalFile Full path and filename of original image
     * @param $newWidth (optional) The width of the resized image
     * @param $newHeight (optional) The height of the resized image
     * @param $newFile (optional) The full destination path and filename
     * @param $enlarge (optional) Whether a smaller original should be enlarged
     * @return mixed FALSE if no action was performed, else void
     * @author Joost van Veen
     */
    function resize ($originalFile, $newFile = '', $newWidth = 120, $newHeight = 120, $enlarge = FALSE)
    {
        // Abort if image does not exist
        if (! file_exists($originalFile) || ! is_file($originalFile)) {
            return FALSE;
        }
        
        // If we should not enlarge we need to check the size of the original image
        if ($enlarge == FALSE) {
            
            // Do not resize if the image is already smaller than $newWidth and $newHeight
            $imgData = $this->getSize($originalFile);
            if ($imgData['width'] <= $newWidth && $imgData['height'] <= $newHeight) {
                if ($newFile == '') {
                    
                    return FALSE;
                }
                else {
                    // Delete existing file, if the destination file is not the
                    // original file (otherwise we'd delete our source file, too)
                    if (file_exists($newFile) && is_file($newFile) && $newFile != $originalFile) {
                        unlink($newFile);
                    }
                    
                    // Copy the image to the new path
                    copy($originalFile, $newFile);
                    return FALSE;
                }
            }
        }
        
        // Configure CI image_lib
        $config['image_library'] = $this->image_library;
        $config['library_path'] = $this->library_path;
        $config['maintain_ratio'] = TRUE;
        $config['width'] = $newWidth;
        $config['height'] = $newHeight;
        $config['source_image'] = $originalFile;
        if ($newFile) {
            $config['new_image'] = $newFile;
        }
        $this->_ci->image_lib->initialize($config);
        
        // Resize the image
        if (! $this->_ci->image_lib->resize()) {
            show_error($this->_ci->image_lib->display_errors());
        }
        
        // Clear lib so we can perform another image action
        $this->_ci->image_lib->clear();
    }

    /**
     * Crop an image so that it becomes square. I fyou need a square thumbnail, 
     * run this method first and resize afterwards.
     * 
     * By default the original image is cropped and overwritten, but you can 
     * supply a destination path if you wish to retain the original image and 
     * create a new, cropped version of that image somewhere else.  
     * 
     * By default the image is cropped from the center, but you can supply an 
     * optional offset parameter.
     * 
     * @param string $originalFile The full path and filename of the image to be cropped
     * @param string $newFile (optional) The full destination path and filename
     * @param integer $offset (optional) Offset for either x or y axis
     * @return mixed FALSE if no action was performed, else void
     * @author Joost van Veen
     */
    function square ($originalFile, $newFile = '', $offset = 0)
    {
        
        // Abort if image does not exist
        if (! file_exists($originalFile) || ! is_file($originalFile)) {
            return FALSE;
        }
        
        // Get original image data
        $imgData = $this->getSize($originalFile);
        
        // Set image lib config
        // Best cropping results on all three main image formats (png, gif, jpg)
        // are with GD2 
        $config['image_library'] = 'GD2';
        $config['library_path'] = null;
        $config['source_image'] = $originalFile;
        $config['maintain_ratio'] = FALSE;
        if ($newFile) {
            $config['new_image'] = $newFile;
        }

        // Crop only if image is not square yet
        if ($imgData['width'] != $imgData['height']) {
            
            // Set x and y axis for cropping. If x and y axis have not been 
            // passed as parameter we will crop from the center of the image.
            if ($imgData['width'] > $imgData['height']) { // Landscape, crop left & right
                $config['width'] = $imgData['height'];
                $config['height'] = $imgData['height'];
                $config['x_axis'] = $offset > 0 ? $offset : ($imgData['width'] - $config['width']) / 2;
            }
            else { // Portrait, crop top & bottom
                $config['width'] = $imgData['width'];
                $config['height'] = $imgData['width'];
                $config['y_axis'] = $offset > 0 ? $offset : ($imgData['height'] - $config['height']) / 2;
            }
        }
        else {
            // Image is already square. No cropping required.
            if ($newFile == '') {
                return FALSE;
            }
            else {
                // We need to copy the image to the new path
                delete_file($newFile);
                copy($originalFile, $newFile);
                return FALSE;
            }
        }
        
        // Crop image
        $this->_ci->image_lib->initialize($config);
        if (! $this->_ci->image_lib->crop()) {
            show_error($this->_ci->image_lib->display_errors());
        }
        
        // Clear lib so we can perform another image action
        $this->_ci->image_lib->clear();
    }

    /**
     * Return an array that contains size and mime of an image. Uses getimagesize().
     * 
     * Return array indexes:
     * 'width'
     * 'height'
     * 'mime'
     * @param string $image Full path to image
     * @return array that contains size and mime of an image
     * @author Joost van Veen
     */
    public function getSize ($image)
    {
        $imgData = getimagesize($image);
        $retval['width'] = $imgData[0];
        $retval['height'] = $imgData[1];
        $retval['mime'] = $imgData['mime'];
        return $retval;
    }

    /**
     * Delete all instances of an image if they exist.
     * @param string $image Full path and filename
     * @param array $destination_file An array of image sizes and corrensponding prefixes 
     * @author Joost van Veen
     */
    public function delete ($image, $filepath, $image_sizes)
    {
        if ($image == '') {
            return FALSE;
        }
        
        $names = array();
        foreach ($image_sizes as $version) {
            $names[] = $version['prefix'] . $image;
        }
        delete_file($names, $filepath);
    }

    /**
     * Upload and resize an image
     * @param string $destination_file The name of teh desitination file, without extension
     * @param string $source_file The name of the upload field
     * @param string $destination_file The name of the file[ath to upload to 
     * @param array $destination_file An array of image sizes and corrensponding prefixes 
     * @param string $old_image The name of anoldimage tha shold e deleted before uploading the new image.
     * @return array $upload_data 
     * @author Joost van Veen
     */
    function upload_and_resize ($destination_file, $source_file, $filepath, $image_sizes, $old_image = '')
    {
        // Delete old image if we have one
        if ($old_image != '') {
            $this->delete($old_image, $filepath, $image_sizes);
        }
        
        // Upload image
        if (! isset($this->_ci->upload)) {
            $this->_ci->load->library('upload');
        }
        $upload_data = $this->upload_image($filepath, $source_file);
        $sourcefile = $upload_data['full_path'];
        
        // Resize and/or crop copies of image
        foreach ($image_sizes as $row) {
            // Set filename, using prefix
            $destinationfile = $upload_data['file_path'] . $row['prefix'] . $destination_file . $upload_data['file_ext'];
            
            // Resize image
            if (isset($row['crop']) && $row['crop'] == TRUE) {
                $this->squareThumb($sourcefile, $destinationfile, $row['width'], config_item('image_enlarge_on_resize'));
            }
            else {
                $this->resize($sourcefile, $destinationfile, $row['width'], $row['height'], config_item('image_enlarge_on_resize'));
            }
        }
        
        if (file_exists($sourcefile) && is_file($sourcefile)) {
            unlink($sourcefile);
        }
        
        return $upload_data;
    }

    /**
     * Upload an image, taking into account some given config settings, like max 
     * filesize and allowed file types. 
     * If uplaod is not succesful, throw a big, fat CI error and do not return 
     * anything.
     * @param string $uploadpath
     * @param string $image_field
     * @return array CI's upload data array
     * @author Joost van Veen
     */
    function upload_image ($uploadpath, $image_field)
    {
        // Set config
        $config['upload_path'] = $uploadpath;
        $config['overwrite'] = TRUE;
        $config['allowed_types'] = config_item('img_types');
        $config['max_size'] = config_item('max_size_images');
        
        // Load image library if necessary
        if (! isset($this->_ci->upload)) {
            $this->_ci->load->library('upload');
        }
        
        // Upload the file
        $this->_ci->upload->initialize($config);
        
        // Collect data
        if (! $this->_ci->upload->do_upload($image_field)) {
            $msg = upload_errors($image_field, $config);
            log_message('error', 'Unsuccesful image upload: ' . $msg);
            show_error($msg);
        }
        else {
            return $this->_ci->upload->data();
        }
    }
}