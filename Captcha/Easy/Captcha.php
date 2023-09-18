<?php
/**
 * Captcha Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Captcha
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Captcha
 */

class Captcha {

    // Number of characters
    public $chars_number = 4;

    // Letters (1), Numbers (2), Letters & Numbers (3)
    public $string_type = 3;

    // Font Range
    public $font_size = 14;

    // Border Color (optional)
    public $border_color = '76, 76, 76';

    // Path to TrueType Font
    public $tt_font = 'arial.ttf';
    
    public $session_prefix = null;

    /* Show Captcha Image */
    public function show_image($width = 88, $height = 31)
    {
        if (isset($this->tt_font)) {
            if (!file_exists($this->tt_font)) exit('The path to the true type font is incorrect.');
        }

        if ($this->chars_number < 3) exit('The captcha code must have at least 3 characters');

        $string = $this->generate_string();

        $im = imagecreate($width, $height);

        /* Set a White & Transparent Background Color */
        $bg = imagecolorallocatealpha($im, 255, 255, 255, 127); // (PHP 4 >= 4.3.2, PHP 5)
        imagefill($im, 0, 0, $bg);

        /* Border Color */

        if ($this->border_color) {
            list($red, $green, $blue) = explode(',', $this->border_color);

            $border = imagecolorallocate($im, $red, $green, $blue);
            imagerectangle($im, 0, 0, $width - 1, $height - 1, $border);
        }
        
        $line_color = imagecolorallocate($im, 100, 100, 100); 
        for ($i = 0; $i < 10; $i++) {
            imageline($im, mt_rand(0,$width), mt_rand(0,$width), mt_rand(0,$width), mt_rand(0,$width), $line_color);
        }
        
        $pixel_color = imagecolorallocate($im, 150, 150, 150);
        for ($i = 0; $i < 500; $i++) {
            imagesetpixel($im, rand()%200, rand()%50, $pixel_color);
        }  

        $textcolor = imagecolorallocate($im, 0, 0, 0);

        $y = 24;

        for ($i = 0; $i < $this->chars_number; $i++) {
            $char = $string[$i];

            $factor = 17;
            $x = ($factor * ($i + 1)) - 8;
            $angle = rand(1, 15);

            $font = rand(10, 18);

            imagettftext($im, $font, $angle, $x, $y, $textcolor, $this->tt_font, $char);
        }
        
        $_SESSION[SESSION_PREFIX . $this->session_prefix . 'security_code'] = md5(sha1($string));

        /* Output the verification image */
        header("Content-type: image/png");
        imagepng($im);
        imagedestroy($im);
    }

    private function generate_string()
    {
        if ($this->string_type == 1) { // letters
            
            $array = range('A', 'Z');
            
        } else if($this->string_type == 2) { // numbers
            
            $array = range(1, 9);
            
        } else { // letters & numbers
            
            $x = ceil($this->chars_number / 2);

            $array_one = array_rand(array_flip(range('A', 'Z')), $x);

            if ($x <= 2) $x = $x - 1;

            $array_two = array_rand(array_flip(range(1, 9)), $this->chars_number - $x);

            $array = array_merge($array_one, $array_two);
        }

        $rand_keys = array_rand($array, $this->chars_number);

        $string = '';

        foreach ($rand_keys as $key) {
            $string .= $array[$key];
        }

        return $string;
    }     

}