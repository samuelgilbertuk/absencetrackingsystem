<?php

/* -----------------------------------------------------------------------------
 * Function IsPublicHoliday
 *
 * This function creates checks the supplied date to determine whether or not
 * the date is a public holiday.
 *
 * $date (string) string date in the form YYYY-MM-DD.
 * @return (bool)  TRUE if date supplied is a public holiday, otherwise FALSE.
 * -------------------------------------------------------------------------- */
function isPublicHoliday($date)
{
    //Assume false. Will set to true if it is a public holiday
    $result = FALSE; 

    // Obtain the date record that matches this date from the Date table 
    $dateRecord = RetrieveDateRecordByDate($date);
    
    //Q.Was the date found?
    if ($dateRecord <> NULL)
    {
        //Yes. Q.Does the date record have a public holiday ID set?
        if ($dateRecord[DATE_TABLE_PUBLIC_HOL_ID]<> NULL)
        {
            //Yes. Date is therefore a public holiday.
            $result = TRUE;
        }
    }
    return $result;
}

/* -----------------------------------------------------------------------------
 * Function IsWeekend
 *
 * This function checks the supplied date to determine whether or not
 * the date is on a Saturday or Sunday.
 *
 * $date (string) string date in the form YYYY-MM-DD.
 * @return (bool)  TRUE if date supplied lies on a weekend. FALSE otherwise.
 * -------------------------------------------------------------------------- */
function isWeekend($date)
{
    //Assume false. Will set to true if it is a weekend
    $result = FALSE;
    
    //Convert the date supplied to a lower case textual day of the week.
    $date = strtotime($date);
    $date = date("l", $date);
    $date = strtolower($date);
    
    //check to see if this is is a weekend.
    if (($date == "saturday" )|| ($date == "sunday"))
    {
        $result = TRUE;
    }
    return $result;
}

/* -----------------------------------------------------------------------------
 * Function CalculateAnnualLeaveRequired
 *
 * This function calculates how many days of annual leave will be needed to 
 * book a period of time between two dates.
 *
 * $startDate (string) string date in the form YYYY-MM-DD.
 * $endDate   (string) string date in the form YYYY-MM-DD.
 * $absenceTypeID (int)  key of the absence type record.
 * @return (int)  Number of days annual leave required for this period. Will be
 *                zero if no leave is required.
 * -------------------------------------------------------------------------- */
function CalculateAnnualLeaveRequired($startDate,$endDate,$absenceTypeID)
{
    //Assume no leave is required. Will increment this in the function.
    $annualLeaveRequired = 0;
    
    $absenceType = RetrieveAbsenceTypeByID($absenceTypeID);
    
    // Check to ensure that an absence type of this ID actually exists.
    if ($absenceType <> NULL)
    {
    	//Q.Does the absence type supplied use annual leave?
    	if ($absenceType[ABS_TYPE_USES_LEAVE] == TRUE)
    	{
            //Y.We need to calulate the leave required. First convert dates supplied
            //  into times.
            $startTime = strtotime($startDate);
            $endTime = strtotime($endDate);

            // Loop between timestamps, 24 hours at a time.
            // Note that 86400 = 24 hours in second.
            for ($i = $startTime; $i <= $endTime; $i = $i + 86400) 
            {
                //Format the time into a date string
                $thisDate = date('Y-m-d', $i); // 2010-05-01, 2010-05-02, etc
            
                if (!isWeekend($thisDate))
                {
                    if (!isPublicHoliday($thisDate) )
                    {
                        //Date is not a weekend or public holiday, so increment
                        $annualLeaveRequired = $annualLeaveRequired + 1;
                    }
                }
            }
    	}
    }
    else
    {
        error_log("Unknown absence type identifier of $absenceTypeID");
    }
    return $annualLeaveRequired;
}

/* ----------------------------------------------------------------------------
 * Function CalculateRemainingAnnualLeave
 *
 * This function calculates the number of days annual leave that an employee
 * has remaining.
 *
 * $employeeID(int) ID of the employee to calculate this for.
 *
 * @return (int) Number of days annual leave available to the employee.
 * -------------------------------------------------------------------------- */
