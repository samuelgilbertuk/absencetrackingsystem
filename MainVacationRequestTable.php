<?php

/* -----------------------------------------------------------------------------
 * CONSTANTS
 *
 * These constants should be used when refering to the table and the fields 
 * within its records.
 * ---------------------------------------------------------------------------*/
define("MAIN_VACATION_REQUEST_TABLE", "mainVacationRequestTable");
define("MAIN_VACATION_REQ_ID", "mainVacationRequestID");
define("MAIN_VACATION_EMP_ID", "employeeID");
define("MAIN_VACATION_1ST_START", "firstChoiceStartDate");
define("MAIN_VACATION_1ST_END", "firstChoiceEndDate");
define("MAIN_VACATION_2ND_START", "secondChoiceStartDate");
define("MAIN_VACATION_2ND_END", "secondChoiceEndDate");

/* -----------------------------------------------------------------------------
 * Function CreateMainVacationRequestTable
 *
 * This function creates the SQL statement needed to construct the table
 * in the database.
 *
 * @return (bool)  True if table is created successfully, false otherwise.
 * ---------------------------------------------------------------------------*/

function CreateMainVacationRequestTable() {
    $sql = "CREATE TABLE IF NOT EXISTS `mydb`.`mainVacationRequestTable` (
         `mainVacationRequestID` INT NOT NULL AUTO_INCREMENT,
         `employeeID` INT NOT NULL,
         `firstChoiceStartDate` DATE NOT NULL,
         `firstChoiceEndDate` DATE NOT NULL,
         `secondChoiceStartDate` DATE NOT NULL,
         `secondChoiceEndDate` DATE NOT NULL,
         PRIMARY KEY (`mainVacationRequestID`),
         INDEX `fk_mainVacationRequest_Employee1_idx` (`employeeID` ASC),
         CONSTRAINT `fk_mainVacationRequest_Employee1`
         FOREIGN KEY (`employeeID`)
         REFERENCES `mydb`.`EmployeeTable` (`employeeID`)
         ON DELETE NO ACTION
         ON UPDATE NO ACTION);";

    performSQL($sql);
}

/* ----------------------------------------------------------------------------
 * Function CreateMainVacationRequest
 *
 * This function creates a new MainVacationRequestrecord in the table.
 *
 * $employeeID (int)  	   EmployeeID that this request relates to.
 * $firstChoiceStartDate (string)  string in the form YYYY-MM-DD. 
 *                                 start date of the first choice request.
 * $firstChoiceEndDate (string)  string in the form YYYY-MM-DD. 
 *                               end date of the first choice request.
 * $secondChoiceStartDate (string)  string in the form YYYY-MM-DD. 
 *                                 start date of the second choice request.
 * $secondChoiceEndDate (string)  string in the form YYYY-MM-DD. 
 *                               end date of the second choice request.
 *
 * @return (array) If successful, an array is returned where each key represents 
 *                 a field in the record. If unsuccessful, the return will be 
 *                 NULL.
 * ---------------------------------------------------------------------------*/

