<?php
include 'sessionmanagement.php';

// If user is not an adminstrator, redirect them back to the home page.
if (!$isAdministrator) {
    header('Location: index.php');
    exit();
}

// If user has clicked the submit button, try and create the approved absence 
// booking
if (isset($_POST["submit"])) {
    ClearStatus();
    $employeeID = NULL;
    
    if (isset($_POST["employeeID"]))
    {
        $employeeID = $_POST["employeeID"];
    }
    $booking = CreateApprovedAbsenceBooking($employeeID, $_POST["startDate"],
                                       $_POST["endDate"], $_POST["absenceType"]);
}

// If user has clicked the amend button, redirect them to the edit approvced
// booking page, using a GET parameter with the ID of the record to edit.
if (isset($_POST["amend"])) {
    ClearStatus();
    $url = "Location:editApprovedAbsenceBooking.php?ID=" . $_POST["amend"];
    header($url);
}

// If user has clicked the delete button, delete the record from the table.
if (isset($_POST["delete"])) {
    ClearStatus();
    DeleteApprovedAbsenceBooking($_POST["delete"]);
}

//-----------------------------------------------------------------------------
// This function will generate the HTML necessary for the employee select
// drop down HTML element
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
// This function will generate the HTML necessary for the absence type select
// drop down HTML element
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
// Approved absence table
//-----------------------------------------------------------------------------
function DisplayApproveAbsenceTableBody()
{
    $bookings = RetrieveApprovedAbsenceBookings();

    if ($bookings <> NULL) 
    {
        foreach ($bookings as $booking) 
        {
            $employeeID = $booking[APPR_ABS_EMPLOYEE_ID];
            $employee = RetrieveEmployeeByID($employeeID);

            $absenceTypeID = $booking[APPR_ABS_ABS_TYPE_ID];
            $absenceType = RetrieveAbsenceTypeByID($absenceTypeID);
            echo "<tr>";
            echo "<td>".$employee[EMP_NAME]."</td>";
            echo "<td>".$booking[APPR_ABS_START_DATE]."</td>";
            echo "<td>".$booking[APPR_ABS_END_DATE]."</td>";
            echo "<td>".$absenceType[ABS_TYPE_NAME]."</td>";
            echo '<td> <button class="btn btn-success" type="submit" name="amend"'.
                 'value="'.$booking[APPR_ABS_BOOKING_ID].'">Amend</button></td>';
            echo '<td> <button class="btn btn-danger" type="submit" name="delete"'.
                 'value="'.$booking[APPR_ABS_BOOKING_ID].'">Delete</button></td>';
            echo "</tr>";
        }
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Admin Approved Absence Booking</title>
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
                    <h1>Create Approved Absence Booking</h1>

                    <div class="input-group" for="employeeid">
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-user"></span>
                        </span>
                        <?php CreateEmployeeSelect(); ?>
                    </div>
                    <div class="input-group" for="startDate">
                        <span class="input-group-addon">Start Date&nbsp;
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>	
                        <input type="date" class="form-control" name="startDate"
                               id="startDate" placeholder="Start Date">
                    </div>
                    <div class="input-group" for="endDate">
                        <span class="input-group-addon">Finish Date
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>	
                        <input type="date" class="form-control" name="endDate" 
                               id="endDate" placeholder="End Date">
                    </div>
                    <br />
                    <label for="absenceType">Absence Type</label>
                    <?php CreateAbsenceTypeSelect(); ?>

                    <input class="btn btn-success btn-block" type="submit" 
                           name="submit" id="submit" value="Create Absence Booking"/>
                </div>
            </div>
        </form>

        <div id="table">
            <div class="row">
                <div class="col-md-8 col-md-offset-2 text-center">
                    <br/><br/><br/>
                    <h1>Current Approved Absence Bookings</h1>

                    <form method="post">
                        <table class="table table-bordered table-hover ">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Absence Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php DisplayApproveAbsenceTableBody(); ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>