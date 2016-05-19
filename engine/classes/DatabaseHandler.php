<?php
namespace ksf;
use \PDO;

// Class providing generic data access functionality
class DatabaseHandler
{
  // Hold an instance of the PDO class
  private static $_mHandler;

  // Private constructor to prevent direct creation of object
  private function __construct()
  {
  }

  // Return an initialized database handler 
  private static function GetHandler()
  {
    // Create a database connection only if one doesn’t already exist
    if (!isset(self::$_mHandler))
    {
      // Execute code catching potential exceptions
      try
      {
        $system = System::getInstance();
        // Create a new PDO class instance
        self::$_mHandler =
          new PDO('mysql:unix_socket=/var/run/mysqld/mysqld_aux1.socket;host=' . $system->getParameter('DBServer') . ';dbname=' . $system->getParameter('DBName'), $system->getParameter('DBUser'), $system->getParameter('DBPassword'),
                  array(PDO::ATTR_PERSISTENT => false));
       
        unset($system);
        
        self::$_mHandler->exec("set names utf8"); // set default charset

        // Configure PDO to throw exceptions
        self::$_mHandler->setAttribute(PDO::ATTR_ERRMODE,
                                       PDO::ERRMODE_EXCEPTION);
      }
      catch (PDOException $e)
      {
        // Close the database handler and trigger an error
        self::Close();
        trigger_error($e->getMessage(), E_USER_ERROR);
      }
    }

    // Return the database handler
    return self::$_mHandler;
  }

  // Clear the PDO class instance
  public static function Close()
  {
    self::$_mHandler = null;
  }

  // Wrapper method for PDOStatement::execute()
  public static function Execute($sqlQuery, $params = null)
  {
    // Try to execute an SQL query or a stored procedure
    try
    {
      // Get the database handler
      $database_handler = self::GetHandler();

      // Prepare the query for execution
      $statement_handler = $database_handler->prepare($sqlQuery);

      // Execute query
      $statement_handler->execute($params);
    
      return true;
    }
    // Trigger an error if an exception was thrown when executing the SQL query
    catch(PDOException $e)
    {
      // Close the database handler and trigger an error
      self::Close();
      trigger_error($e->getMessage(), E_USER_ERROR);
    }
  }

  // Wrapper method for PDOStatement::fetchAll()
  public static function GetAll($sqlQuery, $params = null,
                                $fetchStyle = PDO::FETCH_ASSOC)
  {
    // Initialize the return value to null
    $result = null;

    // Try to execute an SQL query or a stored procedure
    try
    {
      // Get the database handler
      $database_handler = self::GetHandler();

      // Prepare the query for execution
      $statement_handler = $database_handler->prepare($sqlQuery);

      // Execute the query
      $statement_handler->execute($params);
      
      // Fetch result
      $result = $statement_handler->fetchAll($fetchStyle);
    }
    // Trigger an error if an exception was thrown when executing the SQL query
    catch(PDOException $e)
    {
      // Close the database handler and trigger an error
      self::Close();
      trigger_error($e->getMessage(), E_USER_ERROR);
    }

    // Return the query results
    return $result;
  }

  // Wrapper method for PDOStatement::fetch()
  public static function GetRow($sqlQuery, $params = null,
                                $fetchStyle = PDO::FETCH_ASSOC)
  {
    // Initialize the return value to null
    $result = null;

    // Try to execute an SQL query or a stored procedure
    try
    {
      // Get the database handler
      $database_handler = self::GetHandler();

      // Prepare the query for execution
      $statement_handler = $database_handler->prepare($sqlQuery);

      // Execute the query
      $statement_handler->execute($params);

      // Fetch result
      $result = $statement_handler->fetch($fetchStyle);
    }
    // Trigger an error if an exception was thrown when executing the SQL query
    catch(PDOException $e)
    {
      // Close the database handler and trigger an error
      self::Close();
      trigger_error($e->getMessage(), E_USER_ERROR);
    }

    // Return the query results
    return $result;
  }

  // Return the first column value from a row
  public static function GetOne($sqlQuery, $params = null)
  {
    // Initialize the return value to null    
    $result = null;

    // Try to execute an SQL query or a stored procedure
    try
    {
      // Get the database handler
      $database_handler = self::GetHandler();

      // Prepare the query for execution
      $statement_handler = $database_handler->prepare($sqlQuery);

      // Execute the query
      $statement_handler->execute($params);

      // Fetch result
      $result = $statement_handler->fetch(PDO::FETCH_NUM);

      /* Save the first value of the result set (first column of the first row)
         to $result */
      $result = $result[0];
    }
    // Trigger an error if an exception was thrown when executing the SQL query
    catch(PDOException $e)
    {
      // Close the database handler and trigger an error
      self::Close();
      trigger_error($e->getMessage(), E_USER_ERROR);
    }

    // Return the query results
    return $result;
  }
    
  public static function lastInsertId() {
    try
    {
        return self::$_mHandler->lastInsertId();
    }
    // Trigger an error if an exception was thrown when executing the SQL query
    catch(PDOException $e)
    {
      // Close the database handler and trigger an error
      self::Close();
      trigger_error($e->getMessage(), E_USER_ERROR);
    }
  }
}
?>
