<?php

//-----------------------------------------------------------------------------
// Function ClearStatus
// 
// This function simply clears any text from the session variable "StatusDiv"
//-----------------------------------------------------------------------------
function ClearStatus()
{
    $_SESSION["StatusDiv"]="";
}

/* ----------------------------------------------------------------------------
 * Function GenerateStatus
 *
 * This function generates a session variable called "StatusDiv" which 
 * contains the HTML necessary to display a dismissable status div on the web
 * page.
 * 
 * $isSuccess (bool) If true, the status div will have a green background.
 *                   If false, the status div will have a red background.
 * $statusMessage (string) The status message to display in the statis div.
 * @return (none)  
 * * -------------------------------------------------------------------------*/
function GenerateStatus($isSuccess,$statusMessage)
{
    $statusClass = "alert-danger";
            
    if ($isSuccess)
    {
        $statusClass = "alert-success";
    }
    
    $_SESSION["StatusDiv"] = 
            "<div class='alert $statusClass alert-dismissable'>$statusMessage".
            '<button type="button" class="close" data-dismiss="alert">x</button>'.
            "</div>";
}
?>