<?php
include 'sessionmanagement.php';

if (!$isAdministrator)
{
   header('Location: index.php');
   exit();
}

if ($_GET["ID"] <> NULL)
{
    $record = RetrievePublicHolidayByID($_GET["ID"]);
    $date = RetrieveDateByID($record[PUB_HOL_DATE_ID]);
}

if (isset($_POST["cancel"])) {   
    $url = "Location:adminPublicHolidays.php";   
    header($url);
}

if (isset($_POST["update"])) {
    $record[PUB_HOL_ID]       =   $_GET["ID"];
    $record[PUB_HOL_NAME]     =   $_POST["name"];
    
    $filter[DATE_TABLE_DATE]= $_POST["date"];
    $dates = RetrieveDates($filter);
    
    $date = $dates[0];

    $currentRecord = RetrievePublicHolidayByID($_GET["ID"]);
    
    if ($currentRecord <> NULL)
    {
        if ($currentRecord[PUB_HOL_DATE_ID]<> $date[DATE_TABLE_DATE_ID])
        {
            //Date has changed, so remove the public holiday ID from the old
            //date record and add to the new date record.
            $oldDate = RetrieveDateByID($currentRecord[PUB_HOL_DATE_ID]);
            $oldDate[DATE_TABLE_PUBLIC_HOL_ID] = NULL;
            UpdateDate($oldDate);
        }
    }

    $record[PUB_HOL_DATE_ID]=   $date[DATE_TABLE_DATE_ID];
    $success = UpdatePublicHoliday($record);

    if ($success)
    {
        $url = "Location:adminPublicHolidays.php";   
        header($url);
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
        <title>Amend Company Role</title>
    </head>
 
    <body>
        <?php include 'navbar.php'; ?>
        
        
        <form method="post">
            <div class="row">
            <div class="col-md-4 col-md-offset-4 text-center">
               
                <h1> Edit Public Holiday </h1>    
                
            <div class="input-group" for="roleName">
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="text" class="form-control"  name="name" id="name" 
                       placeholder="Public Holiday Name"
                       value="<?php echo $record[PUB_HOL_NAME];?>">
            </div>
                   
            <div class="input-group" for="date">
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="text" class="form-control"  name="date" id="date" 
                       placeholder="Date Joined"
                       value="<?php echo $date[DATE_TABLE_DATE]; ?>">
            </div>     

            <input class="btn btn-success btn-block" type="submit" name="update" 
                   id="submit" value="Update"/> 
            <input class="btn btn-danger btn-block" type="submit" name="cancel" 
                   id="cancel" value="Cancel"/> 
            </div>
            </div>
        </form>
   </body>
</html>