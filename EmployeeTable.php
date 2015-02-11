<?php

/* ----------------------------------------------------------------------------
 * CONSTANTS
 *
 * These constants should be used when refering to the table and the fields 
 * within its records.
 * ---------------------------------------------------------------------------*/
define("EMPLOYEE_TABLE", "employeeTable");
define("EMP_ID", "employeeID");
define("EMP_NAME", "employeeName");
define("EMP_EMAIL", "emailAddress");
define("EMP_PASSWORD", "password");
define("EMP_DATEJOINED", "dateJoinedTheCompany");
define("EMP_LEAVE_ENTITLEMENT", "annualLeaveEntitlement");
define("EMP_MAIN_VACATION_REQ_ID", "mainVacationRequestID");
define("EMP_COMPANY_ROLE", "companyRole_companyRoleID");
define("EMP_ADMIN_PERM","adminPermissions");
define("EMP_MANAGER_PERM","managerPermissions");


/* -----------------------------------------------------------------------------
 * Function IsValidPassword
 *
 * This function checks the supplied password string to determine whether it
 * conforms to the following password rules.
 * 
 * 1. Must be a minimum of 6 characters in length.
 * 2. Must contain one numeric character.
 * 3. Must contain at least one lower case character.
 * 4. Must contain at least one upper case character.
 *
 * $password (string) the password to check.
 * @return (array)  Array of errors. If array is empty (count(array)==0) then no
 *                  errors occured. Otherwise, each element of the array contains
 *                  a description of the error(s).
 * -------------------------------------------------------------------------- */
function isValidPassword($password)
{
	//Empty error array for the errors if any
	$error = array();
	
	//1. Must be a minimum of 6 characters in length.
	if( strlen($password) < 6 ) 
	{
		$error[] = 'Password need to have at least 6 characters!';
	}
 
	//2. Must contain one numeric character.
 	if( !preg_match("#[0-9]+#", $password) ) 
 	{
		$error[] = 'Password must include at least one number!';
	}
 
	//3. Must contain at least one lower case character.
 	if( !preg_match("#[a-z]+#", $password) ) 
 	{
		$error[] = 'Password must include at least one lowercase letter!';
	}
 
	//4. Must contain at least one upper case character.
 	if( !preg_match("#[A-Z]+#", $password) ) 
 	{
		$error[] = 'Password must include at least one uppercase letter!';
	}
 
	return $error;
}


/* ----------------------------------------------------------------------------
 * Function CreateEmployeeTable
 *
 * This function creates the SQL statement needed to construct the table
 * in the database.
 *
 * @return (bool)  True if table is created successfully, false otherwise.
 * -------------------------------------------------------------------------- */

function CreateEmployeeTable() {
    $sql = "CREATE TABLE IF NOT EXISTS `mydb`.`EmployeeTable` (
         `employeeID` INT NOT NULL AUTO_INCREMENT,
         `employeeName` VARCHAR(50) NOT NULL,
         `emailAddress` VARCHAR(50) NOT NULL,
         `password` VARCHAR(64) NOT NULL,
         `dateJoinedTheCompany` DATE NOT NULL,
         `annualLeaveEntitlement` INT(1) NOT NULL,
         `mainVacationRequestID` INT NULL,
         `companyRole_companyRoleID` INT NOT NULL,
         `adminPermissions` TINYINT(1) NOT NULL,
         `managerPermissions` TINYINT(1) NOT NULL,
         PRIMARY KEY (`employeeID`),
         INDEX `fk_Employee_companyRole1_idx` (`companyRole_companyRoleID` ASC),
         CONSTRAINT `fk_Employee_companyRole1`
         FOREIGN KEY (`companyRole_companyRoleID`)
         REFERENCES `mydb`.`companyRoleTable` (`companyRoleID`)
         ON DELETE NO ACTION
         ON UPDATE NO ACTION);";

    performSQL($sql);
}

