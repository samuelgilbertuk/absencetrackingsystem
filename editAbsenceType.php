<?php
include 'sessionmanagement.php';

if (!$isAdministrator)
{
   header('Location: index.php');
   exit();
}

if ($_GET["ID"] <> NULL)
{
    $absenceType = RetrieveAbsenceTypeByID($_GET["ID"]);
    $usesLeave = false;
    if ($absenceType[ABS_TYPE_USES_LEAVE] == 1)
    {
        $usesLeave = true;
    }

    $canBeDenied = false;
    if ($absenceType[ABS_TYPE_CAN_BE_DENIED] == 1)
    {
        $canBeDenied = true;
    }
}

if (isset($_POST["cancel"])) {   
    $url = "Location:adminAbsenceTypes.php";   
    header($url);
}

if (isset($_POST["update"])) {
    $absenceType[ABS_TYPE_ID]       =   $_GET["ID"];
    $absenceType[ABS_TYPE_NAME]     =   $_POST["name"];
    
    $usesLeave = "0";
    if (isset($_POST["usesLeave"]))
    {
        $usesLeave = "1";
    }
    $canBeDenied = "0";
    if (isset($_POST["canBeDenied"]))
    {
        $canBeDenied = "1";
    }
    
    
    
    $absenceType[ABS_TYPE_USES_LEAVE]= $usesLeave;
    $absenceType[ABS_TYPE_CAN_BE_DENIED]= $canBeDenied;
    
    $success = UpdateAbsenceType($absenceType);

    if ($success)
    {
        $url = "Location:adminAbsenceTypes.php";   
        header($url);
    }   
}   
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Amend Absence Type</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    </head>
 
    <body>
        <?php include 'navbar.php'; ?>
      
        <form method="post">
            <div class="row">
                <div class="col-md-4 col-md-offset-4 text-center">
                    <h1> Edit Absence Type </h1>
            <div class="input-group" for="name">
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-user"></span>
                </span>
                <input type="text" class="form-control" name="name" id="name" 
                       value="<?php echo $absenceType[ABS_TYPE_NAME];?>">
	    </div>

            <label for="usesLeave">Uses Annual Leave</label>
            <input  type="checkbox" name="usesLeave" id="usesLeave" 
                   <?php if ($usesLeave) {echo 'checked="true"';} ?>/>

            <label for="canBeDenied">Can Be Denied</label>
            <input type="checkbox" name="canBeDenied" id="canBeDenied"
                      <?php if ($canBeDenied) {echo 'checked="true"';} ?>/>
            
            <br/> <br/>
            
            <input class="btn btn-success btn-block" type="submit" name="update" 
                   id="submit" value="Update"/> 
            <input class="btn btn-success btn-block" type="submit" name="cancel" 
                   id="cancel" value="Cancel"/> 
                </div>
            </div>
        </form>

    </body>
</html>