function CalculateRemainingAnnualLeave($employeeID)
{
    //Assume no leave is remaining. Will increment this in the function.
    $annualLeaveRemaining = 0;
    
    $employee = RetrieveEmployeeByID($employeeID);
    if ($employee <> NULL)
    {
    	//Start with the annual leave entitlement for the employee. 
    	$annualLeaveRemaining = $employee[EMP_LEAVE_ENTITLEMENT];
        
       	//Get all of the absence bookings for this employee.
        $filter[APPR_ABS_EMPLOYEE_ID] = $employeeID;
        $bookings = RetrieveApprovedAbsenceBookings($filter);
        
        //Q. Does employee have any absence bookings?
        if ($bookings)
        {
        	//Yes. For each booking.....
            foreach ($bookings as $booking)
            {
               	//Calculate how much leave is needed for the booking.
                $startDate   = $booking[APPR_ABS_START_DATE]; 
                $endDate     = $booking[APPR_ABS_END_DATE];
                $absenceType = $booking[APPR_ABS_ABS_TYPE_ID];
                
                $leaveRequired = CalculateAnnualLeaveRequired($startDate,
                                                              $endDate,
                                                              $absenceType);
                
                //subtract this from the annual leave entitlement.
                $annualLeaveRemaining = $annualLeaveRemaining - $leaveRequired;
            }
        }
    }
    else
    {
		error_log("Unknown employee identifier of $employeeID");
    }
    
    return $annualLeaveRemaining;
}

/* ----------------------------------------------------------------------------
 * Function HasSufficientAnnualLeave
 *
 * This function will determine whether an employee has sufficent annual leave
 * available to cover the period of absence between start date and end date, 
 * taking into account the absence type of the request.
 *
 * $employeeID(int) ID of the employee to calculate this for.
 * $startDate(string) start date of the request in the form YYYY-MM-DD.
 * $endDate(string) end date of the request in the form YYYY-MM-DD.
 * $absenceTypeID(int) ID of the absence type of this request.
 *
 * @return (bool) TRUE means sufficent days to cover the requested period.
 *                FALSE means insufficent days to cover the requested period. 
 * -------------------------------------------------------------------------- */
function HasSufficentAnnualLeave($employeeID,$startDate,$endDate,$absenceTypeID)
{
	$hasSufficentLeave = FALSE;
	
	// Firstly, calculate how much leave the employee has remaining.
	$employeesAvailableLeave = CalculateRemainingAnnualLeave($employeeID);
	
	// then calculate how much leave is needed for the period requested.
	$amountOfLeaveNeeded = CalculateAnnualLeaveRequired($startDate,$endDate,$absenceTypeID);
	
	// If amount of leave required is less than or equal to available leave then
	// then the employee has sufficent leave available.
	if ($amountOfLeaveNeeded <= $employeesAvailableLeave)
	{
		$hasSufficentLeave = TRUE;
	}
	
	return $hasSufficentLeave;
}


/* ----------------------------------------------------------------------------
 * Function CountStaffOnLeave
 *
 * This function will calculate the number of staff in a given role who are 
 * on leave on a given date.
 *
 * $roleID(int) ID of the role we are checking
 * $date(string) date we are checking. In the format YYYY-MM-DD.
 *
 * @return (bool) TRUE means there are sufficent staff to grant the request.
 *                FALSE means there are insufficent staff to grant the request. 
 * -------------------------------------------------------------------------- */
 function CountStaffOnLeave($roleID,$date)
{
    //Assume no staff on leave to begin with.
    $countOfStaffOnLeave = 0;
	
    //Retrieve the date record from the database.
    $dateRecord = RetrieveDateRecordByDate($date);
    if ($dateRecord <> NULL)
    {
        //Retrieve all approved absence bookings for this date.
            $dateID = $dateRecord[DATE_TABLE_DATE_ID];
            $filter[APPR_ABS_BOOK_DATE_DATE_ID] = $dateID;
            $bookingsForDate = RetrieveApprovedAbsenceBookingDates($filter);
            if ($bookingsForDate <> NULL)
            {
        	//One or more bookings exist. itterate through them.
        	foreach ($bookingsForDate as $bookingDate)
        	{
                    //Using the absence booking record, obtain the employee 
                    //record from the database
                    $absenceBooking = RetrieveApprovedAbsenceBookingByID(
                                  $bookingDate[APPR_ABS_BOOK_DATE_ABS_BOOK_ID]);
                    if ($absenceBooking <> NULL)
                    {
                        $staffMember = RetrieveEmployeeByID(
                                        $absenceBooking[APPR_ABS_EMPLOYEE_ID]);
           	 	//-----------------------------------------------------
                        // Check to see if this member of staff performs the 
                        // same role as the role of the employee requesting this 
                        // leave. If so, add one to the count of staff on leave.
           	 	//-----------------------------------------------------
           	 	if ($staffMember[EMP_COMPANY_ROLE] == $roleID)
            		{
                            $countOfStaffOnLeave = $countOfStaffOnLeave + 1;
            		}
                    }
                    else
                    {
                        error_log("Unknown absence booking id of ".
                                $bookingDate[APPR_ABS_BOOK_DATE_ABS_BOOK_ID]);
                    }
        	}
            }
        }
        else
        {
            error_log("Unknown date of $date");
        }
    
        return $countOfStaffOnLeave;
}