/* ----------------------------------------------------------------------------
 * Function CreateEmployee
 *
 * This function creates a new Employee record in the table.
 *
 * $employeeName (string)  Name of the employee
 * $emailAddress (string)  Email address of the employee
 * $password (string)      Password for the employee
 * $dateJoinedTheCompany (string) Date joined the company. Must be in the form 
 *                                YYYY-MM-DD
 * $annualLeaveEntitlement (int) Number of days annual leave that the employee 
 *                               is entitled to.
 * $mainVacationRequestID (int) ID of the main Vacation Request record  
 *                         associated with this employee. Note this parameter  
 *                         may be set to NULL if the employee has no 
 *                         mainVacationRequest at time of creation.
 * $companyRoleID (int) ID of the company role record associated with this 
 *                      employee.
 *
 * @return (array) If successful, an array is returned where each key represents 
 *                 a field in the record. If unsuccessful, the return will be NULL.
 * -------------------------------------------------------------------------- */

function CreateEmployee($employeeName, $emailAddress, $password, 
                        $dateJoinedTheCompany, $annualLeaveEntitlement, 
                        $mainVacationRequestID, $companyRoleID, 
                        $isAdministrator = 0, $isManager =0) {
    
    $statusMessage = "";
    $employee = NULL;
    //--------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------
    $inputIsValid = TRUE;

    if (isNullOrEmptyString($employeeName)) {
        $statusMessage .= "Employee Name can not be blank.<br/>";
        error_log("Invalid employeeName passed to CreateEmployee.");
        $inputIsValid = FALSE;
    }

    if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
        $statusMessage .= "Email address given is not a valid email format.<br/>";
        error_log("Invalid email address passed to CreateEmployee.");
        $inputIsValid = FALSE;
    }

    $errorArray = isValidPassword($password);
	
    if (count($errorArray) <> 0) 
    {
    	foreach ($errorArray as $key=>$value)
    	{
            $statusMessage .= $value."<br/>";
            error_log($value);
        }
        $inputIsValid = FALSE;
    }

    if (!isValidDate($dateJoinedTheCompany)) {
        $statusMessage .= "Value given for Date joined the company is not a ".
                          "valid date.<br/>";
        error_log("Invalid dateJoinedTheCompany passed to CreateEmployee.");
        $inputIsValid = FALSE;
    }

    if (!is_numeric($annualLeaveEntitlement)) {
        $statusMessage .= "Please enter a valid value for annual leave ".
                          "entitlement.<br/>";
        error_log("Invalid annualLeaveEntitlement passed to CreateEmployee.");
        $inputIsValid = FALSE;
    }

    if ($mainVacationRequestID <> NULL) {
        $record = RetrieveMainVacationRequestByID($mainVacationRequestID);

        if ($record == NULL) {
            $statusMessage .= "Main Vacation Request ID does not exist in the ".
                              "database.<br/>";
            error_log("Invalid mainVacationRequestID passed to CreateEmployee.");
            $inputIsValid = FALSE;
        }
    }

    $record = RetrieveCompanyRoleByID($companyRoleID);

    if ($record == NULL) {
        $statusMessage .= "Company Role ID does not exist in the database.<br/>";
        error_log("Invalid companyRoleID passed to CreateEmployee.");
        $inputIsValid = FALSE;
    }
    
    //Ensure email address doesn't already exist in the database.
    $filter[EMP_EMAIL] = $emailAddress;
    $result = RetrieveEmployees($filter);
    
    if ($result <> NULL)
    {
        $statusMessage .= "Unable to create record as a user with email address "
                       ."$emailAddress already exists.<br/>";
        error_log("Unable to create record as a user with email address "
                  ."$emailAddress already exists");
        $inputIsValid = FALSE;
    }

    //--------------------------------------------------------------------------
    // Only attempt to insert a record in the database if the input parameters 
    // are ok.
    //--------------------------------------------------------------------------
    if ($inputIsValid) {
        // Create an array with each field required in the record. 
        $employee[EMP_ID] = NULL;
        $employee[EMP_NAME] = $employeeName;
        $employee[EMP_EMAIL] = $emailAddress;
        
        $encryptedPassword = md5(md5($emailAddress).$password);
        
        $employee[EMP_PASSWORD] = $encryptedPassword;
        $employee[EMP_DATEJOINED] = $dateJoinedTheCompany;
        $employee[EMP_LEAVE_ENTITLEMENT] = $annualLeaveEntitlement;
        $employee[EMP_MAIN_VACATION_REQ_ID] = $mainVacationRequestID;
        $employee[EMP_COMPANY_ROLE] = $companyRoleID;
        $employee[EMP_ADMIN_PERM] = $isAdministrator;
        $employee[EMP_MANAGER_PERM] = $isManager;

        $success = sqlInsertEmployee($employee);
        if (!$success) {
            $statusMessage .= "Unexpected error when inserting the record to ".
                              "the database.<br/>";
            error_log("Failed to create Employee. " . print_r($employee));
            $employee = NULL;
            $inputIsValid = false;
        }
        else
        {
            $statusMessage = "Record Created Successfully.";
        }
    }
    
    GenerateStatus($inputIsValid, $statusMessage);
    return $employee;
}

