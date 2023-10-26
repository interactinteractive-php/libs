<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Image Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Image
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Image
 */
  
class Image
{
    public static $FileName;
    public static $FileSize;
    public static $FileType;
    public static $newWidth = 100;
    public static $newHeight = 100;
    public static $TmpName;
    public static $PicDir;
    private static $MaxFileSize = 26214400;
    private static $ImageQuality = 85;
    private static $ImageQualityPng = 3;

    /**
     * Image::Image()
     *
     * @param mixed $FileName
     * @return
     */
    public function __construct($FileName)
    {
        self::$FileName = $FileName;
    }

    /**
     * Image::ValidateExtension()
     *
     * @param mixed $FileName
     * @return
     */
    public static function ValidateExtension($FileName)
    {
        $extension = strtolower(substr($FileName, strrpos($FileName, '.') + 1));
        
        if (in_array($extension, explode(',', Config::getFromCache('CONFIG_IMG_EXT')))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Image::ExistFile()
     *
     * @return
     */
    public static function ExistFile()
    {
        $fileexist = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']) . '/' . self::$PicDir . self::$FileName;
        if (file_exists($fileexist)) {
            return true;
        }
    }

    /**
     * Image::GetError()
     *
     * @param mixed $error
     * @return
     */
    public static function GetError($error)
    {
        switch ($error) {
            case 0:
                // $msg = "Error: Invalid file type <strong>".self::$FileType."</strong>! Allowed type: .jpg, .jpeg, .gif, .png, .bmp <strong>".self::$FileName."</strong><br />";
                $msg = "Файлын төрөл тохирохгүй байгаа тул та зурагаа солино уу <br />";
                Message::add('e', $msg, 'back');
                break;

            case 1:
                $msg = "Error: File <strong>".self::$FileSize."</strong> is too large!<br />";
                Message::add('e', $msg, 'back');
                break;

            case 2:
                $msg = "Error: Please, select a file for uploading!<br>";
                Message::add('e', $msg, 'back');
                break;

            case 3:
                $msg = "Error: File <strong>".self::$FileName."</strong> already exist!<br />";
                Message::add('e', $msg, 'back');
                break;
        }
    }

    /**
     * Image::Resize()
     *
     * @return
     */
    public static function Resize($method)
    {
        if (empty(self::$TmpName)) {
            echo self::GetError(2);
        } elseif (self::$FileSize > self::$MaxFileSize) {
            echo self::GetError(1);
        } elseif (self::ValidateExtension(self::$FileName) == false) {
            echo self::GetError(0);
        } elseif (self::ExistFile()) {
            echo self::GetError(3);
        } else {
            
            if (FileUpload::$fileExtension) {
                $ext = FileUpload::$fileExtension;
            } else {
                $fileNameExplode = explode('.', self::$FileName);
                $ext = strtolower(end($fileNameExplode));
            }
            
            if ($ext == 'jpeg' || $ext == 'jpg' || $ext == 'tiff') {
                $exif = exif_read_data(self::$TmpName);
            } else {
                $exif['Orientation'] = null;
            }

            if (empty($exif['Orientation']) || (isset($exif['Orientation']) && $exif['Orientation'] == '1')) {
                list($width_orig, $height_orig) = getimagesize(self::$TmpName);
            } else {
                list($height_orig, $width_orig) = getimagesize(self::$TmpName);
            }
            
            $ratio_orig = $width_orig / $height_orig;
            
            if ($method == 1) {
                
                if (self::$newWidth && !self::$newHeight) {
                    //self::$newHeight = floor($height_orig * (self::$newWidth / $width_orig));
                    self::$newHeight = floor(($height_orig / $width_orig) * self::$newWidth);
                } elseif (self::$newHeight && !self::$newWidth) {
                    //self::$newWidth = floor($width_orig * (self::$newHeight / $height_orig));
                    self::$newWidth = floor(($width_orig / $height_orig) * self::$newHeight);
                }
                
            } else {
                if ((self::$newWidth / self::$newHeight) > $ratio_orig) {
                    self::$newWidth = self::$newHeight * $ratio_orig;
                } else {
                    self::$newHeight = self::$newWidth / $ratio_orig;
                }
            }
            
            switch ($ext) {
                case 'jpg':
                case 'jpeg':    
                    $source = imagecreatefromjpeg(self::$TmpName);
                    break;
                case 'gif':
                    $source = imagecreatefromgif(self::$TmpName);
                    break;
                case 'bmp':
                    $source = imagecreatefrombmp(self::$TmpName);
                    break;    
                case 'png':
                    $source = imagecreatefrompng(self::$TmpName);
                    break;
            }
            
            if (($ext == 'jpeg' || $ext == 'jpg' || $ext == 'tiff') && !empty($exif['Orientation'])) {

                switch ($exif['Orientation']) {
                    case 8:
                        $source = imagerotate($source, 90, 0);
                        break;
                    case 3:
                        $source = imagerotate($source, 180, 0);
                        break;
                    case 6:
                        $source = imagerotate($source, -90, 0);
                        break;
                }
            }
            
            $normal = imagecreatetruecolor(self::$newWidth, self::$newHeight);
            imagealphablending($normal, false);
            imagesavealpha($normal, true);
            
            $white = imagecolorallocate($normal, 255, 255, 255);
            imagefill($normal, 0, 0, $white);

            if ($method == 1) {
                $origin_x = 0;
                $origin_y = 0;

                $src_x = $src_y = 0;
                $src_w = $width_orig;
                $src_h = $height_orig;

                $cmp_x = $width_orig / self::$newWidth;
                $cmp_y = $height_orig / self::$newHeight;

                if ($cmp_x > $cmp_y) {
                    $src_w = round($width_orig / $cmp_x * $cmp_y);
                    $src_x = round(($width_orig - ($width_orig / $cmp_x * $cmp_y)) / 2);
                } elseif ($cmp_y > $cmp_x) {
                    $src_h = round($height_orig / $cmp_y * $cmp_x);
                    $src_y = round(($height_orig - ($height_orig / $cmp_y * $cmp_x)) / 2);
                }

                imagecopyresampled($normal, $source, $origin_x, $origin_y, $src_x, $src_y, self::$newWidth, self::$newHeight, $src_w, $src_h);
            } else {
                imagecopyresampled($normal, $source, 0, 0, 0, 0, self::$newWidth, self::$newHeight, $width_orig, $height_orig);
            }
            
            imagecolortransparent($normal, $white);
            
            switch ($ext) {
                case 'jpg':
                case 'jpeg':    
                    imagejpeg($normal, self::$PicDir.'/'.self::$FileName, self::$ImageQuality);
                    break;
                case 'gif':
                    imagegif($normal, self::$PicDir.'/'.self::$FileName, self::$ImageQuality);
                    break;
                case 'bmp':
                    imagebmp($normal, self::$PicDir.'/'.self::$FileName);
                    break;    
                case 'png':
                    imagepng($normal, self::$PicDir.'/'.self::$FileName, self::$ImageQualityPng);
                    break;
            }

            imagedestroy($source);
        }
    }

    /**
     * Image::Save()
     *
     * @return
     */
    public static function Save()
    {
        if (empty(self::$TmpName)) {
            echo self::GetError(2);
        } elseif (self::$FileSize > self::$MaxFileSize) {
            echo self::GetError(1);
        } elseif (self::ValidateExtension(self::$FileName) == false) {
            echo self::GetError(0);
        } elseif (self::ExistFile()) {
            echo self::GetError(3);
        }

        else {
            copy(self::$TmpName, self::$PicDir . self::$FileName);
        }
    }
}