/* ----------------------------------------------------------------------------
 * Function SufficentStaffInRoleToGrantRequest
 *
 * This function will determine whether there are sufficent staff within a role
 * to allow the employee to book the period of startDate to endDate as absence.
 *
 * $employeeID(int) ID of the employee to calculate this for.
 * $startDate(string) start date of the request in the form YYYY-MM-DD.
 * $endDate(string) end date of the request in the form YYYY-MM-DD.
 *
 * @return (bool) TRUE means there are sufficent staff to grant the request.
 *                FALSE means there are insufficent staff to grant the request. 
 * -------------------------------------------------------------------------- */
function SufficentStaffInRoleToGrantRequest($employeeID,$startDate,$endDate)
{
    $sufficentStaffInRole = TRUE;
	
    // Get the employee record from the database.
    $Employee = RetrieveEmployeeByID($employeeID);
    if ($Employee <> NULL)
    {
    	// Get the associated Company Role record from the database.
    	$employeeRole = RetrieveCompanyRoleByID($Employee[EMP_COMPANY_ROLE]);
    	if ($employeeRole <> NULL)
    	{
            $minimumStaffingLevel = $employeeRole[COMP_ROLE_MIN_STAFF];
	
            //Calculate the total number of employees in this role.
            $filter[EMP_COMPANY_ROLE] = $Employee[EMP_COMPANY_ROLE];
            $employeesInRole = RetrieveEmployees($filter);
            $numEmployeesInRole = count($employeesInRole);

            //Check staffing levels for each day in the period requested. 
            $tempDate = strtotime($startDate);
            $endTime = strtotime($endDate);

            $underMinimumStaffing = FALSE;
	
            while ($tempDate <= $endTime AND $underMinimumStaffing == FALSE)
            {
                // 2010-05-01, 2010-05-02, etc
	    	$strDate = date('Y-m-d', $tempDate); 
                //Calculate the number of staff in this role that are on leave 
                //on this date.
                $staffOnLeave = CountStaffOnLeave($Employee[EMP_COMPANY_ROLE],
                                                  $strDate);
	    
        	//Q.Would granting this leave would take us below the minimum
        	//staffing level for the role.
        	$availableStaff = $numEmployeesInRole - $staffOnLeave;
        	if ( $availableStaff <= $minimumStaffingLevel)
        	{
                    //Y.Granting the request would take us below the minimum 
                    //staffing level for the role. 
                    $underMinimumStaffing = TRUE;
                    $sufficentStaffInRole = FALSE;
        	}
        
		//move temp date onto the next day. Note tempdate is in seconds.
                //86400 = 60 seconds * 60 minutes * 24 hours.
        	$tempDate = $tempDate + 86400; 
            }
    	}
    	else
    	{
            error_log("Unknown company role identifier of ".
                    $employee[EMP_COMPANY_ROLE]);
    	}
    }
    else
    {
		error_log("Unknown employee identifier of $employeeID");
    }
    return $sufficentStaffInRole;
}	

/* ----------------------------------------------------------------------------
 * Function ProcessAbsenceRequest
 *
 * This function will process an absence request, checking to ensure that:
 * a) The employee has sufficent leave available to cover the request.
 * b) That sufficent staff in the same role are working on that day to allow the 
 *    request. 
 *
 * $employeeID(int) ID of the employee that this absence request is for.
 * $startDate(string) start date of the request in the form YYYY-MM-DD.
 * $endDate(string) end date of the request in the form YYYY-MM-DD.
 * $absenceTypeID(int) ID of the absence type of this request.
 *
 * @return (bool) TRUE means the booking was approved.
 *                FALSE means the booking was denied. 
 * -------------------------------------------------------------------------- */