function CreateMainVactionRequest($employeeID, $firstChoiceStartDate, 
                                  $firstChoiceEndDate, $secondChoiceStartDate, 
                                  $secondChoiceEndDate) 
{
    $statusMessage = "";
    $request = NULL;
    //-------------------------------------------------------------------------
    // Validate Input parameters
    //-------------------------------------------------------------------------
    $inputIsValid = TRUE;

    $employee = RetrieveEmployeeByID($employeeID);

    if ($employee == NULL) {
        $statusMessage.="Unrecognised Employee ID.</br>";
        error_log("Invalid employeeID passed to CreateMainVacationRequest." .
                " value=" . $employeeID);
        $inputIsValid = FALSE;
    }

    if (!isValidDate($firstChoiceStartDate)) {
        $statusMessage.="1st Choice Start Date is not a valid Date.</br>";
        error_log("Invalid firstChoiceStartDate passed to CreateMainVacationRequest." .
                " value=" . $firstChoiceStartDate);
        $inputIsValid = FALSE;
    }

    if (!isValidDate($firstChoiceEndDate)) {
        $statusMessage.="1st Choice Finish Date is not a valid Date.</br>";
        error_log("Invalid firstChoiceEndDate passed to CreateMainVacationRequest." .
                " value=" . $firstChoiceEndDate);
        $inputIsValid = FALSE;
    }

    if (!isValidDate($secondChoiceStartDate)) {
        $statusMessage.="2nd Choice Start Date is not a valid Date.</br>";
        error_log("Invalid secondChoiceStartDate passed to CreateMainVacationRequest." .
                " value=" . $secondChoiceStartDate);
        $inputIsValid = FALSE;
    }

    if (!isValidDate($secondChoiceEndDate)) {
        $statusMessage.="2nd Choice Finish Date is not a valid Date.</br>";
        error_log("Invalid secondChoiceEndDate passed to CreateMainVacationRequest." .
                " value=" . $secondChoiceEndDate);
        $inputIsValid = FALSE;
    }
    
    if (strtotime($firstChoiceEndDate) < strtotime($firstChoiceStartDate)) 
    {
        $statusMessage.="1st Choice End Date is before 1st Choice Start Date.</br>";
        error_log("First Choice End Date is before First Choice Start Date.");
        $inputIsValid = FALSE;
    }
    
    if (strtotime($secondChoiceEndDate) < strtotime($secondChoiceStartDate)) 
    {
        $statusMessage.="2nd Choice End Date is before 2nd Choice Start Date.</br>";
        error_log("Second Choice End Date is before Second Choice Start Date.");
        $inputIsValid = FALSE;
    }
    

    //-------------------------------------------------------------------------
    // Only attempt to insert a record in the database if the input parameters 
    // are ok.
    //-------------------------------------------------------------------------
    if ($inputIsValid) {
        $request[MAIN_VACATION_REQ_ID] = NULL;
        $request[MAIN_VACATION_EMP_ID] = $employeeID;
        $request[MAIN_VACATION_1ST_START] = $firstChoiceStartDate;
        $request[MAIN_VACATION_1ST_END] = $firstChoiceEndDate;
        $request[MAIN_VACATION_2ND_START] = $secondChoiceStartDate;
        $request[MAIN_VACATION_2ND_END] = $secondChoiceEndDate;

        $success = sqlInsertMainVacationRequest($request);
        if (!$success) {
            $inputIsValid = false;

            $statusMessage.="Unexpected error encountered with database. ".
                            "Contact your system administrator.</br>";
            error_log("Failed to create main vacation request. ");
            $request = NULL;
        } 
        else 
        {
            //-----------------------------------------------------------------
            // Now that Main Vacation Request has been created, we need to 
            // update the employee record to reference it. First, chcek to see 
            // if the employee already has a main vacation request (in which 
            //  case we need to deltete it.
            //-----------------------------------------------------------------
            if ($employee[EMP_MAIN_VACATION_REQ_ID] <> NULL) {
                $count = DeleteMainVacationRequest(
                        $employee[EMP_MAIN_VACATION_REQ_ID]);
                if ($count == 0) {
                    $inputIsValid = false;
                    $statusMessage.="Unexpected error encountered when removing".
                                     " Main Vacation Request from Database. ".
                                     "Contact your system administrator.</br>";
                    error_log("Failed to delete main vacation request. ID=" .
                            $employee[EMP_MAIN_VACATION_REQ_ID]);
                }
            }

            //-----------------------------------------------------------------
            // Now update the employee record to reference the new 
            // MainVacationRequest record.
            //-----------------------------------------------------------------
            $employee[EMP_MAIN_VACATION_REQ_ID] = $request[MAIN_VACATION_REQ_ID];
            $success = UpdateEmployee($employee);
            if (!$success) {
                $statusMessage.="Failed to update employee to reference new ".
                                "main vacation request.";
                error_log("Failed to update employee to reference new " .
                        "main vacation request.");
                $inputIsValid = false;
            }
            else 
            {
                $statusMessage .= "Main Vacation Request successfully created.";
            }
        }
    }
    
    GenerateStatus($inputIsValid, $statusMessage);
    return $request;
}

