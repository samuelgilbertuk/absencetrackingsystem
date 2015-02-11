<?php

/* ----------------------------------------------------------------------------
 * CONSTANTS
 *
 * These constants should be used when refering to the table and the fields 
 * within its records.
 * -------------------------------------------------------------------------- */
define("ADHOC_ABSENCE_REQUEST_TABLE", "adHocAbsenceRequestTable");
define("AD_HOC_REQ_ID", "adHocAbsenceRequestID");
define("AD_HOC_EMP_ID", "employeeID");
define("AD_HOC_START", "startDate");
define("AD_HOC_END", "endDate");
define("AD_HOC_ABSENCE_TYPE_ID", "absenceTypeID");

/* ---------------------------------------------------------------------------
 * Function CreateAdHocAbsenceRequestTable
 *
 * This function creates the SQL statement needed to construct the table
 * in the database.
 *
 * @return (bool)  True if table is created successfully, false otherwise.
 * ---------------------------------------------------------------------------*/

function CreateAdHocAbsenceRequestTable() {
    $sql = "CREATE TABLE IF NOT EXISTS `mydb`.`adHocAbsenceRequestTable` (
  		 `adHocAbsenceRequestID` INT NOT NULL AUTO_INCREMENT,
  		 `employeeID` INT NOT NULL,
  		 `startDate` DATE NOT NULL,
  		 `endDate` DATE NOT NULL,
  		 `absenceTypeID` INT NOT NULL,
  		  PRIMARY KEY (`adHocAbsenceRequestID`),
  		  INDEX `fk_adHocAbsenceRequest_absenceType1_idx` (`absenceTypeID` ASC),
  		  INDEX `fk_adHocAbsenceRequest_Employee1_idx` (`employeeID` ASC),
  		  CONSTRAINT `fk_adHocAbsenceRequest_absenceType1`
    	  FOREIGN KEY (`absenceTypeID`)
    	  REFERENCES `mydb`.`absenceTypeTable` (`absenceTypeID`)
    	  ON DELETE NO ACTION
    	  ON UPDATE NO ACTION,
  		  CONSTRAINT `fk_adHocAbsenceRequest_Employee1`
    	  FOREIGN KEY (`employeeID`)
    	  REFERENCES `mydb`.`EmployeeTable` (`employeeID`)
    	  ON DELETE NO ACTION
    	  ON UPDATE NO ACTION);";

    performSQL($sql);
}

/* -----------------------------------------------------------------------------
 * Function CreateAdHocAbsenceRequest
 *
 * This function creates a new AdHocAbsenceRequest record in the 
 * AdHocAbsenceRequestTable.
 *
 * $employeeID (int) ID of the employee record associated with this request.
 * $startDate (string) Start date of the request. Should be in the form YYYY-MM-DD
 * $endDate (string)  End date of the request. Should be in the form YYYY-MM-DD
 * $absenceTypeID (int) ID of the absenceType record assocaited with this request.
 *
 * @return (array) If successful, an array is returned where each key represents
 *                  a field in the record. If unsuccessful, the return will be NULL.
 * -------------------------------------------------------------------------- */

function CreateAdHocAbsenceRequest($employeeID, $startDate, $endDate, $absenceTypeID) {
    $statusMessage = "";
    $request = NULL;
    //--------------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------------
    $inputIsValid = TRUE;

    $record = RetrieveEmployeeByID($employeeID);

    if ($record == NULL) {
        $statusMessage .= "Unable to locate the employees ID in the database.</br>";
        error_log("employeeID passed to CreateAdHocAbsenceRequest does not " .
                "exist in the database. ID=" . $employeeID);
        $inputIsValid = FALSE;
    }

    if (!isValidDate($startDate)) {
        $statusMessage .= "Start date given is not a valid date.</br>";
        error_log("Invalid start date passed to CreateAdHocAbsenceRequest.".
                  "date=".$startDate);
        $inputIsValid = FALSE;
    }

    if (!isValidDate($endDate)) {
        $statusMessage .= "End date given is not a valid date.</br>";
        error_log("Invalid end date passed to CreateAdHocAbsenceRequest. date=".
                $endDate);
        $inputIsValid = FALSE;
    }

    if (strtotime($endDate) < strtotime($startDate)) 
    {
        $statusMessage.="end Date is before start Date.</br>";
        error_log("End Date is before Start Date.");
        $inputIsValid = FALSE;
    }

    //ensure absenceType exists in the database.
    $record = RetrieveAbsenceTypeByID($absenceTypeID);

    if ($record == NULL) {
        $statusMessage .= "Unable to locate the absence type in the database.</br>";
        error_log("absenceType passed to CreateAdHocAbsenceRequest does not " .
                "exist in the database. ID=" . $absenceTypeID);
        $inputIsValid = FALSE;
    }
     

    //------------------------------------------------------------------------
    // Only attempt to insert a record in the database if the input parameters 
    // are ok.
    //------------------------------------------------------------------------
    if ($inputIsValid) {
        $request[AD_HOC_REQ_ID] = NULL;
        $request[AD_HOC_EMP_ID] = $employeeID;
        $request[AD_HOC_START] = $startDate;
        $request[AD_HOC_END] = $endDate;
        $request[AD_HOC_ABSENCE_TYPE_ID] = $absenceTypeID;

        $success = sqlInsertAdHocAbsenceRequest($request);

        if (!$success) {
            $isValidInput = false;
            $statusMessage .= "Unexpected issue when writing to the database.".
                              "Contact your system administrator.</br>";
            error_log("Failed to create Ad Hoc Absence Request.");
            $request = NULL;
        }
        else 
        {
            $statusMessage.="Record successfully created.";
        }
    }
    
    GenerateStatus($inputIsValid, $statusMessage);
    return $request;
}

