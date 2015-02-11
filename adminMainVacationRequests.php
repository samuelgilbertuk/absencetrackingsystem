<?php
include 'sessionmanagement.php';

if (!$isAdministrator)
{
   header('Location: index.php');
   exit();
}

if (isset($_POST["submit"])) 
{
    ClearStatus();
    $request = CreateMainVactionRequest($_POST["employeeID"], 
                               $_POST["firstChoiceStart"],
                               $_POST["firstChoiceEnd"],
                               $_POST["secondChoiceStart"], 
                               $_POST["secondChoiceEnd"]);
}


if (isset($_POST["amend"])) {   
    ClearStatus();
    $url = "Location:editMainRequest.php?ID=".$_POST["amend"].
           "&back=adminMainVacationRequests.php";   
    header($url);
}

if (isset($_POST["delete"])) 
{
    ClearStatus();
    DeleteMainVacationRequest($_POST["delete"]);
}

function PopulateTableBody()
{
    $requests = RetrieveMainVacationRequests();
    if ($requests <> NULL)
    {
        foreach ($requests as $request) { 
            $employee = RetrieveEmployeeByID($request[MAIN_VACATION_EMP_ID]);
            echo "<tr>";
            echo "<td>".$employee[EMP_NAME]."</td>";
            echo "<td>".$request[MAIN_VACATION_1ST_START]."</td>";
            echo "<td>".$request[MAIN_VACATION_1ST_END]."</td>";
            echo "<td>".$request[MAIN_VACATION_2ND_START]."</td>";
            echo "<td>".$request[MAIN_VACATION_2ND_END]."</td>";
            echo '<td> <button class="btn btn-success" type="submit" '.
                 'name="amend"  value="'.$request[MAIN_VACATION_REQ_ID].
                 '">Amend</button></td>';
            echo '<td> <button class="btn btn-danger" type="submit" name="delete"'.
                 ' value="'.$request[MAIN_VACATION_REQ_ID].'">Delete</button></td>';
            echo "</tr>";
        }
    } 
}

function CreateEmployeeSelect()
{
    echo '<select class="form-control" name="employeeID" id="employeeID" >';

    $employees = RetrieveEmployees();
    if ($employees <> NULL) 
    {
        foreach ($employees as $employee) 
        {
            echo '<option value="' . $employee[EMP_ID] . '">' . $employee[EMP_NAME] . '</option>';
        }
    }
    echo '</select>';
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
        <title>Admin Main Vacation Requests</title>
    </head>
 
    <body>
        <?php include 'navbar.php'; ?>

        <form method="post">
           <div class="row">
            <div class="col-md-4 col-md-offset-4 text-center">    
            <h1>Create Main Vacation Request</h1>
            <div class="input-group" for="empName">
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-user"></span>
                </span>
                <?php CreateEmployeeSelect(); ?>
            </div>
            <br/>
            
            <div class="input-group" for="firstChoiceStart">
                <span class="input-group-addon">1st Choice Start Date&nbsp;
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="date" class="form-control" name="firstChoiceStart" 
                       id="firstChoiceStart" placeholder="First Choice Start Date">
            </div>
            
            <div class="input-group" for="firstChoiceEnd">
                <span class="input-group-addon">1st Choice Finish Date
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="date" class="form-control" name="firstChoiceEnd" 
                       id="firstChoiceEnd" placeholder="First Choice End Date">
            </div>
            
            <div class="input-group" for="secondChoiceStart">
                <span class="input-group-addon">2nd Choice Start Date&nbsp;
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="date" class="form-control" name="secondChoiceStart" 
                       id="secondChoiceStart" placeholder="Second Choice Start Date">
            </div>
            
            <div class="input-group" for="secondChoiceEnd">
                <span class="input-group-addon">1st Choice Finish Date
                    <span class="glyphicon glyphicon-calendar"></span>
                  </span>
                  <input type="date" class="form-control" name="secondChoiceEnd" 
                         id="secondChoiceEnd" placeholder="Second Choice End Date">
            </div>
            
            <br/>
            
            <input class="btn btn-success btn-block" type="submit" name="submit" 
                   id="submit" value="Add Main Vacation Request"/>
            </div>
            </div>
        </form>
        
            <div id="table" class="table-responsive text-center">
                <br/><br/><br/>
                <h1>Current Main Vacation Request</h1>

            <form method="post">
                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>First Choice Start Date</th>
                        <th>First Choice End Date</th>
                        <th>Second Choice Start Date</th>
                        <th>Second Choice End Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php PopulateTableBody(); ?>
                </tbody>
            </table>
            </form>
        </div>  
    </body>
</html>