/* ----------------------------------------------------------------------------
 * Function sqlInsertMainVacationRequest
 *
 * This function constructs the SQL statement required to insert a new record
 * into the mainVacationRequest table.
 *
 * &$request(array) Array containing all of the fields required for the record.
 *
 * @return (bool) TRUE if insert into database was successful, false otherwise.
 * 		   
 * Note: If successful then the MAIN_VACATION_REQ_ID entry in the 
 * array passed by the caller will be set to the ID of the record in the 
 * database. 
 * ---------------------------------------------------------------------------*/

function sqlInsertMainVacationRequest(&$request) {
    $sql = "INSERT INTO mainVacationRequestTable (employeeID,".
            "firstChoiceStartDate,firstChoiceEndDate," .
            "secondChoiceStartDate,secondChoiceEndDate) " .
            "VALUES ('" . $request[MAIN_VACATION_EMP_ID] .
            "','" . $request[MAIN_VACATION_1ST_START] . 
            "','" . $request[MAIN_VACATION_1ST_END] .
            "','" . $request[MAIN_VACATION_2ND_START] . 
            "','" . $request[MAIN_VACATION_2ND_END] . "');";
    $request[MAIN_VACATION_REQ_ID] = performSQLInsert($sql);
    return $request[MAIN_VACATION_REQ_ID] <> 0;
}

/* -----------------------------------------------------------------------------
 * Function RetrieveMainVacationRequestByID
 *
 * This function uses the ID supplied as a parameter to construct an SQL select
 * statement and then performs this query, returning an array containing the key 
 * value pairs of the record (or NULL if no record is found matching the id).
 *
 * $id (int) id of the record to retrieve from the database..
 *
 * @return (array) array of key value pairs representing the fields in the 
 *                 record, or NULL if no record exists with the id supplied.
 * ---------------------------------------------------------------------------*/

function RetrieveMainVacationRequestByID($id) {
    $filter[MAIN_VACATION_REQ_ID] = $id;
    $resultArray = performSQLSelect(MAIN_VACATION_REQUEST_TABLE, $filter);

    $result = NULL;

    if (count($resultArray) == 1) {      //Check to see if record was found.
        $result = $resultArray[0];
    }

    return $result;
}

/* ----------------------------------------------------------------------------
 * Function RetrieveMainVacationRequests
 *
 * This function constructs the SQL statement required to query the 
 * MainVacationRequest table.
 *
 * $filter (array) Optional parameter. If supplied, then the array should 
 *                 contain a set of key value pairs, where the keys correspond 
 *                 to one (or more) fields in the record (see constants at top 
 *                 of file) and the values correspond to the values to filter 
 *                 against (IE: The WHERE clause).
 *
 * @return (array) If successful, an array of arrays, where each element 
 *                 corresponds to a row from the query. If a failure occurs, 
 *                 return will be NULL. 
 * ---------------------------------------------------------------------------*/

