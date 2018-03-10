<?php
/**
 * Static Caller Abstract
 * 
 * @package bonzer/ioc-container    
 * @author  Paras Ralhan <ralhan.paras@gmail.com>
 */

namespace Bonzer\IOC_Container\contracts;

use Bonzer\exceptions\Base_Exception;

abstract class Facade_Abstract {

  public function __construct() {
    
  }

  /**
   * --------------------------------------------------------------------------
   * Gets the tatic Caller Instance
   * --------------------------------------------------------------------------
   * 
   * */
  protected static function _get_facade_accessor() {
    throw new Base_Exception( 'No implementation of the method ' . __METHOD__ . ' found!' );
  }

  /**
   * --------------------------------------------------------------------------
   * Magic __callStatic
   * --------------------------------------------------------------------------
   * 
   * @Return mixed 
   * */
  public static function __callStatic( $method, $args ) {
    $accessor = static::_get_facade_accessor();
    if ( is_object( $accessor ) ) {
      $instance = $accessor;
    }
    switch ( count( $args ) ) {
      case 0:
        return $instance->$method();
      case 1:
        return $instance->$method( $args[ 0 ] );
      case 2:
        return $instance->$method( $args[ 0 ], $args[ 1 ] );
      case 3:
        return $instance->$method( $args[ 0 ], $args[ 1 ], $args[ 2 ] );
      case 4:
        return $instance->$method( $args[ 0 ], $args[ 1 ], $args[ 2 ], $args[ 3 ] );
      default:
        return call_user_func_array( array(
          $instance,
          $method ), $args );
    }
  }

}