/* ----------------------------------------------------------------------------
 * Function sqlInsertAdHocAbsenceRequest
 *
 * This function constructs the SQL statement required to insert a new record
 * into the adHocAbsenceRequest table.
 *
 * &$adHocAbsenceRequest (array) Array containing all of the fields required for
 *                               the record.
 * 
 * @return (bool) TRUE if insert into database was successful, false otherwise.
 * 		   
 * Note: If successful then the AD_HOC_REQ_ID entry in the array
 * 	     passed by the caller will be set to the ID of the record in the 
 *           database. 
 * ---------------------------------------------------------------------------*/

function sqlInsertAdHocAbsenceRequest(&$adHocAbsenceRequest) {
    $sql = "INSERT INTO adHocAbsenceRequestTable (employeeID,startDate,"
            . "endDate,absenceTypeID) "
            . "VALUES ('" . $adHocAbsenceRequest[AD_HOC_EMP_ID] .
            "','" . $adHocAbsenceRequest[AD_HOC_START] . "','" .
            $adHocAbsenceRequest[AD_HOC_END] .
            "','" . $adHocAbsenceRequest[AD_HOC_ABSENCE_TYPE_ID] . "');";
    $adHocAbsenceRequest[AD_HOC_REQ_ID] = performSQLInsert($sql);
    return $adHocAbsenceRequest[AD_HOC_REQ_ID] <> 0;
}

/* -----------------------------------------------------------------------------
 * Function RetrieveAdHocAbsenceRequestByID
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

function RetrieveAdHocAbsenceRequestByID($id) {
    $filter[AD_HOC_REQ_ID] = $id;
    $resultArray = performSQLSelect(ADHOC_ABSENCE_REQUEST_TABLE, $filter);

    $result = NULL;

    if (count($resultArray) == 1) {      //Check to see if record was found.
        $result = $resultArray[0];
    }

    return $result;
}

/* -----------------------------------------------------------------------------
 * Function RetrieveAdHocAbsenceRequests
 *
 * This function constructs the SQL statement required to query the 
 * AdHocAbsenceRequest table.
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
 * -------------------------------------------------------------------------- */

