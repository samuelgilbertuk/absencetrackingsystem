<?php
include 'sessionmanagement.php';

// If user is not an adminstrator, redirect them back to the home page.
if (!$isAdministrator)
{
   header('Location: index.php');
   exit();
}

// If user has clicked the submit button, try and create the absence type.
if (isset($_POST["submit"])) {
    ClearStatus();
    
    $usesAnnualLeave = "0";
    if (isset($_POST["usesAnnualLeave"]))
    {
        $usesAnnualLeave = "1";
    }
    $canBeDenied = "0";
    if (isset($_POST["canBeDenied"]))
    {
        $canBeDenied = "1";
    }
    $record = CreateAbsenceType($_POST["absenceTypeName"], 
                                $usesAnnualLeave,
                                $canBeDenied);
}

// If user has clicked the amend button, redirect them to the edit absence
// type page, using a GET parameter with the ID of the record to edit.
if (isset($_POST["amend"])) { 
    ClearStatus();
    $url = "Location:editabsencetype.php?ID=".$_POST["amend"];   
    header($url);
}

// If user has clicked the delete button, delete the record from the table.
if (isset($_POST["delete"])) {
    ClearStatus();
    DeleteAbsenceType($_POST["delete"]);
}

//-----------------------------------------------------------------------------
// This function will generate the HTML necessary for the body of the 
// AbsenceType table.
//-----------------------------------------------------------------------------
function DisplayAbsenceTypeBody() 
{
    $absenceTypes = RetrieveAbsenceTypes();
    if ($absenceTypes <> NULL) 
    {
        foreach ($absenceTypes as $absenceType) 
        {
            echo "<tr>";
            echo "<td>".$absenceType[ABS_TYPE_NAME]."</td>";
            echo "<td>".$absenceType[ABS_TYPE_USES_LEAVE]."</td>";
            echo "<td>".$absenceType[ABS_TYPE_CAN_BE_DENIED]."</td>";
            echo '<td> <button class="btn btn-success" type="submit" '.
                 'name="amend"  value="'.$absenceType[ABS_TYPE_ID].
                 '">Amend</button></td>';
            echo '<td> <button class="btn btn-danger" type="submit" '.
                 'name="delete"  value="'.
                    $absenceType[ABS_TYPE_ID].'">Delete</button></td>';
            echo '</tr>';
        }
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Admin Absence Types</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    </head>
 
    <body>
        <?php include 'navbar.php'; ?>

        <form name="createAbsenceType" method="post">
            <div class="row">
                <div class="col-md-4 col-md-offset-4 text-center">
                    <h1>Create Absence Type</h1>
                    <div class="input-group" for="absenceTypeName">
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-user"></span>
                        </span>
                        <input type="text" class="form-control" 
                               placeholder="Absence Name" name="absenceTypeName" 
                               id="absenceTypeName">
                    </div>

                    <label for="usesAnnualLeave">Uses Annual Leave</label>
                    <input type="checkbox" name="usesAnnualLeave" 
                           id="usesAnnualLeave" /> 
                    
                    <label for="canBeDenied">&nbsp;&nbsp;Can Be Denied</label>
                    <input type="checkbox" name="canBeDenied" id="canBeDenied" /> 

                    <br /> <br />

                    <input class="btn btn-success btn-block" type="submit" 
                           name="submit" id="submit" value="Add Absence Type"/> 
                </div>
            </div>
        </form>

       <div class="col-md-8 col-md-offset-2 text-center">
            <form method="post" >
                <br/><br/><br/>
                <h1>Current Absence Types</h1>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Absence Type Name</th>
                            <th>Uses Annual Leave</th>
                            <th>Can Be Denied</th>
                            <th>Amend</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php DisplayAbsenceTypeBody(); ?>
                    </tbody>
                </table>
            </form>
        </div>
    </body>
</html>
