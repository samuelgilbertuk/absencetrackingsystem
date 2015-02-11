<?php
include 'sessionmanagement.php';

if (!$isManager) {
    header('Location: index.php');
    exit();
}

if (isset($_POST["submit"])) 
{
    ClearStatus();
    $statusMessage = "";
    $inputIsValid = true;
    
    $startDate = $_POST["startDate"];
    $endDate = $_POST["endDate"];
    
    if (!isValidDate($startDate)) {
        $statusMessage.="Start Date is not a valid Date.</br>";
        $inputIsValid = false;
    }

    if (!isValidDate($endDate)) {
        $statusMessage.="Finish Date is not a valid Date.</br>";
        $inputIsValid = false;
    }
    
    if (strtotime($endDate) < strtotime($startDate)) 
    {
        $statusMessage.="End Date is before Start Date.</br>";
        $inputIsValid = false;
    }

    if ($inputIsValid == false)
    {
        GenerateStatus(false, $statusMessage);
    }
}

function DisplaySearchTableBody($startDate,$endDate)
{
    date_default_timezone_set('UTC');
    $startDate = $_POST["startDate"];
    $startDateTime = strtotime($startDate);
   
    $endDate = 
    $endDateTime = strtotime($endDate);

    $bookings = RetrieveApprovedAbsenceBookings();

    if ($bookings <> NULL) 
    {
        foreach ($bookings as $booking) 
       {
            $bookingStartTime = strtotime($booking[APPR_ABS_START_DATE]);
            $bookingEndTime = strtotime($booking[APPR_ABS_START_DATE]);
        
            if ( ($bookingStartTime >= $startDateTime) AND
                 ($bookingEndTime <= $endDateTime))
            {
                $employee = RetrieveEmployeeByID($booking[APPR_ABS_EMPLOYEE_ID]);
                $absenceType = RetrieveAbsenceTypeByID($booking[APPR_ABS_ABS_TYPE_ID]);
                
                echo '<tr>';
                echo '<td>'.$employee[EMP_NAME].'</td>';
                echo '<td>'.$booking[APPR_ABS_START_DATE].'</td>';
                echo '<td>'.$booking[APPR_ABS_END_DATE].'</td>';
                echo '<td>'.$absenceType[ABS_TYPE_NAME].'</td>';
                echo '</tr>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>View Approved Absence Bookings</title>
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
                    <h1>View Approved Absence Bookings</h1>

                    <div class="input-group" for="startDate">
                        <span class="input-group-addon">Search Period Start Date&nbsp;
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>	
                        <input type="date" class="form-control" name="startDate"
                               id="startDate" placeholder="Start Date">
                    </div>


                    <div class="input-group" for="endDate">
                        <span class="input-group-addon">Search Period Finish Date
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>	
                        <input type="date" class="form-control" name="endDate" 
                               id="endDate" placeholder="End Date">
                    </div>
                    <br/>
                    <input class="btn btn-success btn-block" type="submit" 
                           name="submit" id="submit" value="Display Bookings"/>
                </div>
            </div>
        </form>
        <?php if (isset($_POST["submit"])){?>
        <div id="table">
            <div class="row">
                <div class="col-md-8 col-md-offset-2 text-center">
                    <br/><br/><br/>
                    <h1>Search Results</h1>

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
                                <?php DisplaySearchTableBody($_POST["startDate"],
                                                            $_POST["endDate"]); ?>
                            </tbody>
                        </table>
                    </form>
        </div> <?php }?>
    </body>
</html>