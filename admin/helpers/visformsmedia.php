<?php
/**
 * Media Helper for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.utilities.arrayhelper');

/**
 * Visforms modell
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsmediaHelper
{
	
	/**
	 * Checks if the file can be uploaded
	 *
	 * @param array File information
	 * @param string An error message to be returned
	 *
	 * @return boolean
	 * @since Joomla 1.6
	 */

	public static $savemodehigh = 0;
    public static $savemodelow = 1;

	private static function canUpload($file, &$err, $maxfilesize, $allowedextensions)
	{

		if (empty($file['name'])) {
			$err = 'COM_VISFORMS_ERROR_UPLOAD_INPUT';
			return false;
		}

		jimport('joomla.filesystem.file');
        if (str_replace(' ', '', $file['name']) != $file['name'] || $file['name'] !== JFile::makeSafe($file['name']))
		{
			$err = 'COM_VISFORMS_ERROR_WARNFILENAME';
			return false;
		}
        
        $filetypes = explode('.', $file['name']);

		if (count($filetypes) < 2)
		{
			// There seems to be no extension
			$err = 'COM_VISFORMS_ERROR_WARNFILETYPE';
			return false;
		}

		$format = strtolower(JFile::getExt($file['name']));
		$allowable = explode(',', $allowedextensions);		
		if ($format == '' || $format == false || (!in_array($format, $allowable)))
		{
			$err = 'COM_VISFORMS_ERROR_WARNFILETYPE';
			return false;
		}
		$maxSize = (int) ($maxfilesize  * 1024);
        if (($file['error'] == 1)
            || ($maxSize > 0 && (int) $file['size'] > $maxSize))
        {
            $err = 'COM_VISFORMS_ERROR_WARNFILETOOLARGE';
            return false;
        }

		$imginfo = null;

		$images = explode(',', "bmp,gif,jpg,jpeg,png");
		if (in_array($format, $images)) { // if its an image run it through getimagesize
			// if tmp_name is empty, then the file was bigger than the PHP limit
			if (!empty($file['tmp_name'])) {
				if (($imginfo = getimagesize($file['tmp_name'])) === FALSE) {
					$err = 'COM_VISFORMS_ERROR_WARNINVALID_IMG';
					return false;
				}
			} else {
				$err = 'COM_VISFORMS_ERROR_WARNFILETOOLARGE';
				return false;
			}
		}

		$xss_check =  JFile::read($file['tmp_name'], false, 256);
		$html_tags = array('abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext', 'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--');
		foreach($html_tags as $tag) {
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if (stristr($xss_check, '<'.$tag.' ') || stristr($xss_check, '<'.$tag.'>')) {
				$err = 'COM_VISFORMS_ERROR_WARNIEXSS';
				return false;
			}
		}
		return true;
	}
   
    /**
     * Upload files
     * 
     * @param object $visform Form Object with attached field information
     */

    public static function uploadFiles($visform, $client = 'site')
    {
        // set some parameters
        //upload limit php.ini
        $uploadMaxFileSize = self::toBytes(ini_get('upload_max_filesize'));
        //minimum from php ini and Visforms form settings (in Kilobyte)
        $maxfilesize = (((int) $uploadMaxFileSize > 0) && (((int) $visform->maxfilesize === 0) || ($visform->maxfilesize * 1024) > $uploadMaxFileSize)) ? $uploadMaxFileSize/1024 : $visform->maxfilesize;
        $input = JFactory::getApplication()->input;
        $files = $input->files->get('jform', array(), 'array');
        $savemode = (empty($visform->savemode)) ? 'cmd' : 'raw';
        //upload files
        $n=count($visform->fields );           
        for ($i=0; $i < $n; $i++)
        {
            $field = $visform->fields[$i];
            //Request has an fileupload with values
            if ((($client == 'site') && ($field->typefield == 'file') && (isset($_FILES[$field->name]['name'])) && ($_FILES[$field->name]['name'] !=''))
                || (($client == 'admin') && ($field->typefield == 'file') && (isset($_FILES['jform']['name'][$field->name])) && ($_FILES['jform']['name'][$field->name] !='')))
            {
                //only upload if field is not disabled
                if (!isset($field->isDisabled) || ($field->isDisabled == false))
                {
                    $file = ($client == 'site') ? $input->files->get($field->name, '',$savemode) :  $files[$field->name];
	                $allowedextensions =(!empty($field->allowedextensions)) ? $field->allowedextensions : $visform->allowedextensions;
                    if (empty($file))
                    {
                        throw new RuntimeException(JText::_('COM_VISFORMS_FILE_NOT_SAFE'));
                    }
                    $folder		= $visform->uploadpath;
                    if (!file_exists (JPath::clean(JPATH_ROOT . '/' . $folder)))
                    {
                        throw new RuntimeException(JText::_('COM_VISFORMS_UPLOAD_DIRECTORY_DOES_NOT_EXIST'));
                    }
                    else
                    {                     

                        // Set FTP credentials, if given
                        JClientHelper::setCredentialsFromRequest('ftp');

                        // Make the filename safe
                        $file['name_org'] = $file['name'];
                        $file['name']	= JFile::makeSafe($file['name']);
                        $file['name']     = str_replace(' ', '-', $file['name']);
                        // Check upload conditions
                        $err = null;
                        if (!self::canUpload($file, $err, $maxfilesize, $allowedextensions))
                        {
                                // The file can't be upload
                                throw new RuntimeException(JText::sprintf($err, $file['name_org'], $maxfilesize) . ' ('.$field->label.')');
                        }
                        else
                        {
                            //get a unique id to rename uploadfiles
                            $fileuid = uniqid('');

                            //rename file
                            $pathInf = pathinfo($file['name']);
                            $ext = $pathInf['extension'];
                            $file['new_name'] = basename($file['name'],".".$ext) . "_" . $fileuid . "." . $ext;
                            $file['new_name'] = strtolower($file['new_name']);

                            //get complete upload path with filename of renamed file
                            $filepath = JPath::clean(JPATH_SITE . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $file['new_name']);
                            $file['filepath'] = $filepath;
                            $file['filelink'] = JUri::base() . $folder . '/' . $file['new_name'];


                            //try to upload file
                            if (JFile::exists($file['filepath']))
                            {
                                // File exists
                                throw new RuntimeException(JText::sprintf('COM_VISFORMS_ERROR_FILE_EXISTS', $file['name_org']));
                            }
                            else
                            {
                                $checkoptions = (empty($visform->savemode)) ? array() : array('fobidden_ext_in_content' => false);
                                if (!JFile::upload($file['tmp_name'], $file['filepath'], false, false, $checkoptions))
                                {
                                        // Error in upload
                                        throw new RuntimeException(JText::sprintf('COM_VISFORMS_ERROR_UNABLE_TO_UPLOAD_FILE', $file['name_org']));
                                }
                            }
                        }
                    }
                    foreach ($file as $name => $value)
                    {
                        $visform->fields[$i]->file[$name] = $value;
                    }
	                $dbValue = new stdClass();
	                $dbValue->folder = $folder;
	                $dbValue->file = $field->file['new_name'];
	                $registry = new JRegistry($dbValue);
	                $visform->fields[$i]->dbValue = $registry->toString();
                }
            }
        }
        return true;
    }
    
    public static function deletefile($path)
    {        
        return JFile::delete(JPATH_ROOT . '/' . $path);
    }
    
    public static function copyfile($name, $oldpath, $restore)
    { 
        $newpath = JPATH_ROOT . '/images/visforms_save';
        $orgpath = JPATH_ROOT . '/'. $oldpath;
        if (empty($restore))
        {
			$fileexists = JFolder::exists($newpath);
            if (empty($fileexists))
            {
                JFolder::create($newpath);
            }
            return JFile::copy($orgpath, $newpath . '/' . $name);
        }
        else
        {
            return JFile::move($newpath . '/' . $name, $orgpath);
        }
    }
    
    public static function isImage($file)
    {
        if (empty($file))
        {
            return false;
        }
        $saveFile = JFile::makeSafe($file);
        if (empty($saveFile))
        {
            return false;
        }
        $ext = strtolower(JFile::getExt($saveFile));
        $imageExts = array('jpg', 'png', 'gif', 'bmp', 'jpeg');
        if ((!empty($ext)) && (in_array($ext, $imageExts)))
        {
            return true;
        }
        return false;
    }
    
    public static function getFileInfo($dbValue)
    {
       $displayDimension = "";
       $file = New JObject();
       $file->name = JHtml::_('visforms.getUploadFileName', $dbValue);
       $file->path = JHtml::_('visforms.getUploadFilePath', $dbValue);
       $file->filepath = (!empty($file->path)) ? JPath::clean(JPATH_SITE . DIRECTORY_SEPARATOR . $file->path) : '';
       $file->link = JHtml::_('visforms.getUploadFileLink', $dbValue);
       if (empty($file->name) || empty($file->path))
       {
           return false;
       }
       $file->isimage = self::isImage($file->name);
       if (!empty($file->isimage))
       {
            $test = JUri::root() . $file->path;
            $info = @getimagesize(JPATH_SITE . '/' . $file->path);
            $file->width  = @$info[0];
            $file->height = @$info[1];
            if (($info[0] > 60) || ($info[1] > 60))
            {
                $dimensions = JHelperMedia::imageResize($file->width, $file->height, 60);
                $file->width = $dimensions[0];
                $file->height = $dimensions[1];
            }
            if ((!empty($file->width)) && (!empty($file->height)))
            {
                $file->displayDimension = 'width="' . $file->width .'" height="' . $file->height .'" ';
            }
        }
        
        return $file;
    }

    /**
     * Small helper function that properly converts any
     * configuration options to their byte representation.
     *
     * @param   string|integer  $val  The value to be converted to bytes.
     *
     * @return integer The calculated bytes value from the input.
     *
     * @since 3.3
     */
    public static function toBytes($val)
    {
        switch ($val[strlen($val) - 1])
        {
            case 'M':
            case 'm':
                return (int) $val * 1048576;
            case 'K':
            case 'k':
                return (int) $val * 1024;
            case 'G':
            case 'g':
                return (int) $val * 1073741824;
            default:
                return $val;
        }
    }
}