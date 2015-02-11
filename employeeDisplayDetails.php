<?php
include 'sessionmanagement.php';

if (isset($_POST["deleteApproved"])) {
    DeleteApprovedAbsenceBooking($_POST["deleteApproved"]);
}

if (isset($_POST["amendAdHoc"])) {   
    ClearStatus();
    $url = "Location:editAdHocAbsenceRequest.php?ID=".$_POST["amendAdHoc"].
           "&back=employeeDisplayDetails.php";   
    header($url);
}

if (isset($_POST["deleteAdHoc"])) 
{
    DeleteAdHocAbsenceRequest($_POST["deleteAdHoc"]);
}


if (isset($_POST["amendMain"])) {
    ClearStatus();
    $url = "Location:editMainRequest.php?ID=".$_POST["amendMain"].
           "&back=employeeDisplayDetails.php";   
    header($url);
}

if (isset($_POST["deleteMain"])) 
{
    DeleteMainVacationRequest($_POST["deleteMain"]);
}


function DisplayEmployeeDetailsListItems($userID)
{
    $employee = RetrieveEmployeeByID($userID);
    $companyRole = RetrieveCompanyRoleByID($employee[EMP_COMPANY_ROLE]);

    echo '<li class="list-group-item ">ID: '.
            $employee[EMP_ID].'</li>';
    echo '<li class="list-group-item ">Name: '.
            $employee[EMP_NAME].'</li>';
    echo '<li class="list-group-item ">Email: '.
            $employee[EMP_EMAIL].'</li>';
    echo '<li class="list-group-item ">Date Joined: '.
            $employee[EMP_DATEJOINED].'</li>';
    echo '<li class="list-group-item ">Company Role: '.
            $companyRole[COMP_ROLE_NAME].'</li>';
    echo '<li class="list-group-item ">Is Admin: '.
            $employee[EMP_ADMIN_PERM].'</li>';
    echo '<li class="list-group-item ">Is Manager: '.
            $employee[EMP_MANAGER_PERM].'</li>';
    echo '<li class="list-group-item ">Leave Entitlement: '.
            $employee[EMP_LEAVE_ENTITLEMENT].'</li>';
    echo '<li class="list-group-item ">Annual leave remaining:'.
            CalculateRemainingAnnualLeave($employee[EMP_ID]).'</li>';
}
 

function DisplayApproveAbsenceRequestsTableBody($userID)
{
    $filter[APPR_ABS_EMPLOYEE_ID] = $userID;
    $bookings = RetrieveApprovedAbsenceBookings($filter);

    if ($bookings <> NULL) {
        foreach ($bookings as $booking) {
            $absenceTypeID = $booking[APPR_ABS_ABS_TYPE_ID];
            $absenceType = RetrieveAbsenceTypeByID($absenceTypeID);
            echo '<tr>';
            echo '<td>'.$booking[APPR_ABS_START_DATE].'</td>';
            echo '<td>'.$booking[APPR_ABS_END_DATE].'</td>';
            echo '<td>'.$absenceType[ABS_TYPE_NAME].'</td>';
            echo '<td> <button class="btn btn-danger" type="submit" '.
                 'name="deleteApproved"  value="'.
                  $booking[APPR_ABS_BOOKING_ID].'">Delete</button></td>';
            echo '</tr>';
        }
    } 
}

function DisplayPendingAdHocRequestsTableBody($userID)
{
    $filter[AD_HOC_EMP_ID] = $userID;
    $adHocRequests = RetrieveAdHocAbsenceRequests($filter);

    if ($adHocRequests <> NULL) {
        foreach ($adHocRequests as $request) {
            $absenceTypeID = $request[AD_HOC_ABSENCE_TYPE_ID];
            $absenceType = RetrieveAbsenceTypeByID($absenceTypeID);
            echo '<tr>';
            echo '<td>'.$request[AD_HOC_START].'</td>';
            echo '<td>'.$request[AD_HOC_END].'</td>';
            echo '<td>'.$absenceType[ABS_TYPE_NAME].'</td>';
            echo '<td> <button class="btn btn-success" type="submit" '.
                 'name="amendAdHoc"  value="'.$request[AD_HOC_REQ_ID].'">Amend'.
                 '</button></td>';
            echo '<td> <button class="btn btn-danger" type="submit" '.
                 'name="deleteAdHoc"  value="'.$request[AD_HOC_REQ_ID].'">Delete'.
                 '</button></td>';
            echo '</tr>';
        }
    }
}
  


function DisplayMainVacationRequestTableBody($userID)
{ 
    $employee = RetrieveEmployeeByID($userID);
    $mainVacationRequest = RetrieveMainVacationRequestByID($employee[EMP_MAIN_VACATION_REQ_ID]);
    if ($mainVacationRequest <> NULL)
    {
        echo '<tr>';
        echo '<td>'.$mainVacationRequest[MAIN_VACATION_1ST_START].'</td>';
        echo '<td>'.$mainVacationRequest[MAIN_VACATION_1ST_END].'</td>';
        echo '<td>'.$mainVacationRequest[MAIN_VACATION_2ND_START].'</td>';
        echo '<td>'.$mainVacationRequest[MAIN_VACATION_2ND_END].'</td>';
        echo '<td> <button class="btn btn-success" type="submit" name="amendMain"'.
             'value="'.$mainVacationRequest[MAIN_VACATION_REQ_ID].'">Amend</button></td>';
        echo '<td> <button class="btn btn-danger" type="submit" name="deleteMain"'.
             'value="'.$mainVacationRequest[MAIN_VACATION_REQ_ID].'">Delete</button></td>';
        echo '</tr>';
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
        <title>Display Employee Details</title>

    </head>

    <body>
        <?php include 'navbar.php'; ?>
        
        <div class="col-md-4 col-md-offset-4 text-center">
            <h1>Employee Details</h1>
            <ul class="list-group ">
                <?php DisplayEmployeeDetailsListItems($userID); ?>
            </ul>
        </div>
       
        <form method="POST">
        <div class="row">
        <div class="col-md-8 col-md-offset-2 text-center">
        <table class="table table-bordered table-hover ">
            <h1> Approved Absence Requests </h1>
            <thead>
                <tr>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Absence Type</th>
                </tr>
            </thead>
            <tbody>
              <?php DisplayApproveAbsenceRequestsTableBody($userID); ?>
            </tbody>
        </div>
        </div>    
        </table>
        </form>

        <form method="POST">
        <div class="row">
        <div class="col-md-8 col-md-offset-2 text-center">
        <table class="table table-bordered table-hover">
            <h1>Pending AdHoc Requests</h1>
            <thead>
                <tr>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Absence Type</th>
                </tr>
            </thead>
            <tbody>
            <?php DisplayPendingAdHocRequestsTableBody($userID); ?>
            </tbody>
        </table> 
        </div>
        </div>
        </form>    
        <form method="POST">
        <div class="row">
        <div class="col-md-8 col-md-offset-2 tect-center">
            <h1>Pending Main Vacation Requests</h1>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>First Choice Start</th>
                        <th>First Choice End</th>
                        <th>Second Choice Start</th>
                        <th>Second Choice End</th>
                    </tr>
                </thead>
                <tbody>
                    <?php DisplayMainVacationRequestTableBody($userID); ?>
                </tbody>
            </table>
        </div>
        </div>
        </form>
      
    </body>
</html>