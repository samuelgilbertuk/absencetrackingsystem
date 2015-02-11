<?php
include 'sessionmanagement.php';

if (!$isAdministrator)
{
   header('Location: index.php');
   exit();
}

if (isset($_POST["submit"])) 
{
    ClearStatus();

    $isAdministrator = 0;
    $isManager = 0;
    
    if (isset($_POST["isAdministrator"]))
    {
        $isAdministrator = 1;
    }
    if (isset($_POST["isManager"] ))
    {
       $isManager = 1;
    }
    
    
    $employee = CreateEmployee($_POST["empName"], 
                               $_POST["eMail"],
                               $_POST["password"],
                               $_POST["dateJoin"], 
                               $_POST["annualLeave"],
                               NULL,
                               $_POST["companyRole"],
                               $isAdministrator,
                               $isManager); 
}

if (isset($_POST["amend"])) { 
    ClearStatus();

    $url = "Location:editEmployee.php?ID=".$_POST["amend"];   
    header($url);
}

if (isset($_POST["delete"])) 
{
    ClearStatus();
    DeleteEmployee($_POST["delete"]);
}

function DisplayEmployeeTableBody()
{
    $employees = RetrieveEmployees();
    if ($employees <> NULL)
    {
        foreach ($employees as $employee) 
        { 
            $role = RetrieveCompanyRoleByID($employee[EMP_COMPANY_ROLE]);
            echo "<tr>";
            echo "<td>".$employee[EMP_ID]."</td>";
            echo "<td>".$employee[EMP_NAME]."</td>";
            echo "<td>".$employee[EMP_EMAIL]."</td>";
            echo "<td>".$employee[EMP_DATEJOINED]."</td>";
            echo "<td>".$employee[EMP_LEAVE_ENTITLEMENT]."</td>";
            echo "<td>".$role[COMP_ROLE_NAME]."</td>";
            echo "<td>".$employee[EMP_MAIN_VACATION_REQ_ID]."</td>";
            echo "<td>".$employee[EMP_ADMIN_PERM]."</td>";
            echo "<td>".$employee[EMP_MANAGER_PERM]."</td>";
            echo '<td> <button type="submit" class="btn btn-success" '.
                 'name="amend" id="amend"  value="'.$employee[EMP_ID].
                 '">Amend</button></td>';
            echo '<td> <button type="submit" class="btn btn-danger" '.
                 'name="delete" id="delete" value="'.$employee[EMP_ID].
                 '">Delete</button></td>';
            echo "</tr>";
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
        <title>Admin Employees</title>
    </head>
 
    <body>
        <?php include 'navbar.php'; ?>
       

        <form class="signUp" method="post">
            <div class="row">
                
            <div class="col-md-4 col-md-offset-4 text-center">
                <h1> Create a new Employee </h1>
                
                <div class="input-group" for="empName">
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-user"></span>
                    </span>
                    <input type="text" class="form-control" placeholder="Name" 
                           name="empName" id="empName" >
                </div>

                <div class="input-group" for="eMail">
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-envelope"></span>
                    </span>
                    <input type="text" class="form-control" placeholder="Email" 
                           name="eMail" id="eMail">
                </div>


                <div class="input-group" for="password">
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-lock"></span>
                    </span>
                    <input type="password" class="form-control" placeholder="Password" 
                           name="password" id="password">
                </div>

                <div class="input-group" for=dateJoin">
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                    <input type="date" class="form-control" name="dateJoin" 
                           id="dateJoin" placeholder="Date Joined">
                </div>
                <br/>

                <label for="companyRole">Company Role</label>
                <?php
                $roles = RetrieveCompanyRoles();
                if ($roles <> NULL) {
                    echo '<select  class= "form-control" name="companyRole">';
                    foreach ($roles as $role) {
                        echo '<option value="' . $role[COMP_ROLE_ID] . '">' .
                                $role[COMP_ROLE_NAME] . '</option>';
                    }
                }

                echo '</select>';
                ?>
                <br/>

                <label for="annualLeave">Annual Leave Entitlement</label>
                <input type="range"  class= "form-control" name="annualLeave" 
                       min="10" max="28" value="19" step="1" 
                       oninput="updateAnnualLeave(value)"  id="annualLeave" /> 
                <output for="minStaff" id="Leave">19</output>

                <br/>
                
                    <label for="isAdministrator" >Is Administrator</label>
                    <input type="checkbox" name="isAdministrator" 
                           id="isAdministrator" /> 
                    <label for="isManager" >&nbsp;&nbsp;Is Manager</label>
                    <input type="checkbox"  name="isManager" id="isManager" /> 
                </div>
                <input type="submit" class="btn btn-success col-md-4 col-md-offset-4" 
                       name="submit" id="submit" value="Add Employee"/>
            </div>    
        </form>
        
        <div id="table" class="table-responsive">
            <form method="post">
                <div class="row col-md-10 col-md-offset-1 text-center">
                    <br/><br/><br/>
                    <h1>Current Employees </h1>
             
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Date Joined</th>
                            <th>Annual Leave Entitlement</th>
                            <th>Company Role</th>
                            <th>Main Vacation Request ID</th>
                            <th>Is Administrator</th>
                            <th>Is Manager</th>
                            <th>Amend</th>
                            <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php DisplayEmployeeTableBody(); ?>
                </tbody>
            </table>
            </form>
        </div>  
                
        <script>
            function updateAnnualLeave(level)
            {
                document.querySelector('#Leave').value = level;
            }                
        </script>

       </body>

</html>
