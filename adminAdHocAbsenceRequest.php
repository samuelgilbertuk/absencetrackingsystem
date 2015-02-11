<?php
include 'sessionmanagement.php';

// If user is not an adminstrator or a manager redirect them back to the home 
// page.
if (!$isManager AND !$isAdministrator) {
    header('Location: index.php');
    exit();
}

// If user has clicked the submit button, try and create the ad hoc request in
// the database.
if (isset($_POST["submit"])) {
    ClearStatus();
    $employeeID = NULL;
    if (isset($_POST["employeeID"]))
    {
        $employeeID = $_POST["employeeID"];
    }
    
    $request = CreateAdHocAbsenceRequest($employeeID, 
                                         $_POST["startDate"], 
                                         $_POST["endDate"],
                                         $_POST["absenceType"]);
}

// If user has clicked the amend button, redirect them to the edit adhoc absence
// page, using a GET parameter with the ID of the record to edit.
if (isset($_POST["amend"])) {
    ClearStatus();
    $url = "Location:editAdHocAbsenceRequest.php?ID=".
            $_POST["amend"] . "&back=adminAdHocAbsenceRequest.php";
    header($url);
}

// If user has clicked the delete button, delete the record from the table.
if (isset($_POST["delete"])) {
    ClearStatus();
    DeleteAdHocAbsenceRequest($_POST["delete"]);
}

//-----------------------------------------------------------------------------
// This function will generate the HTML necessary for the Employee select
// dropdown.
//-----------------------------------------------------------------------------
function CreateEmployeeSelect()
{
    echo '<select class="form-control" name="employeeID" id="employeeID" >';
    echo '<option value="" disabled selected>Select Employee</option>';

    $employees = RetrieveEmployees();
    if ($employees <> NULL) 
    {
        foreach ($employees as $employee) 
        {
            echo '<option value="' . $employee[EMP_ID] . '">'.
                    $employee[EMP_NAME] . '</option>';
        }
    }
    echo '</select>';
}

//-----------------------------------------------------------------------------
// This function will generate the HTML necessary for the body of the 
// AbsenceType select dropdown.
//-----------------------------------------------------------------------------
function CreateAbsenceTypeSelect()
{
    $absenceTypes = RetrieveAbsenceTypes();
    if ($absenceTypes <> NULL) 
    {
        echo '<select class="form-control" name="absenceType">';
        foreach ($absenceTypes as $absenceType) 
        {
            echo '<option value="' . $absenceType[ABS_TYPE_ID] . '">'.
                    $absenceType[ABS_TYPE_NAME] . '</option>';
        }
    }
    echo '</select>';
} 

//-----------------------------------------------------------------------------
// This function will generate the HTML necessary for the body of the 
// AdHoc table.
//-----------------------------------------------------------------------------
function PopulateAdHocTable()
{
    $requests = RetrieveAdHocAbsenceRequests();
    if ($requests <> NULL) {
        foreach ($requests as $request) {
            $employeeID = $request[AD_HOC_EMP_ID];
            $employee = RetrieveEmployeeByID($employeeID);

            $absenceTypeID = $request[AD_HOC_ABSENCE_TYPE_ID];
            $absenceType = RetrieveAbsenceTypeByID($absenceTypeID);
            echo '<tr>';
            echo '<td>'.$employee[EMP_NAME].'</td>';
            echo '<td>'.$request[AD_HOC_START].'</td>';
            echo '<td>'.$request[AD_HOC_END].'</td>';
            echo '<td>'.$absenceType[ABS_TYPE_NAME].'</td>';
            echo '<td> <button class="btn btn-success" type="submit" name="amend"'.
                    'value="'.$request[AD_HOC_REQ_ID].'">Amend</button></td>';
            echo '<td> <button class="btn btn-danger" type="submit" name="delete"'.
                    'value="'.$request[AD_HOC_REQ_ID].'">Delete</button></td>';
            echo '</tr>';
        }
    } 
}?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Admin AdHoc Requests</title>
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
                    <h1>Create Ad Hoc Absence Request</h1>
                    <div class="input-group" for="employeeName">
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-user"></span>
                        </span>
                        <?php CreateEmployeeSelect(); ?>
                    </div>
                    <div class="input-group" for=startDate">
                        <span class="input-group-addon">Start Date&nbsp;
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                        <input type="date" class="form-control" name="startDate" 
                               id="startDate" placeholder="Start Date">
                    </div>  
                    <div class="input-group" for=endDate">
                        <span class="input-group-addon">Finish Date
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                        <input type="date" class="form-control" name="endDate" 
                               id="endDate" placeholder="End Date">
                    </div>
                    <br />
                    <label for="absenceType">Absence Type</label>
                    <?php CreateAbsenceTypeSelect(); ?>
                    <br/>
                    <input class="btn btn-success btn-block" type="submit" 
                           name="submit" id="submit" value="Add AdHoc Request"/>
                </div>
            </div>
        </form>

        <?php if ($isAdministrator) { ?>
        <div class="col-md-8 col-md-offset-2 text-center">
            <form method="post">
                <br/><br/><br/>
                <h1>Current Ad Hoc Absence Requests</h1>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Absence Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php PopulateAdHocTable(); ?> 
                    </tbody>
                </table>
            </form>
        </div>  
    <?php } ?>
    </body>
</html>