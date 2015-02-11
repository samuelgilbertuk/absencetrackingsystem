<?php
include 'sessionmanagement.php';


if (!$isAdministrator)
{
   header('Location: index.php');
   exit();
}

if (isset($_POST["submit"])) {
    ClearStatus();
    $role = CreateCompanyRole($_POST["roleName"], $_POST["minStaff"]);
    }

if (isset($_POST["amend"])) {   
    ClearStatus();
    $url = "Location:editcompanyrole.php?roleID=".$_POST["amend"];   
    header($url);
}

if (isset($_POST["delete"])) {
    ClearStatus();
    DeleteCompanyRole($_POST["delete"]);
}

function DisplayCompanyRolesTableBody()
{
    $roles = RetrieveCompanyRoles();
    if ($roles <> NULL)
    {
        foreach ($roles as $role) 
        {
            echo "<tr>";
            echo "<td>".$role[COMP_ROLE_NAME]."</td>";
            echo "<td>".$role[COMP_ROLE_MIN_STAFF]."</td>";
            echo "<td>"; 
            echo '<button class="btn btn-success" type="submit"'.
                 'name="amend"'.
                 'value="'.$role[COMP_ROLE_ID].'">'.
                 'Amend</button></td>';
            echo '<td> <button class="btn btn-danger" type="submit"'.
                 'name="delete"'.  
                 'value="'.$role[COMP_ROLE_ID].'">'.
                 'Delete</button></td>';
            echo '</tr>';
        }
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
        <title>Admin Company Roles</title>
    </head>
 
    <body>
        <?php include 'navbar.php'; ?>
        <div class="row col-md-4 col-md-offset-4 text-center">
            <form method="post">
                <h1>Create New Company Role</h1>
                <div class="input-group" for="roleName">
                    <span class="input-group-addon">Company Role Name </span>
                    <input type="text" class="form-control" 
                           placeholder="Enter name" name="roleName" 
                           id="roleName">
                </div>
            
                <br/>    

                <label for="minStaff">Minimum Staff Level</label>
                <input type="range" class="form-control" name="minStaff" min="0" 
                       max="30" value="1" step="1" 
                    oninput="updateMinStaff(value)"  id="minStaff" /> 
                <output for="minStaff" id="staffNumber">1</output>
                <input class="btn btn-success btn-block" type="submit" 
                       name="submit" id="submit" value="Add Role"/> 
                
            </form>
        </div>
        
     
        <div class="row col-md-8 col-md-offset-2 text-center">
            <br/><br/>
            <h1>Current Company Roles</h1>
            <form method="post">
                <table class="table table-hover table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th>Role Name</th>
                            <th>Minimum Staffing Level</th>
                            <th>Amend</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php DisplayCompanyRolesTableBody(); ?>
                    </tbody>
                </table>
            </form>
        </div>

        <script>
            function updateMinStaff(level)
            {
                document.querySelector('#staffNumber').value = level;
            }
        </script>
    </body>
</html>

