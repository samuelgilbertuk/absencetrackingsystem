<?php
session_start();
session_unset();
session_destroy();
include "sessionmanagement.php";

$connection = $GLOBALS["connection"];

//Check to see if database already exists. If it does we do not allow the user
//to access this page, and instead re-direct them to the login page.
if (mysqli_select_db($connection,'mydb')) 
{
    $totalEmployees = 0;
    $employeesWithNoMainVacation = 0;
    GetEmployeeCount($totalEmployees,$employeesWithNoMainVacation);
    
    if ($totalEmployees > 0)
    {
        header('Location: login.php');
        exit();
    }
}

if (!isset($_SESSION['StatusDiv']))
{
    $_SESSION['StatusDiv'] = "";
}

if (isset($_POST["submit"])) 
{
    ClearStatus();
    $name = $_POST["empName"];
    $email = $_POST["eMail"];
    $password = $_POST["password"];
    $dateJoined= $_POST["dateJoin"];
    $annualLeave = $_POST["annualLeave"];
    
    CreateNewDatabase();
    $success = CreateDefaultRecords($name,$email,$password,$dateJoined,$annualLeave);
    
    if ($success)
    {
        header('Location: login.php');
        exit();       
    }
}
    
?>

<!DOCTYPE html>
<html>
    <head>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <meta charset="UTF-8">
        <title>Absence Tracking System Setup</title>
    </head>
 
    <body>
        <?php include 'navbar.php'; ?>
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-2">
                    <h1 align="center">FIRST TIME SETUP</h1>
                    Welcome to the Absence Tracking System. To set up the system
                    we first need to create an administrator account.<br/><br/>
                    
                    Using the form below, please enter the email address and 
                    password for the person who will be the system administrator.
                    <br/><br/>
                    
                    <form method="POST">
                        <div class="input-group" for="empName">
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-user"></span>
                            </span>
                            <input type="text" class="form-control" placeholder="Name" 
                                   name="empName" id="empName" >
                        </div>

                        <div class="input-group" for="eMail">
                            <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-envelope"></span>
                            </span>
                            <input type="text" class="form-control" placeholder="Email" 
                                   name="eMail" id="eMail">
                        </div>


                        <div class="input-group" for="password">
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-lock"></span>
                            </span>
                            <input type="password" class="form-control" placeholder="Password" 
                                   name="password" id="password">
                        </div>

                        <div class="input-group" for=dateJoin">
                            <span class="input-group-addon"> Date Joined the Company
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                            <input type="date" class="form-control" name="dateJoin" 
                                id="dateJoin" placeholder="Date Joined">
                        </div>
                        <br/>

                        <label for="annualLeave">Annual Leave Entitlement</label>
                        <input type="range"  class= "form-control" name="annualLeave" 
                            min="10" max="28" value="19" step="1" 
                            oninput="updateAnnualLeave(value)"  id="annualLeave" /> 
                            <output for="minStaff" id="Leave">19</output>

                        <br/>
                             <input type="submit" 
                               class="btn btn-lg btn-primary btn-block btn-default" 
                               name="submit" id="submit" value="Create Administrator Account"/>
                    </form>
                </div>
             </div>     
        </div>

        <script>
            function updateAnnualLeave(level)
            {
                document.querySelector('#Leave').value = level;
            }                
        </script>

    </body>
</html>