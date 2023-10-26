<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');
/** 
 * FileUpload Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	FileUpload
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/FileUpload
 */

class FileUpload {
    
    public static $FileName;
    public static $TempFileName;
    public static $UploadDirectory;
    public static $ValidExtensions;
    public static $Message;
    public static $MaximumFileSize;
    public static $IsImage;
    public static $MaximumWidth;
    public static $MaximumHeight;
    public static $fileExtension = null;
    public static $mimes = array();
    public static $uploadedFiles = array();
    public static $uploadedRealPathFiles = array();
    
    public static function ValidateExtension()
    {
        $FileName = trim(self::$FileName);
        $FileParts = pathinfo($FileName);
        $Extension = strtolower($FileParts['extension']);
        $ValidExtensions = self::$ValidExtensions;

        if (!$FileName) {
            self::SetMessage("ERROR: File name is empty.");
            return false;
        }

        if (!$ValidExtensions) {
            self::SetMessage("WARNING: All extensions are valid.");
            return true;
        }

        if (in_array($Extension, $ValidExtensions)) {
            
            self::SetMessage("MESSAGE: The extension '$Extension' appears to be valid.");

            if (!FileUpload::checkContentType($FileName, self::GetTempName())) {
            
                self::SetMessage("Error: ContentType is invalid.");
                return false;
            }
            
            return true;
        } else {
            self::SetMessage("Error: The extension '$Extension' is invalid.");
            return false;  
        }
    }

    public static function ValidateSize()
    {
        $MaximumFileSize = self::$MaximumFileSize;
        $TempFileName = self::GetTempName();
        $TempFileSize = @filesize($TempFileName) ? filesize($TempFileName) : 0;

        if ($MaximumFileSize == '') {
            self::SetMessage("WARNING: There is no size restriction.");
            return true;
        }

        if ($MaximumFileSize <= $TempFileSize) {
            self::SetMessage("ERROR: The file is too big. It must be less than $MaximumFileSize and it is $TempFileSize.");
            return false;
        }

        self::SetMessage("Message: The file size is less than the MaximumFileSize.");
        return true;
    }

    public static function ValidateExistance()
    {
        $FileName = self::$FileName;
        $UploadDirectory = self::$UploadDirectory;
        $File = $UploadDirectory . $FileName;

        if (file_exists($File)) {
            self::SetMessage("Message: The file '$FileName' already exist.");
            $UniqueName = rand() . $FileName;
            self::SetFileName($UniqueName);
            self::ValidateExistance();
        } else {
            self::SetMessage("Message: The file name '$FileName' does not exist.");
            return false;
        }
    }

    public static function ValidateDirectory()
    {
        $UploadDirectory = self::$UploadDirectory;

        if (!$UploadDirectory) {
            self::SetMessage("ERROR: The directory variable is empty.");
            return false;
        }

        if (!is_dir($UploadDirectory)) {
            self::SetMessage("ERROR: The directory '$UploadDirectory' does not exist.");
            return false;
        }

        if (!is_writable($UploadDirectory)) {
            self::SetMessage("ERROR: The directory '$UploadDirectory' does not writable.");
            return false;
        }

        if (substr($UploadDirectory, -1) != "/") {
            self::SetMessage("ERROR: The traling slash does not exist.");
            $NewDirectory = $UploadDirectory . "/";
            self::SetUploadDirectory($NewDirectory);
            self::ValidateDirectory();
        } else {
            self::SetMessage("MESSAGE: The traling slash exist.");
            return true;
        }
    }

    public static function ValidateImage()
    {
        $MaximumWidth = self::MaximumWidth;
        $MaximumHeight = self::MaximumHeight;
        $TempFileName = self::TempFileName;

        if ($Size = @getimagesize($TempFileName)) {
            $Width = $Size[0];   //$Width is the width in pixels of the image uploaded to the server.
            $Height = $Size[1];  //$Height is the height in pixels of the image uploaded to the server.
        }

        if ($Width > $MaximumWidth) {
            self::SetMessage("The width of the image [$Width] exceeds the maximum amount [$MaximumWidth].");
            return false;
        }

        if ($Height > $MaximumHeight) {
            self::SetMessage("The height of the image [$Height] exceeds the maximum amount [$MaximumHeight].");
            return false;
        }

        self::SetMessage("The image height [$Height] and width [$Width] are within their limitations.");     
        return true;
    }

