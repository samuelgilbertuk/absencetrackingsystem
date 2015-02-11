<?php
include 'sessionmanagement.php';

if (!$isAdministrator)
{
   header('Location: index.php');
   exit();
}

function DisplayDatesTableBody()
{
    $dates = RetrieveDates();
    if ($dates <> NULL)
    {
        foreach ($dates as $date) 
        {
            echo "<tr>";
            echo "<td>".$date[DATE_TABLE_DATE_ID]."</td>";
            echo "<td>".$date[DATE_TABLE_DATE]."</td>";
            echo "<td>".$date[DATE_TABLE_PUBLIC_HOL_ID]."</td>";
            
            $pubHolID = $date[DATE_TABLE_PUBLIC_HOL_ID]; 
            $publicHolidayName = "";
                            
            if ( $pubHolID <> NULL)
            {
                $publicHoliday = RetrievePublicHolidayByID($pubHolID);
                $publicHolidayName = $publicHoliday[PUB_HOL_NAME];
            }
            
            echo "<td>".$publicHolidayName."</td>";
            echo "</tr>";
        }
    } 
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Admin Dates</title>
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
            <div class="row">
                <div class="col-md-8 col-md-offset-2 text-center">
            <form method="post">
            <table class="table table-hover table-bordered">
                <br/> <br/> <br/>
                <thead>
                    <tr>
                <h1> Current Dates </h1>
                        <th>Date ID</th>
                        <th>Date</th>
                        <th>Public Holiday ID</th>
                        <th>Public Holiday Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php DisplayDatesTableBody(); ?>
                </tbody>
            </table>
            </form>
                </div>
            </div>
        </div>
    </body>
</html>
