<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Example extends CI_Controller
{

    public function __construct ()
    {
        parent::__construct();
        $this->load->helper('general');
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
        $data['filename'] = 'rooney.jpg';
        $data['newFileName'] = 'resized.jpg';
        $data['enlargeOnResize'] = FALSE;
        $sourcefile = realpath(FCPATH . 'gfx') . DIRECTORY_SEPARATOR . $data['filename'];
        
        $images = $this->_get_images_config();
        $this->load->library('images');
        foreach ($images as $image) {
            
            $destinationfile = FCPATH . 'gfx/' . $image['prefix'] . $data['newFileName'];
            
            if (isset($image['crop']) && $image['crop'] == TRUE) {
                $this->images->squareThumb($sourcefile, $destinationfile, $image['width'], $data['enlargeOnResize']);
            }
            else {
                $this->images->resize($sourcefile, $destinationfile, $image['width'], $image['height'], $data['enlargeOnResize']);
            }
        }
        
        $data['images'] = $images;
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
        $destinationfile = FCPATH . 'gfx/' . $data['newFileName'];
        
        $this->load->library('images');
        $this->images->resize($sourcefile, $destinationfile, $data['newWidth'], $data['newHeight'], $data['enlargeOnResize']);
        
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
        $destinationfile = FCPATH . 'gfx/' . $data['newFileName'];
        
        $this->load->library('images');
        $this->images->squareThumb($sourcefile, $destinationfile, $data['newWidth'], $data['enlargeOnResize']);
        
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
        $images[$i]['width'] = 400;
        $images[$i]['height'] = 400;
        $images[$i]['crop'] = FALSE;
        $images[$i]['prefix'] = '400_';
        
        // Resize & crop; square from the center
        $i ++;
        $images[$i]['width'] = 300;
        $images[$i]['height'] = 300;
        $images[$i]['crop'] = TRUE;
        $images[$i]['prefix'] = '300_square_';
        
        // Resize & crop; square from the center
        $i ++;
        $images[$i]['width'] = 120;
        $images[$i]['height'] = 120;
        $images[$i]['crop'] = TRUE;
        $images[$i]['prefix'] = '120_square_';
        
        // Resize as thumb & preserve aspect ratio
        $i ++;
        $images[$i]['width'] = 90;
        $images[$i]['height'] = 90;
        $images[$i]['crop'] = FALSE;
        $images[$i]['prefix'] = '90_preserved_';
        
        return $images;
    }

}