function RetrieveMainVacationRequests($filter = NULL) {
    $inputIsValid = TRUE;

    //-------------------------------------------------------------------------
    // Validate Input parameters
    //-------------------------------------------------------------------------
    if ($filter <> NULL) {
        foreach ($filter as $key => $value) {
            if (strcmp($key, MAIN_VACATION_REQ_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid MAIN_VACATION_REQ_ID of " . $value .
                            " passed to RetrieveMainVacationRequests.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, MAIN_VACATION_EMP_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid MAIN_VACATION_EMP_ID of " . $value .
                            " passed to RetrieveMainVacationRequests.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, MAIN_VACATION_1ST_START) == 0) {
                if (!isValidDate($value)) {
                    error_log("Invalid MAIN_VACATION_1ST_START of " . $value .
                            " passed to RetrieveMainVacationRequests.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, MAIN_VACATION_1ST_END) == 0) {
                if (!isValidDate($value)) {
                    error_log("Invalid MAIN_VACATION_1ST_END of " . $value .
                            " passed to RetrieveMainVacationRequests.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, MAIN_VACATION_2ND_START) == 0) {
                if (!isValidDate($value)) {
                    error_log("Invalid MAIN_VACATION_2ND_START of " . $value .
                            " passed to RetrieveMainVacationRequests.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, MAIN_VACATION_2ND_END) == 0) {
                if (!isValidDate($value)) {
                    error_log("Invalid MAIN_VACATION_2ND_END of " . $value .
                            " passed to RetrieveMainVacationRequests.");
                    $inputIsValid = FALSE;
                }
            } else {
                error_log("Unknown Filter " . $key . 
                        " passed to RetrieveMainVacationRequests.");
                $inputIsValid = FALSE;
            }
        }
    }

    //-------------------------------------------------------------------------
    // Only attempt to perform query in the database if the input parameters 
    // are ok.
    //-------------------------------------------------------------------------
    $result = NULL;
    if ($inputIsValid) {
        $result = performSQLSelect(MAIN_VACATION_REQUEST_TABLE, $filter);
    }
    return $result;
}

/* ----------------------------------------------------------------------------
 * Function UpdateMainVacationRequest
 *
 * This function constructs the SQL statement required to update a row in 
 * the MainVacationRequest table.
 *
 * $fields (array) array of key value pairs, where keys correspond to fields 
 *                 in the record (see constants at start of this file). Note, 
 *                 this array MUST provide the id of the record 
 *                 (MAIN_VACATION_REQ_ID) and one or more other fields to be 
 *                 updated. 
 *
 * @return (bool) TRUE if update succeeds. FALSE otherwise. 
 * ------------------------------------------------------------------------- */

function UpdateMainVacactionRequest($fields) {
    $success = false;
    $statusMessage = "";
    //-------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------
    $inputIsValid = TRUE;
    $validID = false;
    $countOfFields = 0;
    
    foreach ($fields as $key => $value) {
        if ($key == MAIN_VACATION_REQ_ID) {
            $record = RetrieveMainVacationRequestByID($value);
            if ($record <> NULL) {
                $validID = true;
                $countOfFields++;
            }
        } else if ($key == MAIN_VACATION_EMP_ID) {
            $countOfFields++;

            $record = RetrieveEmployeeByID($value);
            if ($record == NULL) {
                $statusMessage .="Invalid Main Vacation Employee ID</br>";
                error_log("Invalid MAIN_VACATION_EMP_ID passed to " .
                        "UpdateMainVacationRequest.");
                $inputIsValid = FALSE;
            }
        } else if ($key == MAIN_VACATION_1ST_START) {
            $countOfFields++;

            if (!isValidDate($value)) {
                $statusMessage .="Invalid 1st Choice Start Date</br>";
                error_log("Invalid MAIN_VACATION_1ST_START passed to ".
                          "UpdateMainVacationRequest.");
                $inputIsValid = FALSE;
            }
        } else if ($key == MAIN_VACATION_1ST_END) {
            $countOfFields++;

            if (!isValidDate($value)) {
                $statusMessage .="Invalid 1st Choice Finish Date/br>";
                error_log("Invalid MAIN_VACATION_1ST_END passed to ".
                          "UpdateMainVacationRequest.");
                $inputIsValid = FALSE;
            }
        } else if ($key == MAIN_VACATION_2ND_START) {
            $countOfFields++;

            if (!isValidDate($value)) {
                $statusMessage .="Invalid 2nd Choice Start Date/br>";
                error_log("Invalid MAIN_VACATION_2ND_START passed to ".
                          "UpdateMainVacationRequest.");
                $inputIsValid = FALSE;
            }
        } else if ($key == MAIN_VACATION_2ND_END) {
            $countOfFields++;

            if (!isValidDate($value)) {
                $statusMessage .="Invalid 2nd Choice Finish Date/br>";
                error_log("Invalid MAIN_VACATION_2ND_END passed to ".
                          "UpdateMainVacationRequest.");
                $inputIsValid = FALSE;
            }
        } else {
            $statusMessage .="Invalid Field encountered./br>";
            error_log("Invalid field passed to ".
                      "UpdateMainVacationRequest. $key=" . $key);
            $inputIsValid = FALSE;
        }
    }
    
    $firstChoiceStartDate = $fields[MAIN_VACATION_1ST_START];
    $firstChoiceEndDate = $fields[MAIN_VACATION_1ST_END];
    $secondChoiceStartDate = $fields[MAIN_VACATION_2ND_START];
    $secondChoiceEndDate = $fields[MAIN_VACATION_2ND_END];
    
    
    if (strtotime($firstChoiceEndDate) < strtotime($firstChoiceStartDate)) 
    {
        $statusMessage.="1st Choice End Date is before 1st Choice Start Date.</br>";
        error_log("First Choice End Date is before First Choice Start Date.");
        $inputIsValid = FALSE;
    }
    
    if (strtotime($secondChoiceEndDate) < strtotime($secondChoiceStartDate)) 
    {
        $statusMessage.="2nd Choice End Date is before 2nd Choice Start Date.</br>";
        error_log("Second Choice End Date is before Second Choice Start Date.");
        $inputIsValid = FALSE;
    }

    if (!$validID) {
        $statusMessage .="No valid record ID found/br>";
        error_log("No valid ID supplied in call to UpdateMainVacationRequest.");
        $inputIsValid = FALSE;
    }

    if ($countOfFields < 2) {
        $statusMessage .="You must modify at least one of the fields of the record./br>";
        error_log("Insufficent fields supplied in call to UpdateMainVacationRequest.");
        $inputIsValid = FALSE;
    }

    //--------------------------------------------------------------------------
    // Only attempt to update a record in the database if the input parameters 
    // are ok.
    //--------------------------------------------------------------------------
    if ($inputIsValid) {
        $success = performSQLUpdate(MAIN_VACATION_REQUEST_TABLE, 
                                    MAIN_VACATION_REQ_ID, $fields);
        if ($success)
        {
            $statusMessage.="Record successfully modified.";
        }
        else
        {
            $inputIsValid = false;
            $statusMessage.="Error encountered when updating the database. ".
                            "Contact system administrator.</br>";
        }
    }
    GenerateStatus($inputIsValid, $statusMessage);
    return $success;
}

/* ----------------------------------------------------------------------------
 * Function DeleteMainVacationRequest
 *
 * This function constructs the SQL statement required to delete a row in 
 * the MainVacationRequest table.
 *
 * $ID(integer) ID of the record to be removed from the table. This should be  
 *              set to the  MAIN_VACATION_REQ_ID value of the record you wish to 
 *              delete.
 *
 * @return (int) count of rows deleted. 0 means delete was unsuccessful. 
 * --------------------------------------------------------------------------*/

function DeleteMainVacationRequest($ID) {
    $result = 0;
    $record = RetrieveMainVacationRequestByID($ID);

    if ($record <> NULL) {
        $employee = RetrieveEmployeeByID($record[MAIN_VACATION_EMP_ID]);
        if ($employee) {
            $employee[EMP_MAIN_VACATION_REQ_ID] = NULL;
            UpdateEmployee($employee);
        }

        $sql = "DELETE FROM mainVacationRequestTable WHERE mainVacationRequestID=".
                $ID . ";";
        $result = performSQL($sql);
    }
    return $result;
}
?>