<?php
namespace Bonzer\IOC_Container\facades;
/**
 * IOC Container Static Caller
 * 
 * @package bonzer/ioc-container    
 * @author  Paras Ralhan <ralhan.paras@gmail.com>
 */

use Bonzer\IOC_Container\Container as IOC_Container;
class Container extends \Bonzer\IOC_Container\contracts\Facade_Abstract {

  /**
   * --------------------------------------------------------------------------
   * IOC Container Static Caller
   * --------------------------------------------------------------------------
   * 
   * @Return Container 
   * */
  public function __construct() {
    parent::__construct();
  }

  /**
   * --------------------------------------------------------------------------
   * Get the instance of the IOC Container
   * --------------------------------------------------------------------------
   * 
   * @Return Container 
   * */
  protected static function _get_facade_accessor() {
    return IOC_Container::get_instance();
  }

}
