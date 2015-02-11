<?php
include 'sessionmanagement.php';

// If user is not an adminstrator, redirect them back to the home page.
if (!$isAdministrator)
{
   header('Location: index.php');
   exit();
}

//-----------------------------------------------------------------------------
// This function will generate the HTML necessary for the body of the 
// Approved Absence booking date table.
//-----------------------------------------------------------------------------
function DisplayApproveAbsenceBookingDatesTable()
{
    $bookingDates = RetrieveApprovedAbsenceBookingDates();
    if ($bookingDates <> NULL)
    {
        foreach ($bookingDates as $bookingDate) 
        { 
            echo "<tr>";
            echo  "<td>".$bookingDate[APPR_ABS_BOOK_DATE_ID]."</td>";
            echo "<td>".$bookingDate[APPR_ABS_BOOK_DATE_DATE_ID]."</td>";
            echo "<td>".$bookingDate[APPR_ABS_BOOK_DATE_ABS_BOOK_ID]."</td>";
            echo "</tr>";
        }
    } 
}


?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Admin Approved Absence Booking Dates</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    </head>
 
    <body>
        <?php include 'navbar.php'; ?>

        <div id="table">
            <form method="post">
            <div class="row">
            <div class="col-md-8 col-md-offset-2 text-center">
                <h1> Approved Absence Booking Dates </h1>
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Record ID</th>
                        <th>Date ID</th>
                        <th>Approved Absence Booking ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php DisplayApproveAbsenceBookingDatesTable(); ?>
                </tbody>
            </table>
            </div>
            </div>   
            </form>
        </div>
    </body>
</html>