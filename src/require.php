<?php

if ( ! defined( 'DS' ) ) define( 'DS', DIRECTORY_SEPARATOR );

//////////////////////////////////////////////////

if ( ! class_exists( 'ProjectHandler' ) ) {
class ProjectHandler
{

  const DEFINES = 'defines';
  const FILES = 'files';

  /**
   * The actions results, fetched by static::TYPE, $callback
   */

  public static array $result = [
    self::DEFINES => [],
    self::FILES => []
  ];

  /**
   * This is the list of actions to perform
   * as determined by the entry values
   */

  public array $actions = [];

  /**
   * These are the parameters to the actions and callbacks
   */

  public array $parameters = [
    'Run' => [ 'entry.php' ]
  ];

  /**
   * Check if a result value exists
   */

  public function hasResult( $type, $action )
  {

    return isset( static::$result[ $type ][ $action ] );

  }

  /**
   * Get a result value
   */

  public function getResult( $type, $action )
  {

    return static::$result[ $type ][ $action ];

  }

  /**
   * Call with default settings
   */

  public function __invoke()
  {

    return $this->call( 'call', [ 'Run' ] );

  }

  /**
   * Call a callable or action/method-callable
   * that has it's parameters
   * provided by the parameters property
   */

  public function call( $callback, $parameters = null )
  {

    $action = $this->actionExists( $callback ) ? $this->buildAction( $callback ) : $callback;

    if ( is_null( $parameters ) ) {
      $parameters = $this->getParameters( $callback );
    }
    if ( is_null( $parameters ) ) {
      $parameters = [];
    }

    $result = $action( ...$parameters );

    return $result;

  }

  /**
   * Call entry, get the actions
   * run the actions/callbacks with call
   */

  public function run( $entry )
  {

    if ( ! is_array( $entry ) ) {
      $this->call( 'Entry', [ $entry ] );
    }

    foreach ( $this->actions as $methodName ) {
      $this->call( $methodName );
    }

  }

  /**
   * Set the entry value
   * or fetch it by file
   */

  public function entry( array|string $entry )
  {

    if ( ! is_array( $entry ) ) {
      $dir = __FILE__;
      if ( 'require.php' === $entry ) {
        $dir = dirname( __FILE__ );
      }

      $entryPath = helperPathUpFind( $dir, [ $entry ] );

      if ( $entryPath ) {
        $entry = helperPathAppend( $entryPath, $entry );
      } else {
        throw new Exception( 'code 9419' );
      }

      $entry = require $entry;
    }

    if ( ! is_array( $entry ) ) {
      return;
    }

    foreach ( $entry as $action => $parameters ) {
      $this->setAction( $action, $parameters );

    }

    return $this->parameters;

  }

  protected function setAction( $action, array $parameters = [] )
  {

    if ( $this->actionExists( $action ) ) {
      if ( ! in_array( $action, $this->actions ) ) {
        $this->actions[] = $action;
        if ( isset( $parameters ) ) {
          $this->setParameters( $action, $parameters );
        }
      } else {
        return false;
      }
    }

    return true;

  }

  protected function buildAction( $action )
  {

    return [ $this, $action ];

  }

  protected function actionExists( $action )
  {

    return is_callable( $this->buildAction( $action ) );

  }

  protected function actionNormal( $action )
  {

    return $action;

  }

  /**
   * Set the parameters of a callback
   */

  public function setParameters( $callback, array $args )
  {

    $this->parameters[ $callback ] = $args;

  }

  /**
   * Fetch the parameters of a callback
   */

  public function getParameters( $callback )
  {

    return isset( $this->parameters[ $callback ] ) ? $this->parameters[ $callback ] : null;

  }

  /**
   * Get the parameters of all callbacks
   */

  public function getParametersList()
  {

    return $this->parameters;

  }

  public function helperPathDefineAbsolute( string|array $define, $path = null )
  {

    return static::$result[ static::DEFINES ] = helperPathDefineAbsolute( $define, $path, static::$result[ static::DEFINES ] );

  }

  public function helperPathDefineRelative( string|array $define, $constant = null, $path = null )
  {

    return static::$result[ static::DEFINES ] = helperPathDefineRelative( $define, $constant, $path, static::$result[ static::DEFINES ] );

  }

  public function helperPathRequireAbsolute( string|array $file )
  {

    return static::$result[ static::FILES ] = helperPathRequireAbsolute( $file, static::$result[ static::FILES ] );

  }

  public function helperPathRequireRelative( string|array $constant, $file = null )
  {

    return static::$result[ static::FILES ] = helperPathRequireRelative( $constant, $file, static::$result[ static::FILES ] );

  }

}
}

/**
 * Define absolute path constants
 */

