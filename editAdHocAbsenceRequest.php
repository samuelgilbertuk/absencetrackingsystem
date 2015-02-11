<?php
include 'sessionmanagement.php';

$returnURL = "index.php";
if (isset($_GET["back"]))
{
    $returnURL = $_GET["back"];
}

if ($_GET["ID"] <> NULL)
{
    $request = RetrieveAdHocAbsenceRequestByID($_GET["ID"]);
    $Employee = RetrieveEmployeeByID($request[AD_HOC_EMP_ID]);
    
    if (!$isAdministrator)
    {
        if ($request[AD_HOC_EMP_ID] <> $userID)
        {
            //--------------------------------------------------------------
            // The user is not an administrator, but is attempting to 
            // edit an approved absence request for another user. This is
            // not allowed, and should not happen. If it does, we redirect
            // the user back to the index.
            //--------------------------------------------------------------
            header('Location: index.php');
            exit();
        }
    }
}

if (isset($_POST["cancel"])) { 
    ClearStatus();
    header("location:".$returnURL);
    exit;
}

if (isset($_POST["update"])) {
    ClearStatus();
    $request[AD_HOC_REQ_ID]          =  $_GET["ID"];
    $request[AD_HOC_START]           =   $_POST["startDate"];
    $request[AD_HOC_END]             =   $_POST["endDate"];
    $request[AD_HOC_ABSENCE_TYPE_ID] =   $_POST["absenceType"];
    $success = UpdateAdHocAbsenceRequest($request);

    if ($success)
    {
        header("location:".$returnURL);
        exit;
    }
}



function CreateAbsenceTypeSelect($absenceIDToSelect)
{
    $absenceTypes = RetrieveAbsenceTypes();
    if ($absenceTypes <> NULL)
    {
        echo '<select class="form-control" name="absenceType">';
                
        foreach ($absenceTypes as $absenceType)
        {
            if ($absenceType[ABS_TYPE_ID]== $absenceIDToSelect)
            {
                echo '<option selected="selected" value="'.
                        $absenceType[ABS_TYPE_ID].'">'.
                        $absenceType[ABS_TYPE_NAME].'</option>';                       
            }
            else                      
            {
                echo '<option value="'.$absenceType[ABS_TYPE_ID].'">'.
                        $absenceType[ABS_TYPE_NAME].'</option>';
            }
        }
        echo '</select>';
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Admin Ad Hoc Absence Requests</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    </head>
    <body>
        <?php include 'navbar.php'; ?>
        
        
        <form method="post" class="signUp">
            <div class="row">
            <div class="col-md-4 col-md-offset-4 text-center"> 
                <h1> Edit Ad Hoc Request </h1>
            
            <div class="input-group"  for="empID">
            <span class="input-group-addon">
                <span class="glyphicon glyphicon-user"></span>
            </span>    
            <?php 
                if ($Employee <> NULL)
                {
                  echo '<input type="text" class="form-control" name="empID" '.
                       'id="empID" readonly value="'.$Employee[EMP_NAME].'"/>';
                }
            ?> 
           </div>
            
            <div class="input-group" for="startDate">
		<span class="input-group-addon">Start&nbsp;
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>	
  		<input type="date" class="form-control" name="startDate" 
                       id="startDate" value="<?php echo $request[AD_HOC_START]?>">
            </div>
  
            
            <div class="input-group" for="endDate">
		<span class="input-group-addon">Finish
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>	
  		<input type="date" class="form-control" name="endDate" 
                       id="endDate" value="<?php echo $request[AD_HOC_END]?>">   
            </div>
                
            <br/>                
            <p class="text-center">
            <label for="absenceType">Absence Type</label>
            <?php CreateAbsenceTypeSelect($request[AD_HOC_ABSENCE_TYPE_ID]); ?> 
            </p>
            <br />
            
            <input class="btn btn-success btn-block" type="submit" name="update" 
                   id="submit" value="Edit Request"/>
            <input class="btn btn-danger btn-block" type="submit" name="cancel" 
                   id="cancel" value="Cancel"/>
        </form>
    </body>
</html>