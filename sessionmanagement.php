<?php
IF (!isset($_SESSION))
{
    session_start();
}

include 'databasefunctions.php';
include 'config.php';


$userID          = "";
$isAdministrator = false; 
$isManager       = false;    

//Create a connection to the database. This is a global variable and will 
//be accessed via $_GLOBALS["connection"]
if (!isset($GLOBALS["connection"]))
{
    $GLOBALS["connection"] = connectToSql($configDBHostName,
                                          $configDBUser,
                                          $configDBPassword);
}
$connection = $GLOBALS["connection"];

 if (basename($_SERVER['PHP_SELF']) != "setup.php")
{
    if (!mysqli_select_db($connection,'mydb')) 
    {
        header('Location: setup.php');
        exit();
    }
}

if (!isset($_SESSION['StatusDiv']))
{
    $_SESSION['StatusDiv'] = "";
}

$page = basename($_SERVER['PHP_SELF']); 
 if ( $page != "login.php" && $page!="setup.php" && $page != "test.php")
 {
    //----------------------------------------------------------------------------
    // If the session variables are not set, then the user is not logged in.
    // If this happens, redirect the user to the login page.
    //----------------------------------------------------------------------------
    if ( (!isset($_SESSION['userID'])) OR
         (!isset($_SESSION['administrator'])) OR
        (!isset($_SESSION['manager']) ))  
    {
        header('Location: login.php');
        exit();
    }
    else 
    {
        //Set up some local variables using the values of the session variables.
        //All of our web code can then refer to these variable names.
        $userID          = $_SESSION['userID'];
        $isAdministrator = $_SESSION['administrator']; 
        $isManager       = $_SESSION['manager'];    
    }
}
?>