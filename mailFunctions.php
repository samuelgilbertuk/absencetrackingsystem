<?php

/* -----------------------------------------------------------------------------
 * Function SendApprovedEmail
 *
 * This function creates and sends an email to an employee, informing them that
 * an absence booking request has been approved.
 *
 * $employeeID (int) id for the employee that the email should be sent to.
 * $startDate (string) date that the booking starts in the form YYYY-MM-DD
 * $endDate (string) date that the booking ends in the form YYYY-MM-DD
 * @return (bool)  TRUE if email was generated successfully, FALSE otherwise.
 * -------------------------------------------------------------------------- */
 function SendApprovedEmail($employeeID,$startDate,$endDate)
{
    $employee 	= RetrieveEmployeeByID($employeeID);
    $to 	= $employee[EMP_EMAIL];
    $from	= "admin@absencetrackingsystem.com";
    
    $subject 	= "ABSENCE REQUEST APPROVED";
    $message 	= "Your request for absence between the dates of ".
                  "$startDate and $endDate has been approved.";
				  
    $result = mail($to,$subject,$message);
    return $result;
}


/* -----------------------------------------------------------------------------
 * Function SendDeniedEmail
 *
 * This function creates and sends an email to an employee, informing them that
 * an absence booking request has been denied.
 *
 * $employeeID (int) id for the employee that the email should be sent to.
 * $startDate (string) date that the booking starts in the form YYYY-MM-DD
 * $endDate (string) date that the booking ends in the form YYYY-MM-DD
 * $reason (string) reason for denial.
 * @return (bool)  TRUE if email was generated successfully, FALSE otherwise.
 * -------------------------------------------------------------------------- */
 function SendDeniedEmail($employeeID,$startDate,$endDate,$reason)
{
    $employee 	= RetrieveEmployeeByID($employeeID);
    $to 	= $employee[EMP_EMAIL];
    $from	= "admin@absencetrackingsystem.com";
	
    $subject 	= "ABSENCE REQUEST DENIED";
    $message 	= "Unfortunatly, your request for absence between the dates of ".
                   "$startDate and $endDate has been denied. Reason: $reason";

    $result = mail($to,$subject,$message);
    return $result;
}


/* -----------------------------------------------------------------------------
 * Function SendEmailToOfficeManager
 *
 * This function creates and sends an email to the office managers informing them
 * of a staff shortfall due to a booking that can not be disallowed taking place 
 * during a time period which will take the number of people in that role below
 * the minimum number defined for that role.
 *
 * $employeeID (int) id for the employee that has requested the leave.
 * $startDate (string) date that the booking starts in the form YYYY-MM-DD
 * $endDate (string) date that the booking ends in the form YYYY-MM-DD
 * $absenceType (int) id for the type of absence.
 * @return (bool)  TRUE if email was generated successfully, FALSE otherwise.
 * -------------------------------------------------------------------------- */
function SendShortfallAlertToOfficeManager($employeeID,$startDate,$endDate,$absenceTypeID)
{
    $employee 	= RetrieveEmployeeByID($employeeID);
    $employeeName 	= $employee[EMP_NAME];
	
    $absenceType 	= RetrieveAbsenceTypeByID($absenceTypeID);
    $absenceName 	= $absenceType[ABS_TYPE_NAME];
	
    $role 			= RetrieveCompanyRoleByID($employee[EMP_COMPANY_ROLE]);
    $roleName		= $role[COMP_ROLE_NAME];
    $minimumStaff	= $role[COMP_ROLE_MIN_STAFF];
	
    $from			= "admin@absencetrackingsystem.com";
	
    $subject 	= "URGENT: STAFF SHORTFALL";
    $message 	= "Between $startDate and $endDate the number of staff performing the ".
		  "role of $roleName will be below $minimumStaff.".
		  "This is due to $employeeName being absent with $absenceName.";

	
    $filter[EMP_MANAGER_PERM] = 1;
    $managers = RetrieveEmployees($filter);
	
    $success = TRUE;
    foreach ($managers as $manager)
    {
	if (! mail($manager[EMP_EMAIL],$subject,$message))
	{
            $success = FALSE;
	}
    }
    return $success;
}

/* -----------------------------------------------------------------------------
 * Function SendResubmitMainValcationRequest
 *
 * This function creates and sends an email to an employee, asking them to resubmit
 * their main vacation request.
 *
 * $employeeID (int) id for the employee that the email should be sent to.
 * @return (bool)  TRUE if email was generated successfully, FALSE otherwise.
 * -------------------------------------------------------------------------- */
function SendResubmitMainVacationRequest($employeeID)
{
    $employee 	= RetrieveEmployeeByID($employeeID);
    $to 		= $employee[EMP_EMAIL];
    $from		= "admin@absencetrackingsystem.com";
	
    $subject 	= "URGENT: NEW MAIN VACATION REQUEST NEEDED";
    $message 	= "Unfortunatly, both of your main vacation choices are unavailable.".
	   	  "Please submit a new Main Vacation Request with two new choices.";
	
    $result = mail($to,$subject,$message);
    return $result;
}
?>
