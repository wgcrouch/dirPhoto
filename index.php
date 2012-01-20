<?php

    $appDir = __DIR__;
    $configFile = __DIR__ . '.config.php';
    $config = false;
    
    $dirIterator = new DirectoryIterator($appDir);
    $albums = array();
    foreach ($dirIterator as $fileInfo) {
        if ($fileInfo->isDir() && $fileInfo->getFilename() != '.' && $fileInfo->getFilename() != '..') {         
            $albums[$fileInfo->getFilename()] = new $album; 
        }
    }
    
class Album
{
    public $path = '';
    public $name = '';
    public $pics = array();        

    public function __construct(SplFileInfo $fileInfo) 
    {
        $path = $fileInfo->getRealPath();
        $name = $fileInfo->getFilename();
    }
    public function getIterator() 
    {
        return new DirectoryIterator($this->path);
    }
    
    public function index() 
    {
        $createThumbs = false;        
        $thumbsDir = $this->_album->path . '/' . 'thumbs';
        $mediumDir = $this->_album->path . '/' . 'medium';            
        if (!file_exists($thumbsDir)) {
            mkdir($thumbsDir);
            $createThumbs = true; 
        }
        if (!file_exists($mediumDir)) {
            mkdir($mediumDir);
            $createThumbs = true; 
        }                
        $pics = array();
        foreach($this->getIterator() as $fileInfo) {            
            if ($fileInfo->isFile()) {
                $extension = $fileInfo->getExtension();
                if (strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg') {
                    $pics[] = clone($fileInfo);
                }
            }
        }
        if (count($pics)) {
            if ($createThumbs) {                
                foreach($pics as $pic) {
                    $this->createThumb($pic->getRealPath(), $thumbsDir . '/' . $pic->getFileName());
                    $this->createThumb($pic->getPath(), $mediumDir . '/' . $pic->getFileName(), 500);
                }            
            }
            $this->_album->pics = $pics;
        }       
        if ($createThumbs) {
            touch($this->_album->path . '/config.php');
        }
    }

    public function createThumb($imgPath, $newPath, $height = '165') 
    {
        $sizes = getimagesize($imgPath);

        $aspect_ratio = $sizes[1] / $sizes[0];

        if ($sizes[1] <= $height) {
            $newWidth = $sizes[0];
            $newHeight = $sizes[1];
        } else {
            $newHeight = $height;
            $newWidth = abs($newHeight / $aspect_ratio);
        }
        $destimg = ImageCreateTrueColor($newWidth, $newHeight);
        $srcimg = ImageCreateFromJPEG($imgPath);
        ImageCopyResized($destimg, $srcimg, 0, 0, 0, 0, $newWidth, $newHeight, ImageSX($srcimg), ImageSY($srcimg));
        ImageJPEG($destimg, $newPath, 90);
        imagedestroy($destimg);        
    }

}

?>

