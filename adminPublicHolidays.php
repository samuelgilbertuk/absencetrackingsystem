<?php
include 'sessionmanagement.php';
  
if (!$isAdministrator)
{
   header('Location: index.php');
   exit();
}

if (isset($_POST["submit"])) {
    ClearStatus();
    $dateID = RetrieveDateIDByDate($_POST["date"]);
    $holiday = CreatePublicHoliday($_POST["name"], $dateID);
    }

if (isset($_POST["amend"])) {   
    ClearStatus();
    $url = "Location:editpublicholiday.php?ID=".$_POST["amend"];   
    header($url);
}

if (isset($_POST["delete"])) {
    ClearStatus();
    DeletePublicHoliday($_POST["delete"]);
}

function DisplayPublicHolidayTableBody()
{
    $holidays = RetrievePublicHolidays();
    if ($holidays <> NULL)
    {
        foreach ($holidays as $holiday) 
        { 
            $date = RetrieveDateByID($holiday[PUB_HOL_DATE_ID]);
            echo "<tr>";
            echo "<td>".$holiday[PUB_HOL_ID]."</td>";
            echo "<td>".$holiday[PUB_HOL_NAME]."</td>";
            echo "<td>".$holiday[PUB_HOL_DATE_ID]."</td>";
            echo "<td>".$date[DATE_TABLE_DATE]."</td>";
            echo '<td> <button class="btn btn-success" type="submit" '. 
                 'name="amend" value="'.$holiday[PUB_HOL_ID].'">'.
                 'Amend</button></td>';
            echo '<td> <button class="btn btn-danger" type="submit"'. 
                 'name="delete" value="'.$holiday[PUB_HOL_ID].'">'.
                 'Delete</button></td>';
            echo '</tr>';
        }
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Admin Public Holidays</title>
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
                <h1> Add Public Holiday </h1>
            <div class="input-group" for="name">
		<span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>	
                <input type="text" class="form-control" name="name" id="name" 
                       placeholder="Public Holiday Name">
            </div>

            <div class="input-group" for="date">
		<span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>	
  		<input type="date" class="form-control" name="date" id="date" 
                       placeholder="Date">
            </div>
                
            <br/>    
            <input class="btn btn-success btn-block col-md-4" type="submit" 
                   name="submit" id="submit" value="Add"/> 
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
                <h1> Current Public Holidays </h1>
                    <tr>
                        <th>ID</th>
                        <th>Public Holday Name</th>
                        <th>Date ID</th>
                        <th>Date</th>
                        <th>Amend</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php DisplayPublicHolidayTableBody(); ?>
                </tbody>
            </table>
            </div>
            </div>
            </form>
        </div>
    </body>
</html>
