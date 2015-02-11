<?php
include "sessionmanagement.php";
ClearStatus();

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
        <title>Administration</title>
    </head>
 
    <body>
        <?php include 'navbar.php'; ?>

        <?php if ($isAdministrator) { ?>
        
        <div class="col-md-4 col-md-offset-4 text-center">
            <h1>Administrator Functions</h1>
            <ul class="list-group ">
                <li class="list-group-item ">
                    <a href="adminCompanyRoles.php">Admin Company Roles</a>
                </li>
                <li class="list-group-item ">
                    <a href="adminEmployeeTable.php">Admin Employees</a>
                </li>
                <li class="list-group-item ">
                    <a href="adminMainVacationRequests.php">Admin Main Vacation Requests</a>
                </li>
                <li class="list-group-item ">
                    <a href="adminAbsenceTypes.php">Admin Absence Types</a>
                </li>
                <li class="list-group-item ">
                    <a href="adminAdHocAbsenceRequest.php">Admin Ad Hoc Requests</a>
                </li>
                <li class="list-group-item ">
                    <a href="adminApprovedAbsenceBookings.php">Admin Approved Absence Bookings</a>
                </li>
                <li class="list-group-item ">
                    <a href="adminApprovedAbsenceBookingDate.php">Admin Approved Absence Booking Dates</a>
                </li>
                <li class="list-group-item ">
                    <a href="adminDates.php">Admin Dates</a>
                </li>
                <li class="list-group-item ">
                    <a href="adminPublicHolidays.php">Admin Public Holidays</a>
                </li>
                <li class="list-group-item ">
                    <a href="administerVacation.php">Admin Vacation</a>
                </li>
            </ul>
        </div>
        <?php } ?>
        
        
        <?php if ($isManager) { ?>
        <div class="col-md-4 col-md-offset-4 text-center">
            <h1>Office Manager  Functions</h1>
            <ul class="list-group ">
                <li class="list-group-item ">
                    <a href="managerViewApprovedRequests.php">View approved absence bookings</a>
                </li>
                <li class="list-group-item ">
                    <a href="adminAdHocAbsenceRequest.php">Create AdHoc request for staff member</a>
                </li>
            </ul>
        </div>    
        <?php } ?>
        
        
        <div class="col-md-4 col-md-offset-4 text-center">
            <h1>Employee Functions</h1>
            <ul class="list-group">
                <li class="list-group-item ">
                    <a href="employeeMainVacationRequest.php">Main Vacation Request</a>
                </li>
                <li class="list-group-item ">
                    <a href="employeeDisplayDetails.php">Display Details</a>
                </li>
                <li class="list-group-item ">
                    <a href="employeeAdHocRequests.php">Ad Hoc Requests</a>
                </li>
            </ul>
        </div>
    </body>
</html>