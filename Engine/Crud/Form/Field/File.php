<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

use Engine\Crud\Form\Field;

/**
 * File field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class File extends Field
{
	/**
	 * Form element type
	 * @var string
	 */
	protected $_type = 'file';

	/**
	 * File name template
	 * @var string
	 */
	protected $_template;
	
	/**
	 * Upload directory
	 * @var string
	 */
	protected $_uploadDirectory;
	
	/**
	 * Maximum file size
	 * @var integer
	 */
	protected $_size;
	
	/**
	 * File extentions
	 * @var string
	 */
	protected $_extensions;

    /**
     * Upload file name
     * @var string
     */
    protected $_fileName;

    /**
     * Full upload file name
     * @var string
     */
    protected $_fileFullName;
	
	/**
	 * Constructor
	 *
     * @param string $label
     * @param string $name
	 * @param string $uploadDirectory
	 * @param string $template
	 * @param string $desc
	 * @param int $size
	 * @param bool $required
	 * @param int $width
	 * @param string $extensions
	 */
	public function __construct(
        $label = null,
        $name = false,
        $uploadDirectory, 
        $template = '{id}',
        $desc = null, 
        $size = '1024000', 
        $required = false, 
        $width = 150, 
        $extensions = null
    ) {
		parent::__construct($label, $name, $desc, $required, $width, null);
		
		$this->_uploadDirectory = $uploadDirectory;
		$this->_template = $template;
		$this->_extensions = $extensions;
		$this->_size = $size;
	}

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
    protected function _init()
	{
        parent::_init();
		if (null !== $this->_extensions) {
			$this->_validators[] = array(
				'validator' => 'Engine\Validation\File\Extension',
				'options' => array(
					$this->_extensions
				),			
			);
		}
		$this->_validators[] = array(
			'validator' => 'Engine\Validation\File\Count',
			'options' => array(
				'min' => 0,
				'max' => 1
			),			
		);
	}

    /**
     * Update field
     *
     * return void
     */
	public function updateField() 
	{
		$this->_form->setAttrib('enctype', 'multipart/form-data');

		$this->_notSave = true;
		
        $key = $this->getKey();
	    if (empty($_FILES) || !isset($_FILES[$key])) {				
			$_FILES[$key] = ["name" => "", "type" => "", "tmp_name" => "", "error" => 4, "size" => 0];
		}
	}

    /**
     * After save field trigger
     *
     * @param array $data
     */
	public function postSaveAction(array $data)
	{
	    $key = $this->getKey();
		if (empty($_FILES) || !isset($_FILES[$key]) || !empty($_FILES[$key]['error'])) {
			return false;
		}
		$fullName = $this->_template;
		if (!$fullName) {
			$file = explode(".", $_FILES[$key]['name']);
			$fullName = $file[0];
		}
		
		if (strpos($fullName,'{sha}') !== false) {
			$file_hash_name = ($this->sha1) ? $this->sha1($this->getId()) : $this->getId();
			$fullName = str_replace('{sha}', $file_hash_name, $fullName);
		}
        $uploadDirectory = rtrim(\Engine\Tools\String::generateStringTemplate($this->_uploadDirectory, $data, '{', '}'), "/)");
		$fullName = \Engine\Tools\String::generateStringTemplate($fullName, $data, '{', '}');

		$fileType = strtolower(end(explode(".", $_FILES[$key]['name'])));
		$fullName = $fullName.'.'.$fileType;
		$zend_upload_dir = $uploadDirectory;
		$fullName = $uploadDirectory.'/'.$fullName;
		$pathinfo = pathinfo($fullName);
		$uploadDirectory = $pathinfo['dirname'];
		$fileName = $pathinfo['basename'];
		
		/* Debuger: */
		/*
		 echo '$fileType = '.$fileType."\n";
		 echo '$this->fileName = '.$this->fileName."\n";
		 echo '$this->uploadDirectory = '.$this->uploadDirectory."\n";
		 echo '$fullName = '.$fullName."\n";
		 exit;
		*/
		if (!is_dir($uploadDirectory)) { 
		    mkdir($uploadDirectory, 0755, true);
		}
		if (file_exists($fullName)) {
		    unlink($fullName);   
		}
		if (is_uploaded_file($_FILES[$key]['tmp_name'])) {	
		    $result = move_uploaded_file($_FILES[$key]['tmp_name'], $fullName);
		} elseif (is_file($zend_upload_dir.'/'.$_FILES[$key]['name'])) {
            $result = rename($zend_upload_dir.'/'.$_FILES[$key]['name'], $fullName);
		}

		$def_data = [$this->_name => $fileName];
		$container = $this->_form->getContainer();
		$result = $container->update($this->_id, $def_data);
		$_FILES[$key]['name'] = $fileName;
	}

    /**
     * Return field value
     *
     * @return string|array
     */
    public function getValue() 
	{
		$key = $this->getKey();
	    if (array_key_exists($key, $_FILES)) {
	        return $_FILES[$key]['name'];
	    }

		return $this->_value;
	}
	
	/**
	 * Return sha uniq id
	 * 
	 * @param int $id
	 */
	protected function sha1($id = 0) 
	{
		return sha1(uniqid($id, true));
	}
}