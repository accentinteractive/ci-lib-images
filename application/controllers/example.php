<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Example extends CI_Controller
{

    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * Display the original image
     * @return void
     * @author Joost van Veen
     */
    public function index ()
    {
        $this->load->view('example');
    }

    /**
     * Resize original image into multiple images and display them
     * @return void
     * @author Joost van Veen
     */
    public function resize_multiple ()
    {
        $filename = 'rooney.jpg';
        $newFileName = 'resized.jpg';
        
        $sourcefile = realpath(FCPATH . 'gfx') . DIRECTORY_SEPARATOR . $filename;
        $enlargeOnResize = FALSE;
        
        $images = $this->_get_images_config();
        $this->load->library('images');
        foreach ($images as $image) {
            
            $destinationfile = $image['prefix'] . $newFileName;
            
            if (isset($image['crop']) && $image['crop'] == TRUE) {
                $this->images->squareThumb($sourcefile, $destinationfile, $image['width'], $enlargeOnResize);
            }
            else {
                $this->images->resize($sourcefile, $destinationfile, $image['width'], $image['height'], $enlargeOnResize);
            }
        }
        
        $data['images'] = $images;
        $data['newFile'] = $newFileName;
        $this->load->view('example_resized', $data);
    }

    /**
     * Resize original image into a smaller version, with the same aspect ratio, and display it.
     * @return void
     * @author Joost van Veen
     */
    public function resize_single ()
    {
        $data['filename'] = 'rooney.jpg';
        $data['newFileName'] = 'resized_single.jpg';
        $data['newWidth'] = 180;
        $data['newHeight'] = 180;
        $data['enlargeOnResize'] = FALSE;
        $sourcefile = realpath(FCPATH . 'gfx') . DIRECTORY_SEPARATOR . $data['filename'];
        
        $this->load->library('images');
        $this->images->resize($sourcefile, $data['newFileName'], $data['newWidth'], $data['newHeight'], $data['enlargeOnResize']);
        
        $this->load->view('example_resize_single', $data);
    }

    /**
     * Resize original image into a smaller, square version, and display it.
     * @return void
     * @author Joost van Veen
     */
    public function resize_single_square ()
    {
        $data['filename'] = 'rooney.jpg';
        $data['newFileName'] = 'resized_single_square.jpg';
        $data['newWidth'] = 200;
        $data['newHeight'] = 200;
        $data['enlargeOnResize'] = FALSE;
        $sourcefile = realpath(FCPATH . 'gfx') . DIRECTORY_SEPARATOR . $data['filename'];
        
        $this->load->library('images');
        $this->images->squareThumb($sourcefile, $data['newFileName'], $data['newWidth'], $data['enlargeOnResize']);
        
        $this->load->view('example_resize_single', $data);
    }

    /**
     * Return a config array for nmultiple image sizes and cropping settings
     * @return array - The config array
     * @author Joost van Veen
     */
    private function _get_images_config ()
    {
        // Resize & preserve aspect ratio
        $i = 0;
        $images[$i]['width'] = 300;
        $images[$i]['height'] = 300;
        $images[$i]['crop'] = FALSE;
        $images[$i]['prefix'] = '300_';
        
        // Resize & crop; square from the center
        $i ++;
        $images[$i]['width'] = 120;
        $images[$i]['height'] = 120;
        $images[$i]['crop'] = TRUE;
        $images[$i]['prefix'] = '120_square_';
        
        // Resize as thumb & preserve aspect ratio
        $i ++;
        $images[$i]['width'] = 120;
        $images[$i]['height'] = 120;
        $images[$i]['crop'] = FALSE;
        $images[$i]['prefix'] = '120_preserved_';
        
        return $images;
    }

}