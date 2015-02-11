<?php
    include 'sessionmanagement.php';
    if(isset($_POST['drop']))
    {
        DropDB();
    }
    
    if(isset($_POST['fill']))
    {
        $destroyExistingDatabase = TRUE;
        $PopulateDatabaseWithTestData = TRUE;
        CreateNewDatabase($destroyExistingDatabase,$PopulateDatabaseWithTestData);
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Amend Absence Type</title>
    </head>
 
    <body>

        <form method="post">
             <input type="submit" name="drop" id="drop" value="Drop"/>
             <input type="submit" name="fill" id="fill" value="Fill"/>
        </form>

    </body>

</html>