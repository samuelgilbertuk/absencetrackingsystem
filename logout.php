<?php
//----------------------------------------------------------------------------
// To log a user out of the system we simply clear and destroy the session
// and then redirect the user back to the login page.
//----------------------------------------------------------------------------
session_start();
session_unset();
session_destroy();
header('Location: login.php');
exit();
?>