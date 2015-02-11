<?php
include 'sessionmanagement.php';

if (!$isAdministrator)
{
   header('Location: index.php');
   exit();
}

$totalEmployees = 0;
$employeesWithNoMainVacation = 0;
$result = GetEmployeeCount($totalEmployees,$employeesWithNoMainVacation); 

if (isset($_POST["processmainrequests"])) 
{
        ClearStatus();
        $succeeded = false;
        $statusMessage = "";
        
	$result = processMainVacationRequests($statusMessage);
        if ($result ==NULL)
        {
            $succeeded = true;
        }
        GenerateStatus($succeeded, $statusMessage);
}

if (isset($_POST["processadhocrequests"])) 
{
        ClearStatus();
	$succeeded = processAdHocRequests($statusMessage);
        GenerateStatus($succeeded, $statusMessage);
}

if (isset($_POST["approve1st"])) { 
    ClearStatus();
    $requestID = $_POST["approve1st"];
    ApproveMainVacationRequest($requestID,true);
}

if (isset($_POST["approve2nd"])) {  
    ClearStatus();
    $requestID = $_POST["approve2nd"];
    ApproveMainVacationRequest($requestID,false);
}

if (isset($_POST["reject"])) 
{
    ClearStatus();
    $ID = $_POST["reject"];
    DeleteMainVacationRequest($ID);
}

if (isset($_POST["approveadhoc"])) {   
    $ID = $_POST["approveadhoc"];
    
    ClearStatus();
    $request  = RetrieveAdHocAbsenceRequestByID($ID);
    $startDate = $request[AD_HOC_START];
    $endDate   = $request[AD_HOC_END];
    $absenceTypeID = $request[AD_HOC_ABSENCE_TYPE_ID];
    
    $success = CreateApprovedAbsenceBooking($request[AD_HOC_EMP_ID],
                                            $startDate,
                                            $endDate,
                                            $absenceTypeID);
    
    if ($success)
    {
        DeleteAdHocAbsenceRequest($ID);
    }
    
}

if (isset($_POST["rejectadhoc"])) 
{
    ClearStatus();
    $ID = $_POST["reject"];
    DeleteAdHocAbsenceRequest($ID);
}

function ApproveMainVacationRequest($requestID,$useFirst)
{
    $statusMessage = "";
    $succeeded = true;
    $absenceType = GetAnnualLeaveAbsenceTypeID();
    
    $request = RetrieveMainVacationRequestByID($requestID);
    if ($request <> NULL)
    {
        $start = $request[MAIN_VACATION_1ST_START];
        $end = $request[MAIN_VACATION_1ST_END];
        
        if (!$useFirst)
        {
          $start = $request[MAIN_VACATION_2ND_START];
          $end = $request[MAIN_VACATION_2ND_END];          
        }
        $succeeded = ProcessAbsenceRequest($request[MAIN_VACATION_EMP_ID],
                                        $start,$end,$absenceType,$statusMessage);
        if ($succeeded)
        {
            DeleteMainVacationRequest($requestID);
        }
     }
    else
    {
        $statusMessage .= "Error: Unable to process your request.".
                          "The MainVacationRequest ID of $requestID ".
                          "could not be found in the database. Please ".
                           "contact your system administrator.</br>";
        $succeeded = false;;
    }
    
    GenerateStatus($succeeded, $statusMessage);
}

function ApproveAdHocRequest($requestID)
{
    $statusMessage = "";
    $succeeded = true;
 
    $request = RetrieveAdHocAbsenceRequestByID($requestID);
    if ($request <> NULL)
    {
        $absenceType = $request[AD_HOC_ABSENCE_TYPE_ID];
        $start = $request[AD_HOC_START];
        $end = $request[AD_HOC_END];
        
        $succeeded = ProcessAbsenceRequest($request[AD_HOC_EMP_ID],
                                        $start,$end,$absenceType,$statusMessage);
        if ($succeeded)
        {
            DeleteAdHocAbsenceRequest($requestID);
        }
     }
    else
    {
        $statusMessage .= "Error: Unable to process your request.".
                          "The AdHoc Request ID of $requestID ".
                          "could not be found in the database. Please ".
                           "contact your system administrator.</br>";
        $succeeded = false;;
    }
    
    GenerateStatus($succeeded, $statusMessage);
}

