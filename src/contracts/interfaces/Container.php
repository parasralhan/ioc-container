<?php
/**
 * IOC Container Public API
 * 
 * @package bonzer/ioc-container  
 * @author  Paras Ralhan <ralhan.paras@gmail.com>
 */

namespace Bonzer\IOC_Container\contracts\interfaces;

interface Container {

  /**
   * --------------------------------------------------------------------------
   * Bind anything to container
   * --------------------------------------------------------------------------
   * 
   * @param string $key
   * @param mixed $binding
   * 
   * @Return void 
   * */
  public function bind( $key, $binding );

  /**
   * --------------------------------------------------------------------------
   * Bind anything to container (Singleton)
   * --------------------------------------------------------------------------
   * 
   * @param string $key
   * @param mixed $binding
   * 
   * @Return void 
   * */
  public function singleton( $key, $binding );

  /**
   * --------------------------------------------------------------------------
   * Resolves the binding
   * --------------------------------------------------------------------------
   * 
   * @param string $key
   * @param array $args
   * 
   * @Return mixed 
   * */
  public function make( $key, $args = [ ] );

  /**
   * --------------------------------------------------------------------------
   * Create Singletons
   * --------------------------------------------------------------------------
   * 
   * @param string $key
   * 
   * @Return mixed 
   * */
  public function make_singleton( $key, $args = [ ] );

  /**
   * --------------------------------------------------------------------------
   * Registered Container Bindings
   * --------------------------------------------------------------------------
   * 
   * @Return array 
   * */
  public function bindings();

  /**
   * --------------------------------------------------------------------------
   * Resover
   * 
   * Builds the instance of the given class
   * --------------------------------------------------------------------------
   * 
   * @param string $class
   * @param bool $singleton -- whether to resolve class as singleton
   * 
   * @Return void 
   * */
  public function resolve( $class, $singleton = FALSE );

}
