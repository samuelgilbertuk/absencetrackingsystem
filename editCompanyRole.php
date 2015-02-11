<?php
include 'sessionmanagement.php';

if (!$isAdministrator)
{
   header('Location: index.php');
   exit();
}

if ($_GET["roleID"] <> NULL)
{
    $role = RetrieveCompanyRoleByID($_GET["roleID"]);
}

if (isset($_POST["cancel"])) {   
    $url = "Location:adminCompanyRoles.php";   
    header($url);
}

if (isset($_POST["update"])) {
    $role[COMP_ROLE_ID]       =   $_GET["roleID"];
    $role[COMP_ROLE_NAME]     =   $_POST["roleName"];
    $role[COMP_ROLE_MIN_STAFF]=   $_POST["minStaff"];
    $result = UpdateCompanyRole($role);

    if ($result)
    {
        $url = "Location:adminCompanyRoles.php";   
        header($url);
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Amend Company Role</title>
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
                <h1> Edit Company Role </h1>
                <div class="input-group" for="roleName">
                    <span class="input-group-addon">Company Role Name 
                        <span class="glyphicon glyphicon-briefcase"></span>
                    </span>
		<input type="text" class="form-control" name="roleName" 
                       id="roleName" value="<?php echo $role[COMP_ROLE_NAME];?>" >
            </div>

            <br/>    

            <label for="minStaff">Minimum Staff Level</label>
            <input type="range" name="minStaff" min="0" max="30" 
                   value="<?php echo $role[COMP_ROLE_MIN_STAFF];?>" step="1" 
                   oninput="updateMinStaff(value)"  id="minStaff" /> 
            <output for="minStaff" id="staffNumber">
                <?php echo $role[COMP_ROLE_MIN_STAFF];?></output>
            <br/>
            <input class="btn btn-success btn-block" type="submit" name="update" 
                   id="submit" value="Update"/> 
            <input class="btn btn-danger btn-block" type="submit" name="cancel" 
                   id="cancel" value="Cancel"/> 

            <script>
                function updateMinStaff(level)
                {
                    document.querySelector('#staffNumber').value = level;
                }
            </script>
            </div>
            </div>
        </form>
    </body>
</html>