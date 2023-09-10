<?php
/**
 * IOC Container
 * 
 * @package bonzer/ioc-container    
 * @author  Paras Ralhan <ralhan.paras@gmail.com>
 */

namespace Bonzer\IOC_Container;

use Bonzer\Exceptions\Invalid_Param_Exception,
    Bonzer\Exceptions\Instantiating_Abstract_Class_Exception,
    Bonzer\Exceptions\Instantiating_Non_Instantiable_Class_Exception;
use Closure;

class Container implements \Bonzer\IOC_Container\contracts\interfaces\Container {

  protected static $_instance;

  /**
   * App Config
   *
   * @var array
   */
  protected $_app;

  /**
   * Providers Instances
   *
   * @var array
   */
  protected $_providers_instances = [ ];

  /**
   * Container Bindings
   *
   * @var string
   */
  protected $_bindings = [ ];

  /**
   * Holds Container bindings that are registered as singletons
   *
   * @var array
   */
  protected $_singletons = [ ];

  /**
   * Holds Container singleton instances
   *
   * @var array
   */
  protected $_singleton_instances = [ ];

  /**
   * --------------------------------------------------------------------------
   * Constructor
   * --------------------------------------------------------------------------
   * 
   * @Return Container 
   * */
  protected function __construct() {
    
  }