function ProcessAbsenceRequest($employeeID,$startDate,$endDate,$absenceTypeID,
                               &$statusMessage)
{
    //Assume that booking will be approved. Will be set to FALSE in function 
    //if necessary.
    $bookingApproved = TRUE;
   
    //--------------------------------------------------------------------------
    //Check to ensure if the employee has sufficent leave available to cover the
    //requested period.
    //--------------------------------------------------------------------------
    if (HasSufficentAnnualLeave($employeeID, $startDate, $endDate, 
                                $absenceTypeID) == FALSE)
    {
    	//Employee has insufficent leave available. Deny the request.
    	$statusMessage .= "Insufficent Annual Leave to cover the period ".
                          "requested.</br>";
        $message = "Insufficent Annual Leave to cover the period requested.";	
    	SendDeniedEmail($employeeID,$startDate,$endDate,$message);
        $bookingApproved = FALSE;
    }
    else
    {
        //----------------------------------------------------------------------
        //Check to ensure there are sufficent staff in the same role as employee
	//working to cover the request.
	//----------------------------------------------------------------------
        if (SufficentStaffInRoleToGrantRequest($employeeID, $startDate, $endDate))
        {
            //Sufficent staff are available, grant the request.
            CreateApprovedAbsenceBooking($employeeID, $startDate, $endDate, 
                                         $absenceTypeID);
            SendApprovedEmail($employeeID,$startDate,$endDate);
            $statusMessage .= "Absence Approved from $startDate to $endDate. ".
                               "Staff notifed via email.</br>";
            $bookingApproved = TRUE;
        }
        else
        {
            //------------------------------------------------------------------
            // Granting the request would mean going below the minimum staffing 
            // level for the role. However, if the type of absence requested is 
            // not deniable, then we have to grant the leave.
            //-----------------------------------------------------------------
            $absenceType = RetrieveAbsenceTypeByID($absenceTypeID);
            if ($absenceType[ABS_TYPE_CAN_BE_DENIED])
            {
             	//Type of leave requested can be denied. Deny the request.
                $bookingApproved = FALSE;
                $statusMessage .= "Absence Rejected from $startDate to $endDate.".
                                  "Request would leave role below minimum ".
                                  "staffing level. Staff notified via email.</br>";	
         
                $message = "Absence Rejected from $startDate to $endDate. ".
                           "Request would leave role below minimum staffing ".
                           "level. Staff notified via email.";	
                SendDeniedEmail($employeeID,$startDate,$endDate,$message);
            }
            else
            {
            	//--------------------------------------------------------------
            	//Type of leave requested can not be denied. Approve the request.
            	//But also inform the office manager that we will be going below  
            	//the minimum staffing level. 
            	//--------------------------------------------------------------
                $absenceType = RetrieveAbsenceTypeByID($absenceTypeID);
                $statusMessage .= "Absence Approved from $startDate to $endDate.".
                                  "Staff notifed via email.</br>";
                $statusMessage .= "<em>Note that the ".$absenceType[ABS_TYPE_NAME].
                                  " role will be under the minimum staffing level".
                                  " during this time. </br>";
                CreateApprovedAbsenceBooking($employeeID, $startDate, $endDate, 
                                             $absenceTypeID);
    	    	SendApprovedEmail($employeeID,$startDate,$endDate);
		SendShortfallAlertToOfficeManager($employeeID,$startDate,$endDate,
                                                  $absenceTypeID);

                $bookingApproved = TRUE;
            }
        }
    }
    
    return $bookingApproved;
}

/* -----------------------------------------------------------------------------
 * Function ProcessMainVacationRequests
 *
 * This function handles the processing of the Main Vacation Requests
 *
 * @return (int)  NULL if all requests were successfully processed.
 *               If not NULL, the return value indicates the employee ID
 *		whose main vacation request could not be granted.
 * -------------------------------------------------------------------------- */
