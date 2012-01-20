<?php    
class Album
{
    public $path = '';
    public $name = '';
    public $pics = array();        
    public $thumbsDir;
    public $mediumDir; 
    
    public function __construct(SplFileInfo $fileInfo, $forceCreate = false) 
    {
        $this->path = $fileInfo->getRealPath();
        $this->name = $fileInfo->getFilename();
        $this->index($forceCreate);
    }
    public function getIterator() 
    {
        return new DirectoryIterator($this->path);
    }
    
    protected function checkThumbDirs()
    {
        $createThumbs = false;   
        $this->thumbsDir = $this->path . '/' . 'thumbs';
        $this->mediumDir = $this->path . '/' . 'medium';            
        if (!file_exists($this->thumbsDir)) {
            print $this->thumbsDir;
            mkdir($this->thumbsDir);
            $createThumbs = true; 
        }
        if (!file_exists($this->mediumDir)) {
            mkdir($this->mediumDir);
            $createThumbs = true; 
        }      
        return $createThumbs;
    }
    
    public function index($forceCreate = false) 
    {
        $createThumbs = $this->checkThumbDirs() || $forceCreate;       
                  
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
                    $this->createThumb($pic->getRealPath(), $this->thumbsDir . '/' . $pic->getFileName());
                    $this->createThumb($pic->getPath(), $this->mediumDir . '/' . $pic->getFileName(), 500);
                }            
            }
            $this->pics = $pics;
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




$appDir = __DIR__;

$forceCreate = false;
if (array_key_exists('recreate', $_GET)) {
    $forceCreate = true;
}

$dirIterator = new DirectoryIterator($appDir);
$albums = array();
foreach ($dirIterator as $fileInfo) {
    if ($fileInfo->isDir() && !$fileInfo->isDot() && strpos($fileInfo->getFileName(), '.') !== 0) {         
        $albums[$fileInfo->getFilename()] = new Album($fileInfo, $forceCreate); 
    }
}

?>


<!DOCTYPE HTML>
<html>
    <head></head>
    <body>
        <?php if (!$album) : ?>
            <?php foreach ($albums as $album) : ?>
            <a href="?album=<?=$album->name?>"><?= $album->name ?></a><br/>
            <?php endforeach; ?>
        <?php else : ?>
        <?php endif; ?>
    </body>
</html>