  /**
   * --------------------------------------------------------------------------
   * Generate class instance
   * --------------------------------------------------------------------------
   * 
   * @Return Container 
   * */
  public static function get_instance() {

    if ( is_null( static::$_instance ) ) {
      return static::$_instance = new static();
    }
    return static::$_instance;
  }

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
  public function bind( $key, $binding ) {
//    if ( array_key_exists( $key, $this->_bindings ) ) {
//      $this->_bindings[ $key ][] = $binding;
//    } else {
//      $this->_bindings[ $key ] = [$binding];
//    }
    $this->_bindings[ $key ][] = $binding;
  }

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
  public function singleton( $key, $binding ) {
    $this->bind( $key, $binding );
    $this->_singletons[ $key ][] = $binding;
  }

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
  public function make( $key, $args = [ ] ) {
    
    if ( array_key_exists( $key, $this->_bindings ) ) { 
      $total_bindings = count($this->_bindings[ $key ]);
      $binding = $this->_bindings[ $key ][$total_bindings - 1];

      // Create Singleton
      if ( array_key_exists( $key, $this->_singletons ) ) {
        return $this->make_singleton( $key, $args );
      }

      // if callback is bounded, invoke it
      if ( $binding instanceof Closure ) {
        return call_user_func_array( $this->_bindings[ $key ], [$args ] );
      }

      // if object is directly bounded, resolve directly
      if ( is_object( $binding ) ) {
        return $binding;
      }

      /* ==========================================================
       * If Binding is in form of string, 
       * pass the bounded string as class to resolve
       * ========================================================== */
      return $this->resolve( $binding );
    }

    // If not bounded, delegate to resolve method with key as class
    if ( !array_key_exists( $key, $this->_bindings ) ) {
      return $this->resolve( $key );
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Create Singletons
   * --------------------------------------------------------------------------
   * 
   * @param string $key
   * 
   * @Return mixed 
   * */
  public function make_singleton( $key, $args = [ ] ) {

    // If instance exitsts already, return it
    if ( isset( $this->_singleton_instances[ $key ] ) ) {
      return $this->_singleton_instances[ $key ];
    }

    if ( !array_key_exists( $key, $this->_singletons ) ) {
      $this->_singletons[ $key ] = $key;
    }
    
    if ( array_key_exists( $key, $this->_singletons )) {
      $total_bindings = count($this->_singletons[ $key ]);
      $binding = $this->_singletons[ $key ][$total_bindings - 1];
    }

    // if callback is bounded, invoke it
    if ( $binding instanceof Closure ) {
      return $this->_singleton_instances[ $key ] = call_user_func_array( $binding, [$args ] );
    }

    // If binding is in form of object
    // resigter is instance and return
    if ( is_object( $binding ) ) {
      return $this->_singleton_instances[ $key ] = $binding;
    }

    // Resolve the singleton
    return $this->resolve( $binding, TRUE );
  }

  /**
   * --------------------------------------------------------------------------
   * Registered Container Bindings
   * --------------------------------------------------------------------------
   * 
   * @Return array 
   * */
  public function bindings() {
    return $this->_bindings;
  }

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
  public function resolve( $class, $singleton = FALSE ) {

    $reflector = new \ReflectionClass( $class );

    // Abort if the class is Abstract
    if ( $reflector->isAbstract() && !$reflector->isInterface() ) {
      throw new Instantiating_Abstract_Class_Exception( "{$class} class is Abstract" );
    }

    // Resolve Interface
    if ( $reflector->isInterface() ) {
      if ( !array_key_exists( $class, $this->_bindings ) ) {
        throw new Instantiating_Abstract_Class_Exception( "{$class} interface is not bounded" );
      }
      $total_bindings = count($this->_bindings[ $class ]);
      $class = $this->_bindings[ $class ][$total_bindings - 1];
      
      $reflector = new \ReflectionClass( $class );
    }

    // Check for non instantiable class
    if ( !$reflector->isInstantiable() ) {

      $all_methods = array_map( function($method) {
        return $method->name;
      }, $reflector->getMethods() );

      /* ==========================================================
       * If init OR get_instance method exists
       * if YES
       * Instatiate via the avaiable method
       * ========================================================== */
      if ( in_array( 'init', $all_methods ) ) {
        $instance_method = 'init';
      }

      if ( in_array( 'get_instance', $all_methods ) ) {
        $instance_method = 'get_instance';
      }

      if ( isset( $instance_method ) ) {
        return $this->_instantiate_singletons( $reflector, $class, $instance_method );
      }

      throw new Instantiating_Non_Instantiable_Class_Exception( "{$class} class is not Instantiable" );
    }

    $constructor = $reflector->getConstructor();

    // Constructor without parameters
    if ( is_null( $constructor ) ) {
      if ( $singleton ) {
        $binding_key = array_search( $class, $this->_singletons ) ? array_search( $class, $this->_singletons ) : $class;
        return $this->_singleton_instances[ $binding_key ] = new $class;
      }
      return new $class;
    }


    $parameters = $constructor->getParameters();
    $dependencies = $this->_dependencies( $parameters );

    // Singleton with parameters
    if ( $singleton ) {
      $binding_key = array_search( $class, $this->_singletons ) ? array_search( $class, $this->_singletons ) : $class;
      return $this->_singleton_instances[ $binding_key ] = $reflector->newInstanceArgs( $dependencies );
    }

    // Brand new Object
    return $reflector->newInstanceArgs( $dependencies );
  }

  /**
   * --------------------------------------------------------------------------
   * Walk though dependencies and resolve them recursively
   * --------------------------------------------------------------------------
   * 
   * @param array $parameters
   * 
   * @Return array 
   * */
  protected function _dependencies( $parameters ) {

    $dependencies = [ ];

    foreach ( $parameters as $parameter ) {

      if ( method_exists($parameter, 'getType') ) {

        $dependency = $parameter->getType();
        $built_in = $dependency->isBuiltin();
        $class_name = $dependency->getName();

      } else {

        $dependency = $parameter->getClass();
        $built_in = is_null( $dependency );

        if ( !$built_in ) {
          $class_name = $dependency->name;
        }

      }      

      if ( $built_in ) {
        $dependencies[] = $this->_resolve_non_class( $parameter );
      } else {
        $dependencies[] = $this->resolve( $class_name );
      }

    }
    
    return $dependencies;
    
  }

  /**
   * --------------------------------------------------------------------------
   * Resolves other parameters
   * --------------------------------------------------------------------------
   * 
   * @param \ReflectionParameter $parameter
   * 
   * @Return void 
   * */
  public function _resolve_non_class( \ReflectionParameter $parameter ) {
    if ( $parameter->isDefaultValueAvailable() ) {
      return $parameter->getDefaultValue();
    }
    throw new Invalid_Param_Exception( "Cannot resolve the unkown!? {$parameter}" );
  }

  /**
   * --------------------------------------------------------------------------
   * Instantiate Singletons
   * --------------------------------------------------------------------------
   * 
   * @param \ReflectionClass $reflector
   * @param string $class
   * @param string $instance_method
   * 
   * @Return void 
   * */
  protected function _instantiate_singletons( \ReflectionClass $reflector, $class, $instance_method ) {

    $method = $reflector->getMethod( $instance_method );
    $parameters = $method->getParameters();
    if ( count( $parameters ) > 0 ) {
      $dependencies = $this->_dependencies( $parameters );
      $reflectionMethod = new \ReflectionMethod( $class, $instance_method );
      return $reflectionMethod->invokeArgs( NULL, $dependencies );
    } else {
      return $class::$instance_method();
    }
  }

}