function helperPathDefineAbsolute( string|array $define, $path = null, ?array $defines = [] )
{

  if ( is_null( $defines ) ) {
    $defines = [];
  }

  if ( is_string( $define ) && ! defined( $define ) && isset( $path ) ) {
    define( $define, $path );
    $defines[ $define ] = $path;
  } elseif ( is_array( $define ) ) {
    foreach ( $define as $name => $path ) {
      $defines = helperPathDefineAbsolute( $name, $path, $defines );
    }
  }

  return $defines;

}

/**
 * Define relative paths by [ DEFINE => [ ABSOLUTE_CONSTANT, 'relative/path' ] ]
 */

function helperPathDefineRelative( string|array $define, $constant = null, $path = null, $defines = [] )
{

  if ( is_string( $define ) && ! defined( $define ) && isset( $path ) ) {
    $value = helperPathAppend( constant( $constant ), $path );
    define( $define, $value );
    $defines[ $define ] = $value;
  } elseif ( is_array( $define ) ) {
    foreach ( $define as $name => $item ) {
      $arrayConstant = $item[0];
      $arrayPath = $item[1];
      $defines = helperPathDefineRelative( $name, $arrayConstant, $arrayPath, $defines );
    }
  } else {
    throw new Exception( 'code 3855' );
  }

  return $defines;

}

/**
 * Require absolute files
 */

function helperPathRequireAbsolute( string|array $file, $files = [] )
{

  if ( is_string( $file ) ) {
    if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
      throw new Exception( 'code 0985' );
    }
    if ( ! in_array( $file, get_included_files() ) ) {
      $files[ $file ] = require_once $file;
    }
  } elseif ( is_array( $file ) ) {
    foreach ( $file as $item ) {
      $files = helperPathRequireFileAbsolute( $item, $files );
    }
  }

  return $files;

}

/**
 * Require files by [ CONSTANT, relative/path.file ]
 */

function helperPathRequireRelative( string|array $constant, $file = null, $files = [] )
{

  if ( is_string( $constant ) && isset( $file ) ) {
    $constant = constant( $constant );
    $files = helperPathRequireAbsolute( helperPathAppend( $constant, $file ) );
  } elseif ( is_array( $constant ) ) {
    foreach ( $constant as $array ) {
      $pathConstant = $array[0];
      $filepath = $array[1];
      $files = helperPathRequireRelative( $pathConstant, $filepath );
    }
  } else {
    throw new Exception( 'code 1950' );
  }

  return $files;

}

function helperPathUpFind( $directory, array $targetDirectoryContains )
{

  $directory = (string) $directory;
  if ( ! is_string( $directory ) ) {
    throw new Exception( 'An error has occurred, please contact the administrator. search 4805' );
  }

  if ( is_file( $directory ) ) {
    $directory = dirname( $directory ) . DIRECTORY_SEPARATOR;
  }

  // no contents, no search
  if ( empty( $targetDirectoryContains ) ) {
    return false;
  }

  while( $directory && ( ! isset( $count ) || ! $count ) ) {

    $directory = rtrim( $directory, DIRECTORY_SEPARATOR . '\\/' ) . DIRECTORY_SEPARATOR;

    $is = [];

    // loop through 'contains'
    foreach ( $targetDirectoryContains as $contains ) {
      $item = $directory . $contains;

      // readable item?, add to $is
      if ( is_readable( $item ) ) {

        $is[] = $item;
 
      }
    }

    // expected versus is
    $isCount = count( $is );
    $containsCount = count( $targetDirectoryContains );

    $count = ( $isCount === $containsCount );

    if ( $count ) {

      break;
    } else {

      $parent = dirname( $directory );

      if ( $parent === $directory ) {

        // if root reached break the loop
        $directory = false;

      } else {

        // continue up
        $directory = $parent;

      }

      continue;
    }

  }

  $directory = rtrim( $directory, DIRECTORY_SEPARATOR . '\\/' );

  return $directory;

}

function helperPathNormalize( $path, $before = '', $after = '', $separator = DIRECTORY_SEPARATOR )
{

  $path = str_replace( [ '\\', '/' ], $separator, $path );

  if ( isset( $before ) ) {
    $path = ltrim( $path, $separator );
    if ( ! empty( $before ) ) {
      $before = $separator;
      $path = $before . $path;
    }
  }

  if ( isset( $after ) ) {
    $path = rtrim( $path, $separator );
    if ( ! empty( $after ) ) {
      $after = $separator;
      $path .= $after;
    }
  }

  return $path;

}

function helperPathAppend( $path, ...$append )
{

  $trim = '\\/';

  $path = rtrim( $path, $trim );

  foreach ( $append as $item ) {
    $path .= DIRECTORY_SEPARATOR . trim( $item, $trim );
  }

  return $path;

}

//////////////////////////////////////////////////

$pj = new ProjectHandler;
$pj();

return $pj;
