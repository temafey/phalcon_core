<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;
	
/**
 * Image field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class ImageAdmin extends Image
{
    /**
     * Save in database image filename
     * @var bool
     */
    protected $_saveFilename;

    /**
     * Remove image source after resizing
     * @var bool
     */
    protected $_removeImgSource;

    /**
     * Resize with watermark
     * @var bool
     */
    protected $_watermark;

    /**
     * Sizes for resizing
     * @var
     */
    protected $_resizerSizes;

    /**
     * Watermark position om image
     * @var int
     */
    protected $_watermarkPosition;

    /**
     * Resize
     * @var bool
     */
    protected $_resize;

    /**
     * @param string $label
     * @param string $name
     * @param string $key
     * @param string $uploadDirectory
     * @param string $fileName
     * @param bool $saveFilename
     * @param string $label
     * @param string $description
     * @param string $size
     * @param string $renderTemplate
     * @param string $imageTitleTemplate
     * @param bool $required
     * @param bool $notEdit
     * @param int $width
     * @param bool $removeImgSource
     * @param bool $watermark
     * @param int $watermarkPosition
     * @param array $resizeSizes
     * @param bool $resize
     */
    public function __construct(
        $label = null,
        $name = null,
        $uploadDirectory, 
        $fileName = '{sha}', 
        $saveFilename = true, 
        $label = null,
        $description = null, 
        $size = '1024000',
        $renderTemplate = null, 
        $imageTitleTemplate = null/* limit to 100K*/, 
        $required = false, 
        $notEdit = false, 
        $width = 150,
        $removeImgSource = false,
        $watermark = false, 
        $watermarkPosition = 4,
        $resizeSizes = [
            'big' => ['width' => 620, 'height' => 465], 
            'middle' => ['width' => 240, 'height' => 180], 
            'small' => ['width' => 140, 'height' => 105]
        ],
        $resize = true
   ) {
        parent::__construct(
            $label,
            $name,
            $uploadDirectory,
            $fileName,
            $label,
            $description,
            $size,
            $required,
            $notEdit,
            $renderTemplate,
            $imageTitleTemplate
        );

        $this->_saveFilename = $saveFilename;
        $this->_removeImgSource = $removeImgSource;
        $this->_watermark = $watermark;
        $this->_watermarkPosition = $watermarkPosition;
        $this->_resizeSizes = $resizeSizes;
        $this->_resize = $resize;
	}

    /**
     * After save field trigger
     *
     * @param array $data
     */
    public function postSaveAction(array $data)
	{
		$key = $this->getKey();
		if (!empty($_FILES) && isset($_FILES[$key]) && empty($_FILES[$key]['error'])) {
			$this->_fileFullName = $this->_fileName;
			$container = $this->_form->getContainer();
			if (strpos($this->_fileFullName, '{sha}') !== false) {
				$file_hash_name = ($this->sha1) ? $this->sha1( $this->getId()) : $this->getId();
	            $this->_fileFullName = str_replace('{sha}', $file_hash_name, $this->_fileFullName);
            }
           	if (strpos($this->_fileFullName, '{name}') !== false) {
                $file = explode(".", $_FILES [$key] ['name']);
                $this->_fileFullName = str_replace('{name}', str_replace(" ", "_", trim($file [0])), $this->_fileFullName);
            }
			if (strpos($this->_fileFullName, '{id}') !== false) {
                $file = explode(".", $_FILES [$key] ['name']);
                $this->_fileFullName = str_replace('{id}', $this->getId(), $this->_fileFullName);
            }
            if (strpos($this->_fileFullName, '{file_name}') !== false) {
                $file = explode (".", $_FILES [$key] ['name']);
                $this->_fileFullName = str_replace('{file_name}', str_replace(" ", "_", trim($file [0])), $this->_fileFullName);
            }
            $this->_fileFullName = \Engine\Tools\String::generateStringTemplate($this->_fileFullName, $data);

            if ($this->_fileFullName === false) {
                $file = explode(".", $_FILES [$key] ['name']);
                $this->_fileFullName = str_replace(" ", "_", trim($file [0]))."_".$this->getId();
            }

			$file_type = strtolower(end(explode(".", $_FILES [$key] ['name'])));

			if ($this->_saveFilename) {
				$file = explode(".", $_FILES [$key] ['name']);
				$pathinfo = pathinfo($this->_fileFullName);
				$this->_fileName = $pathinfo['dirname']."/" . str_replace(" ", "_", trim($file [0]))."_".$this->getId().'.'.$file_type;
			} else {
				$this->_fileName = $this->_fileFullName.'.'.$file_type;
			}

			$zend_upload_dir = $this->_uploadDirectory;
			$this->_fileFullName = $this->_uploadDirectory.'/'.trim($this->_fileName, "/,\\");
			$pathinfo = pathinfo($this->_fileFullName);
			$this->_uploadDirectory = $pathinfo['dirname'];

			$this->_fileName = $pathinfo['basename'];

			/* Debuger: */
			/*
			 echo '$file_type = '.$file_type."\n";
			 echo '$this->_fileName = '.$this->_fileName."\n";
			 echo '$this->_uploadDirectory = '.$this->_uploadDirectory."\n";
			 echo '$this->_fileFullName = '.$this->_fileFullName."\n";
			 echo '$file_resize_small = '.$file_resize_small."\n";
			 echo '$file_resize_middle = '.$file_resize_middle."\n";
			 echo '$file_resize_big = '.$file_resize_big."\n";
			 exit;
			 */
			if (!file_exists($this->_uploadDirectory)) {
			     mkdir($this->_uploadDirectory, 0755, true);
			}
            $file_resize_big = null;
            $file_resize_middle = null;
            $file_resize_small = null;
			if ($this->_resize) {
			    $file_resize_small  = $this->_uploadDirectory.'/small/' .$pathinfo['basename'];
			    $file_resize_middle = $this->_uploadDirectory.'/middle/'.$pathinfo['basename'];
			    $file_resize_big    = $this->_uploadDirectory.'/big/'   .$pathinfo['basename'];
			    $dirs = ['big','middle','small'];
			    \Engine\Tools\File::rarmdir($this->_uploadDirectory, $dirs, 0755, true);
			    if (file_exists($file_resize_small))  unlink($file_resize_small);
			    if (file_exists($file_resize_middle)) unlink($file_resize_middle);
			    if (file_exists($file_resize_big))    unlink($file_resize_big);
			}

			if (file_exists($this->_fileFullName)) { 
				unlink($this->_fileFullName);
			}
			if (is_uploaded_file($_FILES [$key]['tmp_name'])) {
			    move_uploaded_file($_FILES [$key]['tmp_name'], $this->_fileFullName);
			} elseif (is_file($zend_upload_dir.'/'.$_FILES [$key] ['name'])) {
			   rename($zend_upload_dir.'/'.$_FILES [$key] ['name'], $this->_fileFullName);
			}

			if ($this->_resize) {
			    $watermark = array('small' => false, 'middle' => false, 'big' => false);
			    if ($this->_watermark !== false) {
			        if (is_array($this->_watermark)) {
			            if (isset($this->_watermark['small'])) {
			                $watermark['small'] = $this->_watermark['small'];
			            }
			            if (isset($this->_watermark['middle'])) {
			                $watermark['middle'] = $this->_watermark['middle'];
			            }
			            if (isset($this->_watermark['big'])) {
			                $watermark['big'] = $this->_watermark['big'];
			            }
			        } else {
			            $watermark['big'] = $this->_watermark;
			        }
			    }


			    \Engine\Tools\Image::resize($this->_fileFullName, $file_resize_big, $this->_resizeSizes['big']['width'], $this->_resizeSizes['big']['height'], false, $watermark['big'], $this->_watermarkPosition);
			    \Engine\Tools\Image::resize($this->_fileFullName, $file_resize_middle, $this->_resizeSizes['middle']['width'], $this->_resizeSizes['middle']['height'], true, $watermark['middle'], $this->_watermarkPosition);
			    \Engine\Tools\Image::resize($this->_fileFullName, $file_resize_small, $this->_resizeSizes['small']['width'], $this->_resizeSizes['small']['height'], true, $watermark['small'], $this->_watermarkPosition);
			}
			if ($this->_removeImgSource){
			    unlink($this->_fileFullName);
			}

			$def_data = [$this->_name => $this->_fileName];
			$status = $this->_form->getFieldByName('status');
			if ($status !== false) {
			    $statusColumn\Name = $status->getName();
			    $def_data[$statusColumn\Name] = 1;
			}
            $model = $container->getModel();
            $model->update($def_data, $model->getPrimary()." = ?", $this->getId());
		}
	}

	/**
	* Set no resize image.
	* 
	* @return \Engine\Crud\Form\Field\ImageAdmin
	*/
	public function setNoResizeImage()
	{
		$this->_resize = false;
		
		return $this;
	}
}