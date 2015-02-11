<?php
include 'sessionmanagement.php';

if ($_GET["ID"] <> NULL)
{
    $Employee = RetrieveEmployeeByID($_GET["ID"]);
}

if (isset($_POST["cancel"])) {   
    ClearStatus();

    $url = "Location:adminEmployeeTable.php";   
    header($url);
}

if (isset($_POST["update"])) {
    ClearStatus();

    unset($Employee);
    $Employee[EMP_ID]               =   $_GET["ID"];
    $Employee[EMP_NAME]             =   $_POST["empName"];
    $Employee[EMP_EMAIL]            =   $_POST["eMail"];
    $Employee[EMP_DATEJOINED]       =   $_POST["dateJoin"];
    $Employee[EMP_LEAVE_ENTITLEMENT]=   $_POST["annualLeave"];
    $Employee[EMP_COMPANY_ROLE]     =   $_POST["companyRole"];
    
    $Employee[EMP_ADMIN_PERM] = '0';
    if (isset($_POST['isAdmin']))
    {
        if ($_POST["isAdmin"] == 'on')
        {
         $Employee[EMP_ADMIN_PERM] = '1';
        }
    }
    
    $Employee[EMP_MANAGER_PERM] = '0';
    if (isset($_POST['isManager']))
    {
        if ($_POST["isManager"] == 'on')
        {
             $Employee[EMP_MANAGER_PERM] = '1';
        }
    }

    $result = UpdateEmployee($Employee);
    
    if ($result)
    {
        $url = "Location:adminEmployeeTable.php";   
        header($url);
    }
}

function GenerateCompanyRoleSelect()
{
    $roles = RetrieveCompanyRoles();
    if ($roles <> NULL)
    {
        echo '<select class="form-control" name="companyRole">';
        foreach ($roles as $role)
        {
            if ($role[COMP_ROLE_ID]== $Employee[EMP_COMPANY_ROLE])
            {
                echo '<option  selected="selected" value="'.$role[COMP_ROLE_ID].
                        '">'.$role[COMP_ROLE_NAME].'</option>';
            }
            else 
            {
                echo '<option value="'.$role[COMP_ROLE_ID].'">'.
                        $role[COMP_ROLE_NAME].'</option>';
            }
        }
    }
                
    echo '</select>';
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Admin Employees</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    </head>
 
    <body>
        <?php include 'navbar.php'; ?>
        
        <form class="signUp"method="post">
            <div class="row">
            <div class="col-md-4 col-md-offset-4 text-center">
            <h1> Edit Employee </h1>
                
            <div class="input-group" for="empName">
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-user"></span>
                </span>
                <input type="text" class="form-control" placeholder="Name" 
                       name="empName" id="empName" 
                       value="<?php echo $Employee[EMP_NAME]; ?>">
            </div>
            
            <div class="input-group" for="eMail">
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-envelope"></span>
                </span>
                <input type="text" class="form-control" placeholder="Email" 
                       name="eMail" id="eMail" 
                       value="<?php echo $Employee[EMP_EMAIL]; ?>">
            </div>

            <div class="input-group" for=dateJoin">
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                <input type="date" class="form-control" name="dateJoin" 
                       id="dateJoin" placeholder="Date Joined" 
                       value="<?php echo $Employee[EMP_DATEJOINED]; ?>">
            </div>
                
            <br />
            
            <label for="annualLeave">Annual Leave Entitlement</label>
            <input type="range" name="annualLeave" min="10" max="28" 
                   value="<?php echo $Employee[EMP_LEAVE_ENTITLEMENT]; ?>"
                   step="1" oninput="updateAnnualLeave(value)"  id="annualLeave" /> 
            <output for="minStaff" id="Leave">
                <?php echo $Employee[EMP_LEAVE_ENTITLEMENT]; ?></output>
            
            <br />
            
            <label for="companyRole">Company Role</label>
            <?php  GenerateCompanyRoleSelect(); ?>
            <br/>
            
            <label for="isAdmin"> Is Administrator</label>
            <input type="checkbox" name="isAdmin" id="isAdmin" 
                   <?php if ($Employee[EMP_ADMIN_PERM] == 1) echo "checked"; ?>/>
            
            <label for="isManager"> Is Manager</label>
            <input type="checkbox" name="isManager" id="isManager" 
                   <?php if ($Employee[EMP_MANAGER_PERM] == 1) echo "checked"; ?>/>
            
            <br /><br/>
            
            <input class="btn btn-success btn-block" type="submit" name="update"
                   id="submit" value="Edit Employee"/>
            <input class="btn btn-danger btn-block" type="submit" name="cancel" 
                   id="cancel" value="Cancel Changes"/>

            <script>
                function updateAnnualLeave(level)
                {
                    document.querySelector('#Leave').value = level;
                }
            </script>
            </div>
            </div>
        </form>
    </body>
</html>