function RetrieveAdHocAbsenceRequests($filter = NULL) {
    $inputIsValid = TRUE;
    //--------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------
    if ($filter <> NULL) {
        foreach ($filter as $key => $value) {
            if (strcmp($key, AD_HOC_REQ_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid AdHocRequestID of " . $value .
                            " passed to RetrieveAdHocAbsenceRequests.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, AD_HOC_EMP_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("InvalidAD_HOC_EMP_ID of " . $value .
                            " passed to RetrieveAdHocAbsenceRequests.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, AD_HOC_START) == 0) {
                if (!isValidDate($value)) {
                    error_log("Invalid AD_HOC_START of " . $value .
                            " passed to RetrieveAdHocAbsenceRequests.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, AD_HOC_END) == 0) {
                if (!isValidDate($value)) {
                    error_log("Invalid AD_HOC_END of " . $value .
                            " passed to RetrieveAdHocAbsenceRequests.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, AD_HOC_ABSENCE_TYPE_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid AD_HOC_ABSENCE_TYPE_ID of " . $value .
                            " passed to RetrieveAdHocAbsenceRequests.");
                    $inputIsValid = FALSE;
                }
            } else {
                error_log("Unknown Filter " . $key . " passed to "
                        . "RetrieveAdHocAbsenceRequests.");
                $inputIsValid = FALSE;
            }
        }
    }

    //-------------------------------------------------------------------------
    // Only attempt to perform query in the database if the input parameters 
    // are ok.
    //------------------------------------------------------------------------
    $result = NULL;
    if ($inputIsValid) {
        $result = performSQLSelect(ADHOC_ABSENCE_REQUEST_TABLE, $filter);
    }

    return $result;
}

/* ----------------------------------------------------------------------------
 * Function UpdateAdHocAbsenceRequest
 *
 * This function constructs the SQL statement required to update a row in 
 * the AdHocAbsenceRequest table.
 *
 * $fields (array) array of key value pairs, where keys correspond to fields 
 *                 in the record (see constants at start of this file). Note, 
 *                 this array MUST provide the id of the record (AD_HOC_REQ_ID) 
 *                 and one or more other fields to be updated. 
 *
 * @return (bool) TRUE if update succeeds. FALSE otherwise. 
 * ---------------------------------------------------------------------------*/

function UpdateAdHocAbsenceRequest($fields) {
    $statusMessage = "";
    
    //-------------------------------------------------------------------------
    // Validate Input parameters
    //-------------------------------------------------------------------------
    $inputIsValid = TRUE;
    $validID = false;
    $countOfFields = 0;
    foreach ($fields as $key => $value) {
        if ($key == AD_HOC_REQ_ID) {
            $record = RetrieveAdHocAbsenceRequestByID($value);
            if ($record <> NULL) {
                $validID = true;
                $countOfFields++;
            }
        } else if ($key == AD_HOC_EMP_ID) {
            $countOfFields++;

            $record = RetrieveEmployeeByID($value);
            if ($record == NULL) {
                $statusMessage .= "Employee specified can not be found in the "
                               . "database.</br>";
                error_log("Invalid AD_HOC_EMP_ID passed to "
                          . "UpdateAdHocAbsenceRequest." .
                            " Value=" . $value);
                $inputIsValid = FALSE;
            }
        } else if ($key == AD_HOC_START) {
            $countOfFields++;

            if (!isValidDate($value)) {
                $statusMessage .= "Start date entered is not a valid date.</br>";
                error_log("Invalid AD_HOC_START passed to UpdateAdHocAbsenceRequest." .
                        " Value=" . $value);
                $inputIsValid = FALSE;
            }
        } else if ($key == AD_HOC_END) {
            $countOfFields++;

            if (!isValidDate($value)) {
                $statusMessage .= "End date entered is not a valid date.</br>";
                error_log("Invalid AD_HOC_END passed to UpdateAdHocAbsenceRequest." .
                        " Value=" . $value);
                $inputIsValid = FALSE;
            }
        } else if ($key == AD_HOC_ABSENCE_TYPE_ID) {
            $countOfFields++;

            $record = RetrieveAbsenceTypeByID($value);
            if ($record == NULL) {
                $statusMessage .= "Absence Type selected can not be found in the "
                               . "database.</br>";
                error_log("Invalid  AD_HOC_ABSENCE_TYPE_ID passed to " .
                        "UpdateAdHocAbsenceRequest. Value=" . $value);
                $inputIsValid = FALSE;
            }
        } else {
            $statusMessage .= "Unknown field encountered.</br>";
            error_log("Invalid field passed to UpdateAdHocAbsenceRequest."
                      ." $key=" . $key);
            $inputIsValid = FALSE;
        }
    }
    $startDate = $fields[AD_HOC_START];
    $endDate = $fields[AD_HOC_END];
    
    if (strtotime($endDate) < strtotime($startDate)) 
    {
        $statusMessage.="end Date is before start Date.</br>";
        error_log("End Date is before Start Date.");
        $inputIsValid = FALSE;
    }

    if (!$validID) {
        $statusMessage .= "No valid record ID found.</br>";
        error_log("No valid ID supplied in call to UpdateAbsenceType.");
        $inputIsValid = FALSE;
    }

    if ($countOfFields < 2) {
        $statusMessage .= "Insufficent fields supplied in call to UpdateAbsenceType.</br>";
        error_log("Insufficent fields supplied in call to UpdateAbsenceType.");
        $inputIsValid = FALSE;
    }

    //-------------------------------------------------------------------------
    // Only attempt to update a record in the database if the input parameters 
    // are ok.
    //-------------------------------------------------------------------------
    $success = false;

    if ($inputIsValid) {
        $success = performSQLUpdate(ADHOC_ABSENCE_REQUEST_TABLE, 
                                    AD_HOC_REQ_ID, $fields);
        if ($success)
        {
            $statusMessage .= "Record successfully updated.</br>";
        }
        else 
        {
            $statusMessage .= "Unexpected error encountered when updating database.".
                              "Contact your system administrator.</br>";
            $inputIsValid = false;
        }
    }
    
    GenerateStatus($inputIsValid, $statusMessage);
    return $success;
}

/* ----------------------------------------------------------------------------
 * Function DeleteAdHocAbsenceRequest
 *
 * This function constructs the SQL statement required to delete a row in 
 * the AdHocAbsenceRequest table.
 *
 * $ID(integer) ID of the record to be removed from the table. This should be set to 
 *              the AD_HOC_REQ_ID value of the record you wish to delete.
 *
 * @return (int) count of rows deleted. 0 means delete was unsuccessful. 
 * -------------------------------------------------------------------------- */

function DeleteAdHocAbsenceRequest($ID) {
    $sql = "DELETE FROM adHocAbsenceRequestTable WHERE adHocAbsenceRequestID=" . $ID . ";";
    GenerateStatus(true,"Record successfully deleted");
    return performSQL($sql);
}
?>