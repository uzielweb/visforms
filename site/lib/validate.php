<?php
/**
 * Visforms validation class
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

abstract class VisformsValidate
{
       protected $type;
       protected $value;
       protected $rules;
       protected $valid;
       protected $regex;

       public function __construct($type, $args)
       {
           $this->type = $type;
           $this->args = $args;
       }

       public static function validate($type, $args)
       {
           $classname = get_called_class() . ucfirst($type);
           if (!class_exists($classname))
           {
               //try to register it
               JLoader::register($classname, dirname(__FILE__) . '/validate/'. $type . '.php');
               if (!class_exists($classname))
               {
                    throw new RuntimeException('Unable to load validation class ' . $type);
               }
           }
           //Validate with the appropriate subclass
           $validation = new $classname($type, $args);
           $valid = $validation->test();
           return $valid;
       }
       
       abstract protected function test();
}