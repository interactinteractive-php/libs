<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Upload Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Upload
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Upload
 */
   
class Upload
{
    public static $FileName;
    public static $NewName;
    public static $ThumbPrefix;
    public static $File;
    public static $NewWidth = 600;
    public static $NewHeight = 600;
    public static $TWidth = 170;
    public static $THeight = 170;
    public static $SavePath;
    public static $ThumbPath;
    public static $OverWrite;
    public static $NameCase;
    public static $method = 1;
    public static $randName;
    public static $CheckOnlyWidth = false;

    private static $Image;
    private static $width;
    private static $height;
    private static $Error;

    /**
     * Upload::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        self::$FileName = 'imagename.jpg';
        self::$OverWrite = true;
        self::$NameCase = '';
        self::$Error = '';
        self::$NewName = '';
        self::$ThumbPrefix = '';
        self::$randName = '';
    }

    /**
     * Upload::UploadFile()
     * 
     * @return
     */
    public static function UploadFile()
    {
        if (is_array(self::$File['name'])) {
            self::_ArrayUpload();
        } else {
            self::_NormalUpload();
        }
        return self::$Error;
    }

    /**
     * Upload::_ArrayUpload()
     * 
     * @return
     */
    public static function _ArrayUpload()
    {
        for ($i = 0; $i < count(self::$File['name']); $i++) {

            if (!empty(self::$File['name'][$i]) and self::_FileExist(self::$NewName[$i], self::$File['name'][$i]) == false) {
                
                self::_UploadImage(self::$File['name'][$i], self::$File['tmp_name'][$i], self::$File['size'][$i], self::$File['type'][$i], self::$NewName[$i]);

                if (!empty(self::$ThumbPath)) {
                    self::_ThumbUpload(self::$File['name'][$i], self::$File['tmp_name'][$i], self::$File['size'][$i], self::$File['type'][$i], self::$ThumbPrefix.self::$NewName[$i]);
                }
            }
        }
    }

    /**
     * Upload::_NormalUpload()
     * 
     * @return
     */
    public static function _NormalUpload()
    {  
        $_FileName = self::$File['name'];
        $_NewName = self::$NewName;

        if (!empty(self::$File['name']) && self::_FileExist($_NewName, $_FileName) == false) {
            self::_UploadImage(self::$File['name'], self::$File['tmp_name'], self::$File['size'], self::$File['type'], self::$NewName);

            if (!empty(self::$ThumbPath)) {
                self::_ThumbUpload(self::$File['name'], self::$File['tmp_name'], self::$File['size'], self::$File['type'], self::$ThumbPrefix.self::$NewName);
            }
        }
    }

    /**
     * Upload::_UploadImage()
     * 
     * @param mixed $FileName
     * @param mixed $TmpName
     * @param mixed $Size
     * @param mixed $Type
     * @param mixed $NewName
     * @return
     */
    public static function _UploadImage($FileName, $TmpName, $Size, $Type, $NewName)
    {   
        
        Image::$FileName = $FileName;
        Image::$PicDir = self::$SavePath;
        Image::$TmpName = $TmpName;
        Image::$FileSize = $Size;
        Image::$FileType = $Type;
        
        FileUpload::$fileExtension = null;
        
        if (!FileUpload::checkContentType($FileName, $TmpName)) {
            
            Image::GetError(0); exit;
        }
        
        if (FileUpload::$fileExtension) {
            $ext = FileUpload::$fileExtension;
        } else {
            $fileNameExplode = explode('.', $FileName);
            $ext = strtolower(end($fileNameExplode));
        }
        
        if ($ext == 'jpeg' || $ext == 'jpg' || $ext == 'tiff') {
            $exif = exif_read_data($TmpName);
        } else {
            $exif['Orientation'] = null;
        }
        
        if (empty($exif['Orientation'])) {
            list($width, $height) = getimagesize($TmpName);
        } else {
            list($height, $width) = getimagesize($TmpName);
        }

        Image::$FileName = self::_CheckName($NewName, $FileName);
        
        if (self::$CheckOnlyWidth) {
            
            if ($width <= self::$NewWidth) {
                Image::Save();
            } else {
                
                self::$NewHeight = floor(($height / $width) * self::$NewWidth);
                
                Image::$newWidth = self::$NewWidth;
                Image::$newHeight = self::$NewHeight;
        
                Image::Resize(self::$method);
            }
            
        } else {
            /* if ($width < self::$NewWidth && $height < self::$NewHeight) { 
                Image::Save();
            } else { */
                
                self::$NewHeight = floor(($height / $width) * self::$NewWidth);
                
                Image::$newWidth = self::$NewWidth;
                Image::$newHeight = self::$NewHeight;
                
                Image::Resize(self::$method);
            /* } */
        }
    }

    /**
     * Upload::_ThumbUpload()
     * 
     * @param mixed $FileName
     * @param mixed $TmpName
     * @param mixed $Size
     * @param mixed $Type
     * @param mixed $NewName
     * @return
     */
    public static function _ThumbUpload($FileName, $TmpName, $Size, $Type, $NewName)
    {
        list($width, $height) = getimagesize($TmpName);

        Image::$FileName = $FileName;
        Image::$newWidth = self::$TWidth;
        Image::$newHeight = self::$THeight;
        Image::$PicDir = self::$ThumbPath;
        Image::$TmpName = $TmpName;
        Image::$FileSize = $Size;
        Image::$FileType = $Type;

        Image::$FileName = self::_CheckName($NewName, $FileName);

        if ($width < self::$TWidth and $height < self::$THeight) {
            Image::Save();
        } else {
            Image::Resize(self::$method);
        }
    }

    /**
     * Upload::_CheckName()
     * 
     * @param mixed $NewName
     * @param mixed $UpFile
     * @return
     */
    public static function _CheckName($NewName, $UpFile)
    {
        if (empty($NewName)) {
            return self::_ChangeCase($UpFile);
        } else {
            $Ext = explode(".", $UpFile);
            $Ext = end($Ext);
            $Ext = strtolower($Ext);

            $NewName = self::_ChangeCase($NewName . "." . $Ext);
            return $NewName;
        }
    }

    /**
     * Upload::_ChangeCase()
     * 
     * @param mixed $FileName
     * @return
     */
    public static function _ChangeCase($FileName)
    {
        if (self::$NameCase == 'lower') {
            return strtolower($FileName);
        } elseif (self::$NameCase == 'upper') {
            return strtoupper($FileName);
        } else {
            return $FileName;
        }
    }

    /**
     * Upload::_FileExist()
     * 
     * @param mixed $_NewName
     * @param mixed $_FileName
     * @return
     */
    public static function _FileExist($_NewName, $_FileName)
    {
        if (self::$OverWrite == true) {
            if (file_exists(self::$SavePath . self::_CheckName($_NewName, $_FileName))) {
                if (!unlink(self::$SavePath . self::_CheckName($_NewName, $_FileName))) {
                    self::$Error[] = "File: " .self::_CheckName($_NewName, $_FileName) . " Cannot verwrite.";
                } else {
                    if (file_exists(self::$ThumbPath . self::_CheckName($_NewName, $_FileName))) {
                        unlink(self::$ThumbPath . self::_CheckName($_NewName, $_FileName));
                    }
                }
            }
        } else {
            if (file_exists(self::_CheckName($_NewName, $_FileName))) {
                self::$Error[] = "File: " . self::_CheckName($_NewName, $_FileName) . " aready exist";
                return true;
            }
        }
    }
    
}