    public static function UploadFile()
    {
        if (!self::ValidateExtension()) {
            FileUpload::removeUploadedFiles();
            Message::add('e', self::GetMessage(), 'back');
            exit;
        } 

        elseif (!self::ValidateSize()) {
            FileUpload::removeUploadedFiles();
            Message::add('e', self::GetMessage(), 'back');
            exit;
        }

        elseif (self::ValidateExistance()) {
            FileUpload::removeUploadedFiles();
            Message::add('e', self::GetMessage(), 'back');
            exit;
        }

        elseif (!self::ValidateDirectory()) {
            FileUpload::removeUploadedFiles();
            Message::add('e', self::GetMessage(), 'back');
            exit;
        }

        elseif (self::$IsImage && !self::ValidateImage()) {
            FileUpload::removeUploadedFiles();
            Message::add('e', self::GetMessage(), 'back');
            exit;
        }

        else {

            $FileName = self::$FileName;
            $TempFileName = self::$TempFileName;
            $UploadDirectory = self::$UploadDirectory;

            if (is_uploaded_file($TempFileName)) { 
                move_uploaded_file($TempFileName, $UploadDirectory . $FileName);
                return true;
            } else {
                FileUpload::removeUploadedFiles();
                return false;
            }
        }
    }
    
    public static function SetFileName($argv)
    {
        self::$FileName = $argv;
    }

    public static function SetUploadDirectory($argv)
    {
        self::$UploadDirectory = $argv;
    }

    public static function SetTempName($argv)
    {
        self::$TempFileName = $argv;
    }

    public static function SetValidExtensions($argv)
    {
        self::$ValidExtensions = $argv;
    }

    public static function SetMessage($argv)
    {
        self::$Message = $argv;
    }

    public static function SetMaximumFileSize($argv)
    {
        self::$MaximumFileSize = $argv;
    }
   
    public static function SetIsImage($argv)
    {
        self::$IsImage = $argv;
    }

    public static function SetMaximumWidth($argv)
    {
        self::$MaximumWidth = $argv;
    }

    public static function SetMaximumHeight($argv)
    {
        self::$MaximumHeight = $argv;
    }  
    
    public static function GetFileName()
    {
        return self::$FileName;
    }

    public static function GetUploadDirectory()
    {
        return self::$UploadDirectory;
    }

    public static function GetTempName()
    {
        return self::$TempFileName;
    }

    public static function GetValidExtensions()
    {
        return self::$ValidExtensions;
    }

    public static function GetMessage()
    {
        if (!isset(self::$Message)) {
            self::SetMessage("No Message");
        }

        return self::$Message;
    }

    public static function GetMaximumFileSize()
    {
        return self::$MaximumFileSize;
    }

    public static function GetIsImage()
    {
        return self::$IsImage;
    }

    public static function GetMaximumWidth()
    {
        return self::$MaximumWidth;
    }

    public static function GetMaximumHeight()
    {
        return self::$MaximumHeight;
    }
    
    public static function GetConfigFileMaxSize() 
    {
        return Config::getFromCacheDefault('CONFIG_FILE_MAX_SIZE', null, 10485760);
    }
    
    public static function checkContentType($fileName, $tmpName) {
        
        if (!FileUpload::$mimes) {
            require_once (BASEPATH . 'helper/mimes.php');
            
            FileUpload::$mimes = $mimes;
        }
        
        $extension = strtolower(substr($fileName, strrpos($fileName, '.') + 1));

        if (isset(FileUpload::$mimes[$extension])) {
            
            $getContentType = mime_content_type($tmpName);
            $contentType    = FileUpload::$mimes[$extension];
            
            if (is_array($contentType)) {
            
                if (in_array($getContentType, $contentType)) {
                    $result = true;
                } else {
                    $result = false;
                }

            } elseif ($getContentType == $contentType) {
                $result = true;
            } else {
                $result = false;
            }
            
        } else {
            $result = false;
        }
        
        if ($result == false) {
            
            FileUpload::removeUploadedFiles();
            
            return false;
            
        } else {
            
            if ($extension == 'png' && $getContentType == 'image/jpeg') {
                FileUpload::$fileExtension = 'jpg';
            }
            
            return true;
        }
    }
    
    public static function removeUploadedFiles() {
        
        if (FileUpload::$uploadedRealPathFiles) {
            foreach (FileUpload::$uploadedRealPathFiles as $k => $file) {
                @unlink($file);
            }
        }

        if (FileUpload::$uploadedFiles && class_exists('Mdwebservice')) {
            Mdwebservice::deleteUploadedFiles(FileUpload::$uploadedFiles);
        }
        
        return true;
    }
    
}