function DisplayMainVacationTableBody()
{
    $requests = RetrieveMainVacationRequests();
    if ($requests <> NULL)
    {
        foreach ($requests as $request) 
        { 
            $employee = RetrieveEmployeeByID($request[MAIN_VACATION_EMP_ID]);
            echo "<tr>";
            echo "<td>".$employee[EMP_NAME]."</td>";
            echo "<td>".$request[MAIN_VACATION_1ST_START]."</td>";
            echo "<td>".$request[MAIN_VACATION_1ST_END]."</td>";
            echo "<td>".$request[MAIN_VACATION_2ND_START]."</td>";
            echo "<td>".$request[MAIN_VACATION_2ND_END]."</td>";
            echo '<td> <button class="btn btn-success" type="submit" '.
                 'name="approve1st"  value="'.$request[MAIN_VACATION_REQ_ID].
                 '">Approve 1st Choice</button></td>';
            echo '<td> <button class="btn btn-success" type="submit" '.
                 'name="approve2nd"  value="'.$request[MAIN_VACATION_REQ_ID].
                    '">Approve 2nd Choice</button></td>';
            echo '<td> <button class="btn btn-danger" type="submit" name="reject"'.
                 ' value="'.$request[MAIN_VACATION_REQ_ID].'">Reject</button></td>';
            echo '</tr>';
        }
    } 
}

function DisplayAdHocRequestTableBody()
{
    $requests = RetrieveAdHocAbsenceRequests();
    if ($requests <> NULL)
    {
        foreach ($requests as $request) 
        { 
            $employee = RetrieveEmployeeByID($request[AD_HOC_EMP_ID]);
            echo "<tr>";
            echo "<td>".$employee[EMP_NAME]."</td>";
            echo "<td>".$request[AD_HOC_START]."</td>";
            echo "<td>".$request[AD_HOC_END]."</td>";
            echo '<td> <button class="btn btn-success" type="submit" '.
                 'name="approveadhoc"  value="'.$request[AD_HOC_REQ_ID].
                 '">Approve</button></td>';
            echo '<td> <button class="btn btn-danger" type="submit" '.
                 'name="rejectadhoc"  value="'.$request[AD_HOC_REQ_ID].
                 '">Reject</button></td>';
            echo "</tr>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Administer Vacations</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    </head>
 
    <body>
        <?php include "navbar.php"; ?>
        
        <form method="post">
            <div class="row">
            <div class="col-md-4 col-md-offset-4 text-center">
                <h1> Current Processed Requests </h1>
            <div class="input-group" for="StaffWithRequest">
  		<span class="input-group-addon">
                    Employees &nbsp; With&nbsp; Main &nbsp; Vacation&nbsp; Requests
                </span>
  		<input type="text" class="form-control" name="withCount" 
                       id="withCount" readonly 
                       value="<?php echo $totalEmployees-$employeesWithNoMainVacation;?>">
	    </div>
            
            <div class="input-group" for="StaffWithoutRequest">
  		<span class="input-group-addon">
                    Employees Without Main Vacation Requests
                </span>
  		<input type="text" class="form-control" name="withoutCount" 
                       id="withCount" readonly 
                       value="<?php echo $employeesWithNoMainVacation;?>">
	    </div>

                        
            <input class="btn btn-success btn-block" type="submit" 
                   name="processmainrequests" id="submit" 
                   value="Process Main Requests"/>
            <input class="btn btn-success btn-block" type="submit" 
                   name="processadhocrequests" id="submit" 
                   value="Process Ad Hoc Requests"/>
            </div>
            </div>
        </form>
        
         <div id="table">
            
            <form method="post">
                
            <div class="row">
            <div class="col-md-8 col-md-offset-2 text-center">
            <table class="table table-bordered table-hover">
                <br/> <br/> <br/> 
                <thead>
                    <h1>Current Main Vacation Requests</h1>
                    <tr>
                        <th>Name</th>
                        <th>First Choice Start</th>
                        <th>First Choice End</th>
                        <th>Second Choice Start</th>
                        <th>Second Choice End</th>
                    </tr>
                </thead>
                <tbody>
                    <?php DisplayMainVacationTableBody(); ?>
                </tbody>
            </table>
            </div>
            </div>
            </form>
        </div>  
      
         <div id="table">
            <form method="post">
            <div class="row">
            <div class="col-md-8 col-md-offset-2 text-center">    
            <table class="table table-bordered table-hover">
                <br/> <br/> <br/>
                <thead>
                    <h1> Current Ad Hoc Requests </h1>
                    <tr>
                        <th>Name</th>
                        <th>Start</th>
                        <th>End</th>
                    </tr>
                </thead>
                <tbody>
                    <?php DisplayAdHocRequestTableBody(); ?>
                </tbody>
            </table>
            </div>
            </div>
            </form>
        </div>  
    </body>
</html>
