 <?php 
    $userDetails = "Not Logged In";
    if (isset($_SESSION['userID'])) 
    {
        $employee = RetrieveEmployeeByID($_SESSION['userID']); 
        if ($employee)
        {
            $userDetails = "Logged in as ".$employee[EMP_NAME];
        }
    }
?>


<nav role="navigation" class="navbar navbar-default">
    <div class="navbar-header">
        <button type="button" data-target=".navbarCollapse" 
                data-toggle="collapse" class="navbar-toggle">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        
        <img src="/images/logo.png" style="max-width:45px" class="img-rounded" 
             alt="Rounded Image">
        <a href="#" class="navbar-brand">Absence Tracking System</a>
    </div>
    
    <div class="nav navbar-nav">
        <ul class=""navbar-nav>
            <li><a href="index.php">Home</a></li>
        </ul>
    </div>
        
    <div id="navbarCollapse" class="collapse navbar-collapse">
        <ul class="nav navbar-nav navbar-right">
            <li><?php echo $userDetails; ?></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>
<?php echo $_SESSION["StatusDiv"] ?>
        