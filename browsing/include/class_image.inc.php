<?php
/**
 * 
 * @author	
 * @version
 * @package
 * @license	
 * @copyright (c) 2001-2009 Lynx s.r.l.
 */

/**
 * 
 */
// classe  di astrazione per GD
class ImageDevice
{
  
  private $imagetypes;
  
  function ImageDevice() {
    // Get the image types supported by this PHP build.
    $this->imagetypes = imagetypes();
  }
  
  /**
   * 
   * @param string $filename - path to the file
   * @return resource on success, null on failure.
   */
  function imageCreateFromX($filename) {
    /*
     * Check if file $filename exists on filesystem
     */
    if(!file_exists($filename)) {
      /*
       * Instead of returning NULL, return an image created from
       * a default file.
       */
      return NULL;
    }
    
    /*
     * Obtain info about this image.
     */
    $imagesize = getimagesize($filename);
    
    /*
     * Prefer PNG over JPG
     * Prefer JPG over GIF
     */
    /* 
     * $imagesize[2] is one of the IMAGETYPE_XXX constants indicating the type
     * of the image
     */
    if($imagesize[2] == IMAGETYPE_PNG && ($this->imagetypes & IMG_PNG)) {
      return imagecreatefrompng($filename);
    }
    if($imagesize[2] == IMAGETYPE_JPEG && ($this->imagetypes & IMG_JPG)) {
      return imagecreatefromjpeg($filename);
    }
    if($imagesize[2] == IMAGETYPE_GIF && ($this->imagetypes & IMG_GIF)) {
      return imagecreatefromgif($filename);
    }
    return null;
  }
  /**
   * 
   * @param resource $image
   * @return bool
   */
  function imageX($image) {
    if($this->imagetypes & IMG_PNG) {
      return imagepng($image);
    }
    if($this->imagetypes & IMG_JPG) {
      return imagejpeg($image);
    }
    if($this->imagetypes & IMG_GIF) {
      return imagegif($image);
    }
    return false;
  } 
  
  /**
   * 
   * @param  string $filename - path to the file
   * @return array on success, null on failure.
   */
  function getImageSizeX($filename) {
    /*
     * Check if file $filename exists on filesystem
     */
    if(!file_exists($filename)) {
      /*
       * Instead of returning NULL, return an image created from
       * a default file.
       */
      return NULL;
    }
    
    return getimagesize($filename);
  }
  
  function error() {
    return '';
  }
/*
 * Resize the image and return the resource of the image.
 * The caller can output or write the file with new dimension
 * @param string $img_src the path to the file
 * @param string $max_width
 * @param string $max_height
 * @return $im_thumb the pointer (id) to the image
 */

  function resize_image($img_src,$max_width= MAX_WIDTH, $max_height = MAX_HEIGHT) {
        if (file_exists($img_src)) {
            // Get image dimensions
            $size_src = $this->GetImageSizeX($img_src);
            // mydebug(__LINE__,__FILE__,$size_src);

            $height_src = $size_src[1];
            $width_src = $size_src[0];
            $height_dest = $height_src;
            $width_dest = $width_src;

            $ratio = $max_height/$max_width ;
            if ($height_src/$width_src > $ratio){
             // height is the problem
                if ($height_src > $max_height){
                  $width_dest = floor($width_src*($max_height/$height_src));
                  $height_dest = $max_height;
                }
            } else {
                // width is the problem
                if ($width_src > $max_height){
                  $height_dest = floor($height_src*($max_width/$width_src));
                  $width_dest = $max_width;
                }
              }
            $extension = $this->imagetypes;

            $im_src = $this->imagecreateFromX($img_src);
            $im_thumb = imagecreatetruecolor($width_dest, $height_dest);
//            mydebug(__LINE__,__FILE__,$id->error);
// Versione immagini ridimensionate.
            $im_result_resized =  imagecopyresampled ($im_thumb, $im_src, 0, 0, 0, 0, $width_dest, $height_dest, $width_src, $height_src);
// Versione che non ridimensiona
            //$im_result =  ImageCopy ($im_dest, $im_src, $dest_x, $dest_y, $src_x, $src_y, $width_src, $height_src);
            imagedestroy($im_src);
//            imagedestroy($im_thumb);
            return $im_thumb;
        }
    }

}


?>