function ProcessMainVacationRequests(&$statusMessage)
{
    $stoppedAtEmployeeID = NULL;
	
    //--------------------------------------------------------------------------
    // Need to get all main vacation requests in an order based on the employees
    // length of service, so use the bespoke SQL query below.
    //--------------------------------------------------------------------------
    $conn = $GLOBALS["connection"];
    
    $sql = "SELECT * FROM mainVacationRequestTable JOIN EmployeeTable ".
           "WHERE mainVacationRequestTable.EmployeeID = EmployeeTable.EmployeeID ".
           "ORDER BY EmployeeTable.dateJoinedTheCompany;";
    
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        $statusMessage.="SQL Error when accessing database.</br>";
        error_log("PerformSQL failed. Sql = $sql");
    }
    else 
    {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $lastRequestGranted = TRUE;
		
	//---------------------------------------------------------------------
	// Process each request in turn until either all have been processed, 
	// or we reach a request that can't be granted.
	//---------------------------------------------------------------------
        while ($row and $lastRequestGranted)
        {
            $id                         = $row[MAIN_VACATION_REQ_ID];
            $employeeID                 = $row[MAIN_VACATION_EMP_ID];
    	    $firstChoiceStartDate       = $row[MAIN_VACATION_1ST_START];
            $firstChoiceEndDate         = $row[MAIN_VACATION_1ST_END];
            $secondChoiceStartDate      = $row[MAIN_VACATION_2ND_START];
            $secondChoiceEndDate        = $row[MAIN_VACATION_2ND_END];
            $absenceTypeID              = GetAnnualLeaveAbsenceTypeID();
            $Employee                   = RetrieveEmployeeByID($employeeID);
            
            
            $statusMessage.="<b>[Processing main vacation request for ".
                    $Employee[EMP_NAME]."]</b><br/>";
            $statusMessage.="Attempting first choice from $firstChoiceStartDate ".
                            "to $firstChoiceEndDate.</br>";
            
	    //Try and approve first choice request.
	    if ( ProcessAbsenceRequest($employeeID,$firstChoiceStartDate,
                                    $firstChoiceEndDate,$absenceTypeID,
                                    $statusMessage) == FALSE)
	    {
                //First choice denied. Try and approve second choice request.
                $statusMessage.="Attempting second choice from ".
                                "$secondChoiceStartDate to $secondChoiceEndDate.</br>";
                if ( ProcessAbsenceRequest($employeeID,$secondChoiceStartDate,
                                           $secondChoiceEndDate,$absenceTypeID,
                                           $statusMessage) == FALSE)
		{
                    //second choice denied. Mail employee to instruct them to 
                    //submit a new booking.
                    $statusMessage .="Neither first nor second choice could be ".
                                     "granted. Employee has been emailed to ask ".
                                     "for new dates.</br>";
                    SendResubmitMainVacationRequest($employeeID);

                    //set the ID of the employee who needs to resubmit, to 
                    //pass back to the calling function.
                    $lastRequestGranted = FALSE;
                    $stoppedAtEmployeeID = $employeeID;
	        }
	    }
	    	
	    //Delete the main vacation request.
	    DeleteMainVacationRequest($id);
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
  	}
    }
    return $stoppedAtEmployeeID;
}


/* -----------------------------------------------------------------------------
 * Function ProcessAdHocRequests.
 *
 * This function itterates through the AdHoc absence requests, processing each
 * one.
 *
 * @return (none) 
 * -------------------------------------------------------------------------- */
function ProcessAdHocRequests(&$statusMessage)
{
    $requests = RetrieveAdHocAbsenceRequests();
    $return = true;
    
    foreach ($requests as $request)
    {
    	$id 			= $request[AD_HOC_REQ_ID];
    	$employeeID		= $request[AD_HOC_EMP_ID];
	$startDate		= $request[AD_HOC_START];
	$endDate		= $request[AD_HOC_END];
	$absenceTypeID  = $request[AD_HOC_ABSENCE_TYPE_ID];
        
        $employee = RetrieveEmployeeByID($employeeID);
        $statusMessage.="<b>[Processing AdHoc request for ".
                        $employee[EMP_NAME]."]</b><br/>";

    	$result = ProcessAbsenceRequest($employeeID,$startDate,$endDate,
                                        $absenceTypeID,$statusMessage);
        if ($result == false)
        {
            $return = false;
        }
        
    	DeleteAdHocAbsenceRequest($id);
    }
    return $return;
}

?>
