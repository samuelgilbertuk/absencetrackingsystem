<?php
session_start();
include 'sessionmanagement.php';

    

if (!isset($_SESSION['StatusDiv']))
{
    $_SESSION['StatusDiv'] = "";
}

if (isset($_POST["submit"])) {
    ClearStatus();
    $email = $_POST["inputEmail"];
    $password = $_POST["inputPassword"];
    
    if ($email == "")
    {
        GenerateStatus(false,"You must enter an email address.");
    }
    else if ($password == "")
    {
        GenerateStatus(false,"You must enter a password.");
    }
    else 
    {
        $filter[EMP_EMAIL] = $email;
        $employees = RetrieveEmployees($filter);
        if (count($employees)<>1)
        {
            GenerateStatus(false,"No matching email address found.");
        }
        else 
        {
            $encryptedPassword = $employees[0][EMP_PASSWORD];
            $temp = md5(md5($email).$password);
        
            if ($temp == $encryptedPassword)
            {
                $_SESSION['userID'] = $employees[0][EMP_ID];
                $_SESSION['administrator'] = $employees[0][EMP_ADMIN_PERM];
                $_SESSION['manager'] = $employees[0][EMP_MANAGER_PERM];
                header('Location: index.php');
            }
            else
            {
                GenerateStatus(false,"Password is incorrect.");
            }
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
        <title>Absence Tracking System Login</title>
    </head>
 
    <body>
        <?php include 'navbar.php'; ?>
        
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <form method="POST">
                        <div class="input-group" for="email">
                            <span class="input-group-addon glyphicon glyphicon-user"></span>
                            <input type="email" class="form-control" 
                                   name="inputEmail" id="email" placeholder="Email">
                        </div>

                        <div class="input-group" for="password">
                            <span class="input-group-addon glyphicon glyphicon-lock"></span>
                            <input type="password" class="form-control" 
                                   name="inputPassword" id="password" placeholder="Password">
                        </div>

                        <input type="submit" 
                               class="btn btn-lg btn-primary btn-block btn-default" 
                               name="submit" value="Sign In">
                    </form>
                </div>
             </div>     
        </div>
    </body>
</html>