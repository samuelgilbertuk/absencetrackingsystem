<?php
include 'sessionmanagement.php';

$returnURL = "index.php";
if (isset($_GET["back"]))
{
    $returnURL = $_GET["back"];
}

if ($_GET["ID"] <> NULL)
{
    $record = RetrieveMainVacationRequestByID($_GET["ID"]);
    
    if (!$isAdministrator)
    {
        if ($record[MAIN_VACATION_EMP_ID] <> $userID)
        {
            header('Location: index.php');
            exit();
        }
    }
    $employee = RetrieveEmployeeByID($record[MAIN_VACATION_EMP_ID]);

}

if (isset($_POST["cancel"])) {   
    ClearStatus();
    header("Location:".$returnURL);
    exit();
}

if (isset($_POST["update"])) {
    ClearStatus();
    $record[MAIN_VACATION_REQ_ID]       =   $_GET["ID"];
    $record[MAIN_VACATION_EMP_ID]       =   $employee[EMP_ID];
    $record[MAIN_VACATION_1ST_START]    =   $_POST["firstChoiceStart"];
    $record[MAIN_VACATION_1ST_END]      =   $_POST["firstChoiceEnd"];
    $record[MAIN_VACATION_2ND_START]    =   $_POST["secondChoiceStart"];
    $record[MAIN_VACATION_2ND_END]      =   $_POST["secondChoiceEnd"];
    $success = UpdateMainVacactionRequest($record);
    
    if ($success)
    {
        header("Location:".$returnURL);
        exit();
    }
}

if (isset($_POST["delete"])) {
    ClearStatus();
    DeleteMainVacationRequest($_POST["delete"]);
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
        <title>Admin Employees</title>
    </head>
 
    <body>
        <?php include 'navbar.php'; ?>
      <div class="row">
       <div class="col-md-4 col-md-offset-4 text-center">
        <form method="post" class="signUp">
            <h1> Edit Main Request </h1>
            <div class="input-group"  for="empName">
            <span class="input-group-addon">
                <span class="glyphicon glyphicon-user"></span>
            </span>    
            <?php 
                $employee = RetrieveEmployeeByID($record[MAIN_VACATION_EMP_ID]);
                if ($employee <> NULL)
                {
                  echo '<input type="text" class="form-control" name="empID" '
                    . 'id="empID" readonly value="'.$employee[EMP_NAME].'"/>';
                }
            ?> 
           </div>
           
            <div class="input-group" for="firstChoiceStart">
                <span class="input-group-addon">1st Choice Start
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="date" class="form-control"  
                       name="firstChoiceStart" id="firstChoiceStart" 
                       value="<?php echo $record[MAIN_VACATION_1ST_START]; ?>">
            </div>
               
            
            <div class="input-group" for="firstChoiceEnd">
                <span class="input-group-addon">1st Choice Finish
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="date" class="form-control"  
                       name="firstChoiceEnd" id="firstChoiceEnd" 
                       value="<?php echo $record[MAIN_VACATION_1ST_END]; ?>">
            </div>
                
            <div class="input-group" for="secondChoiceStart">
                <span class="input-group-addon">2nd Choice Start
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="date" class="form-control"  
                       name="secondChoiceStart" id="secondChoiceStart" 
                       value="<?php echo $record[MAIN_VACATION_2ND_START]; ?>">
            </div>
                
            
            <div class="input-group" for="secondChoiceEnd">
                <span class="input-group-addon">2nd Choice Finish
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="date" class="form-control"  
                       name="secondChoiceEnd" id="secondChoicEnd" 
                       value="<?php echo $record[MAIN_VACATION_2ND_END]; ?>">
            </div>
               
            <br />

            <input class="btn btn-success btn-block" type="submit" 
                    name="update" id="submit" value="Update"/> 
            <input class="btn btn-danger btn-block" type="submit" 
                    name="cancel" id="cancel" value="Cancel"/> 
        </form>
       </div> 
      </div>  
    </body>
</html>