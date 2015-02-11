<?php
include 'sessionmanagement.php';  //sets $userID,$isAdministrator and $isManager

$employee = RetrieveEmployeeByID($userID);
$requestID = $employee[EMP_MAIN_VACATION_REQ_ID];

$today = date("Y-m-d");
$firstChoiceStart   = $today;
$firstChoiceEnd     = $today;
$secondChoiceStart  = $today;
$secondChoiceEnd    = $today;

if ( $requestID <> NULL)
{
    $mainVacationRequest = RetrieveMainVacationRequestByID($requestID);
    $firstChoiceStart    = $mainVacationRequest[MAIN_VACATION_1ST_START];
    $firstChoiceEnd      = $mainVacationRequest[MAIN_VACATION_1ST_END];
    $secondChoiceStart   = $mainVacationRequest[MAIN_VACATION_2ND_START];
    $secondChoiceEnd     = $mainVacationRequest[MAIN_VACATION_2ND_END];
}

if (isset($_POST["submit"])) 
{
    ClearStatus();
    $request = CreateMainVactionRequest($userID, 
                               $_POST["firstChoiceStart"],
                               $_POST["firstChoiceEnd"],
                               $_POST["secondChoiceStart"], 
                               $_POST["secondChoiceEnd"]);

    if ($request <> NULL)
    {
        $url = "Location:index.php";   
        header($url);
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
        <title>Employee Main Vacation Request</title>
        
    </head>
 
    <body>
          <?php include 'navbar.php'; ?>

        <form method="post">
            <div class="row">
            <div class="col-md-4 col-md-offset-4 text-center">
                
                <h1> Create Main Vacation Request </h1>
                
            <div class="input-group" for="firstChoiceStart">
                <span class="input-group-addon">1st Choice Start
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="date" class="form-control" name="firstChoiceStart"
                       id="firstChoiceStart" placeholder="First Choice Start">
            </div>
            
            <div class="input-group" for="firstChoiceEnd">
                <span class="input-group-addon">1st Choice Finish
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="date" class="form-control" name="firstChoiceEnd" 
                       id="firstChoiceEnd" placeholder="First Choice End">
            </div>
            
            <div class="input-group" for="secondChoiceStart">
                <span class="input-group-addon">2nd Choice Start
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="date" class="form-control" name="secondChoiceStart"
                       id="secondChoiceStart" placeholder="Second Choice Start">
            </div>
            
            <div class="input-group" for="secondChoiceEnd">
                <span class="input-group-addon">2nd Choice Finish
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="date" class="form-control" name="secondChoiceEnd" 
                       id="secondChoiceEnd" placeholder="Second Choice End">
            </div>
            <br/>
            <input class="btn btn-success btn-block" type="submit" name="submit" 
                   id="submit" value="Submit Main Vacation Request"/>
            </div>
            </div>
        </form>
    </body>
</html>
