<?php
/**
 * FileUploader file
 *
 * PHP version 5
 *
 * @package  Default
 * @author   vito <vito@lynxlab.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
/**
 * Description of FileUploader
 *
 * @package  Default
 * @author   vito <vito@lynxlab.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class FileUploader
{
    public function __construct($pathToUploadFolder,$fieldUploadName='uploaded_file')
    {
//        print_r($_FILES);
        $this->_error = $_FILES[$fieldUploadName]['error'];
        $this->_name = $_FILES[$fieldUploadName]['name'];
        $this->_size = $_FILES[$fieldUploadName]['size'];
        $this->_tmpName = $_FILES[$fieldUploadName]['tmp_name'];
        $this->_type = $_FILES[$fieldUploadName]['type'];

        $this->_destinationFolder = $pathToUploadFolder;
        $this->_errorMessage = '';
    }

    public function upload($reduction=false)
    {
        $this->cleanFileName();

        if (!is_dir($this->_destinationFolder)) {
            if ($this->createUploadDir($this->_destinationFolder)) {
                $this->_errorMessage = 'Upload directory do not exists';
                //return ADA_FILE_UPLOAD_ERROR_UPLOAD_PATH;
                return false;
            }
        }
                
        if (!is_writable($this->_destinationFolder)) {
            $this->_errorMessage = 'Upload directory not writable';
            //return ADA_FILE_UPLOAD_ERROR_UPLOAD_PATH;
            return false;
        }
        if (empty($this->_name)) {
            $this->_errorMessage = 'Uploaded filename is empty';
            return false;
        }
        if ($this->_error) {
            $this->_errorMessage = 'There was an error during the upload';
            //return $this->_error;
            return false;
        }
        $ADA_MIME_TYPE = $GLOBALS['ADA_MIME_TYPE'];
        if ($ADA_MIME_TYPE[$this->_type]['permission'] != ADA_FILE_UPLOAD_ACCEPTED_MIMETYPE) {
            $this->_errorMessage = 'Mimetype not accepted';
            //return ADA_FILE_UPLOAD_ERROR_MIMETYPE;
            return false;
        }
        if ($this->_size >= ADA_FILE_UPLOAD_MAX_FILESIZE) {
            //return ADA_FILE_UPLOAD_ERROR_FILESIZE;
            $this->_errorMessage = 'The uploaded file size exceeds the maximum permitted filesize';
            return false;
        }
        if ($reduction) $this->reduceImage();

        return $this->moveFileToDestinationFolder();
    }

    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    private function cleanFileName()
    {
        $this->_name = preg_replace('/[^\w\-\.]/', '_', $this->_name);
    }

    private function moveFileToDestinationFolder()
    {
        if (file_exists($this->getPathToUploadedFile()))
                {
            $this->_name = time() . '_' . $this->_name;
        }

        return @move_uploaded_file($this->_tmpName, $this->getPathToUploadedFile());
    }

    public function getPathToUploadedFile()
    {
        return $this->_destinationFolder . $this->_name;
    }

    public static function listDirectoryContents($pathToDirectory, $filterFiles=FileUploader::FILES_AND_DIRS, $includeFullPath = false)
    {

        if (is_dir($pathToDirectory)) {
            $files = scandir($pathToDirectory);
            $filteredFiles = array();

            foreach ($files as  $f) {
                $pathToTheFile = $pathToDirectory . DIRECTORY_SEPARATOR . $f;
                if (self::testFileType($pathToTheFile, $filterFiles)) {
                    if ($includeFullPath) {
                        $filteredFiles[] = $pathToTheFile;
                    } else {
                        $filteredFiles[] = $f;
                    }
                }
            }

            if ($includeFullPath) {
                $diffArray = array($pathToDirectory . DIRECTORY_SEPARATOR . '.',
                                   $pathToDirectory . DIRECTORY_SEPARATOR . '..');
            } else {
                $diffArray = array('.', '..');
            }

            return array_diff($filteredFiles, $diffArray);
        } else {
            return array();
        }
    }

    private static function testFileType($pathToTheFile, $type)
    {
        switch($type) {
            case FileUploader::FILES_ONLY:
                return is_file($pathToTheFile);

            case FileUploader::DIRS_ONLY:
                return is_dir($pathToTheFile);

            default:
                return true;
        }
    }

    /**
     * Creates the upload directory for the user 
     *
     * @param integer $user_id
     * @return FALSE if an error occurs, a string containing the path to the
     * directory on success
     */
    static public function createUploadDir() {
  
        if (mkdir($this->_destinationFolder) == FALSE) {
            return FALSE;
        }

        return $this->_destinationFolder;
    }
    
    /**
     * Reduce image using GD
     * 
     */
    public function reduceImage() {
        require_once ROOT_DIR .'/browsing/include/class_image.inc.php';
        $id_img = new ImageDevice();
        $new_img = $id_img->resize_image($this->_tmpName, AVATAR_MAX_WIDTH, AVATAR_MAX_HEIGHT);
        if(stristr($this->_type, 'png')) {
          imagepng($new_img,$this->_tmpName);
        }
        if(stristr($this->_type, 'jpeg')) {
          imagejpeg($new_img,$this->_tmpName);
        }
        if(stristr($this->_type, 'gif')) {
          imagegif($new_img,$this->_tmpName);        
        }
//        imagejpeg($new_img,$this->_tmpName);
    } 
            
    
    private $_name;
    private $_tmpName;
    private $_size;
    private $_type;
    private $_error;
    private $_errorMessage;
    private $_destinationFolder;

    const FILES_AND_DIRS = 0;
    const FILES_ONLY = 1;
    const DIRS_ONLY = 2;
}