/* ----------------------------------------------------------------------------
 * Function sqlInsertEmployee 
 *
 * This function constructs the SQL statement required to insert a new record
 * into the employee table.
 *
 * &$employee(array) Array containing all of the fields required for the record.
 *
 * @return (bool) TRUE if insert into database was successful, false otherwise.
 * 		   
 * Note: If successful then the EMP_ID entry in the 
 * array passed by the caller will be set to the ID of the record in the 
 * database. 
 * ---------------------------------------------------------------------------*/

function sqlInsertEmployee(&$employee) {
    $sql = "INSERT INTO EmployeeTable (employeeName,emailAddress,password," .
            "annualLeaveEntitlement,dateJoinedTheCompany,".
            "companyRole_companyRoleID,adminPermissions,managerPermissions) " .
            "VALUES ('".$employee[EMP_NAME]."','". $employee[EMP_EMAIL]. "','"
            . $employee[EMP_PASSWORD] . "','".$employee[EMP_LEAVE_ENTITLEMENT] .
            "','".$employee[EMP_DATEJOINED]."','".$employee[EMP_COMPANY_ROLE] .
            "','".$employee[EMP_ADMIN_PERM]."','".$employee[EMP_MANAGER_PERM]."');";

    $employee[EMP_ID] = performSQLInsert($sql);
    return $employee[EMP_ID] <> 0;
}

/* ----------------------------------------------------------------------------
 * Function RetrieveEmployeeByID
 *
 * This function uses the ID supplied as a parameter to construct an SQL select 
 * statement and then performs this query, returning an array containing the key 
 * value pairs of the record (or NULL if no record is found matching the id).
 *
 * $id (int) id of the record to retrieve from the database..
 *
 * @return (array) array of key value pairs representing the fields in the 
 *                  record, or NULL if no record exists with the id supplied.
 * ---------------------------------------------------------------------------*/

function RetrieveEmployeeByID($id) {
    $filter[EMP_ID] = $id;
    $resultArray = performSQLSelect(EMPLOYEE_TABLE, $filter);

    $result = NULL;

    if (count($resultArray) == 1) {      //Check to see if record was found.
        $result = $resultArray[0];
    }

    return $result;
}

/* ----------------------------------------------------------------------------
 * Function RetrieveEmployees
 *
 * This function constructs the SQL statement required to query the employees 
 * table.
 *
 * $filter (array) Optional parameter. If supplied, then the array should 
 *                 contain a set of key value pairs, where the keys correspond ≈
 *                 contain a set in the record (see constants at top of file) 
 *                 and the values correspond to the values to filter against 
 *                 (IE: The WHERE clause).
 *
 * @return (array) If successful, an array of arrays, where each element 
 *                 corresponds to a row from the query. If a failure occurs, 
 *                 return will be NULL. 
 * ---------------------------------------------------------------------------*/

function RetrieveEmployees($filter = NULL) {
    $inputIsValid = TRUE;

    //-------------------------------------------------------------------------
    // Validate Input parameters
    //-------------------------------------------------------------------------
    if ($filter <> NULL) {
        foreach ($filter as $key => $value) {
            if (strcmp($key, EMP_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid EMP_ID of " . $value .
                            " passed to RetrieveEmployees.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, EMP_NAME) == 0) {
                if (isNullOrEmptyString($value)) {
                    error_log("Invalid EMP_NAME passed to RetrieveEmployees.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, EMP_EMAIL) == 0) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    error_log("Invalid EMP_EMAIL of " . $value .
                            " passed to RetrieveEmployees.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, EMP_PASSWORD) == 0) {
                if (isNullOrEmptyString($value)) {
                    error_log("Invalid EMP_PASSWORD passed to RetrieveEmployees.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, EMP_DATEJOINED) == 0) {
                if (!isValidDate($value)) {
                    error_log("Invalid EMP_DATEJOINED of " . $value .
                            " passed to RetrieveEmployees.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, EMP_LEAVE_ENTITLEMENT) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid EMP_LEAVE_ENTITLEMENT of " . $value .
                            " passed to RetrieveEmployees.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, EMP_MAIN_VACATION_REQ_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid EMP_MAIN_VACATION_REQ_ID of " . $value .
                            " passed to RetrieveEmployees.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, EMP_COMPANY_ROLE) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid EMP_COMPANY_ROLE of " . $value .
                            " passed to RetrieveEmployees.");
                    $inputIsValid = FALSE;
                }
            } else {
                error_log("Unknown Filter " . $key . " passed to RetrieveEmployees.");
                $inputIsValid = FALSE;
            }
        }
    }

    //--------------------------------------------------------------------------
    // Only attempt to perform query in the database if the input parameters 
    // are ok.
    //--------------------------------------------------------------------------
    $result = NULL;
    if ($inputIsValid) {
        $result = performSQLSelect(EMPLOYEE_TABLE, $filter);
    }
    return $result;
}

/* -----------------------------------------------------------------------------
 * Function UpdateEmployee
 *
 * This function constructs the SQL statement required to update a row in 
 * the Employee table.
 *
 * $fields (array) array of key value pairs, where keys correspond to fields in 
 *                 the record (see constants at start of this file). Note, this 
 *                 array MUST provide the id of the record (EMP_ID) and one or 
 *                 more other fields to be updated. 
 *
 * @return (bool) TRUE if update succeeds. FALSE otherwise. 
 * -------------------------------------------------------------------------- */

function UpdateEmployee($fields) {
    $statusMessage = "";

    //-------------------------------------------------------------------------
    // Validate Input parameters
    //-------------------------------------------------------------------------
    $inputIsValid = TRUE;
    $validID = false;
    $countOfFields = 0;

    
    foreach ($fields as $key => $value) {
        if ($key == EMP_ID) {
            $record = RetrieveEmployeeByID($value);
            if ($record <> NULL) {
                $validID = true;
                $countOfFields++;
            }
        } else if ($key == EMP_NAME) {
            $countOfFields++;

            if (isNullOrEmptyString($value)) {
            
                $statusMessage .= "Employee name can not be blank.</br>";
                error_log("Invalid EMP_NAME passed to UpdateEmployee.");
                $inputIsValid = FALSE;
            }
        } else if ($key == EMP_EMAIL) {
            
            $countOfFields++;

            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            
                $statusMessage .= "Email address is not in a valid format.</br>";
                 error_log("Invalid email address passed to UpdateEmployee.");
                $inputIsValid = FALSE;
            }
        } else if ($key == EMP_PASSWORD) {
            //No validation on password, since this is an MD5 encoded string.
            $countOfFields++;

        } else if ($key == EMP_DATEJOINED) {
            
            $countOfFields++;

            if (!isValidDate($value)) {
            
                $statusMessage.="Date Joined value is not a valid date</br>";               
                error_log("Invalid EMP_DATEJOINED passed to UpdateEmployee.");
                $inputIsValid = FALSE;
            }
        } else if ($key == EMP_LEAVE_ENTITLEMENT) {
            $countOfFields++;
            
            if (!is_numeric($value)) {
            $statusMessage.="Employee Leave Entitlement must be a numeric value.</br>";               
                error_log("Invalid EMP_LEAVE_ENTITLEMENT passed to UpdateEmployee.");
                $inputIsValid = FALSE;
            }
        } else if ($key == EMP_MAIN_VACATION_REQ_ID) {
            if ($value <> NULL)
            {
                $record = RetrieveMainVacationRequestByID($value);
                   
                if ($record == NULL) {
            
                    $statusMessage.="Main Vacation Request ID not found in database.</br>";               
                    error_log("Invalid EMP_MAIN_VACATION_REQ_ID passed to UpdateEmployee.");
                    $inputIsValid = FALSE;
                    
                }
            }    
        } else if ($key == EMP_COMPANY_ROLE) {
            
            $countOfFields++;

            $record = RetrieveCompanyRoleByID($value);

            if ($record == NULL) {
                     
                $statusMessage.="Company Role ID not found in database.</br>";               
                error_log("Invalid EMP_COMPANY_ROLE passed to UpdateEmployee.");
                $inputIsValid = FALSE;
            }
        } else if($key == EMP_ADMIN_PERM)
        {
        	$countOfFields++;
        } else if($key == EMP_MANAGER_PERM)
        {
        	$countOfFields++;
        }
        else {
                          
            $statusMessage.="Unrecognised field of $key encountered.</br>";               
            error_log("Invalid field passed to UpdateEmployee. $key=" . $key);
            $inputIsValid = FALSE;
        }
    }
    if (!$validID) {
        $statusMessage .= "No valid ID supplied.</br>";
        error_log("No valid ID supplied in call to UpdateEmployee.");
        $inputIsValid = FALSE;
    }

    if ($countOfFields < 2) {
        $statusMessage .= "Insufficent fields supplied.</br>";
        error_log("Insufficent fields supplied in call to UpdateEmployee.");
        $inputIsValid = FALSE;
    }

    //-------------------------------------------------------------------------
    // Only attempt to update a record in the database if the input parameters 
    // are ok.
    //-------------------------------------------------------------------------
    $success = false;

    if ($inputIsValid) {

        $success = performSQLUpdate(EMPLOYEE_TABLE, EMP_ID, $fields);
        if ($success)
        {

            $statusMessage .= "Record has been successfully updated.";
        }
        else 
        {

            $inputIsValid = false;
            $statusMessage .= "Unexpected Database error encountered. Please ".
                               "contact your system administrator.";
        }
    }
    
    GenerateStatus($inputIsValid, $statusMessage);
    return $success;
}

/* -----------------------------------------------------------------------------
 * Function DeleteEmployee
 *
 * This function constructs the SQL statement required to delete a row in 
 * the employee table.
 *
 * $ID(integer) ID of the record to be removed from the table. This should be  
 *              set to the EMP_ID value of the record you wish to delete.
 *
 * @return (int) count of rows deleted. 0 means delete was unsuccessful. 
 * -------------------------------------------------------------------------- */

function DeleteEmployee($ID) {
    $result = 0;
    $statusMessage = "";
    
    $employee = RetrieveEmployeeByID($ID);

    if ($employee != NULL) {
        if ($employee[EMP_MAIN_VACATION_REQ_ID] <> NULL) {
            DeleteMainVacatioNRequest($employee[EMP_MAIN_VACATION_REQ_ID]);
        }

        $filter[AD_HOC_EMP_ID] = $ID;
        $adHocAbsenceRequests = RetrieveAdHocAbsenceRequests($filter);

        foreach ((array) $adHocAbsenceRequests as $value) {
            DeleteAdHocAbsenceRequest($value[AD_HOC_REQ_ID]);
        }

        unset($filter);
        $filter[APPR_ABS_EMPLOYEE_ID] = $ID;
        $approvedAbsenceBookings = RetrieveApprovedAbsenceBookings($filter);

        if ($approvedAbsenceBookings <> NULL) {
            foreach ($approvedAbsenceBookings as $value) {
                DeleteApprovedAbsenceBooking($value[APPR_ABS_BOOKING_ID]);
            }
        }

        $sql = "DELETE FROM employeeTable WHERE employeeID=" . $ID . ";";
        $result = performSQL($sql);
        
        $statusMessage .= "Record deleted.</br>";
        GenerateStatus(true,$statusMessage);
    }

    return $result;
}


/* -----------------------------------------------------------------------------
 * Function GetEmployeeCount
 *
 * This function gets a count of employee records which match a given filter.
 *
 * $filter(array) array of key value pairs representing the fields of the record
 *                that should be filtered into the count.
 *
 * @return (int) count of rows that match this filter. 
 * -------------------------------------------------------------------------- */

function GetEmployeeCount(&$totalEmployees,&$employeesWithNoMainVacation) 
{
    $conn = $GLOBALS["connection"];

    $sql = "SELECT COUNT(*) FROM ".EMPLOYEE_TABLE;
   
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        printCallstackAndDie();
    }
    $data = mysqli_fetch_array($result);
    $totalEmployees = $data[0];  
     
     
    $sql = "SELECT COUNT(*) FROM ".EMPLOYEE_TABLE." WHERE mainVacationRequestID ".
            "IS NULL";
   
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        printCallstackAndDie();
    }
    $data = mysqli_fetch_array($result);
    $employeesWithNoMainVacation = $data[0];  
}

?>
