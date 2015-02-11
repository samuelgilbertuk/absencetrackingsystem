<?php

/* --------------------------------------------------------------------------------------
 * CONSTANTS
 *
 * These constants should be used when refering to the table and the fields within its
 * records.
 * ------------------------------------------------------------------------------------- */
define("APPROVED_ABSENCE_BOOKING_TABLE", "approvedAbsenceBookingTable");
define("APPR_ABS_BOOKING_ID", "approvedAbsenceBookingID");
define("APPR_ABS_EMPLOYEE_ID", "employeeID");
define("APPR_ABS_START_DATE", "absenceStartDate");
define("APPR_ABS_END_DATE", "approvedEndDate");
define("APPR_ABS_ABS_TYPE_ID", "absenceTypeID");


/* --------------------------------------------------------------------------------------
 * Function CreateApprovedAbsenceBookingTable
 *
 * This function creates the SQL statement needed to construct the table
 * in the database.
 *
 * @return (bool)  True if table is created successfully, false otherwise.
 * ------------------------------------------------------------------------------------- */

function CreateApprovedAbsenceBookingTable() {
    $sql = "CREATE TABLE IF NOT EXISTS `mydb`.`approvedAbsenceBookingTable` (
  `approvedAbsenceBookingID` INT NOT NULL AUTO_INCREMENT,
  `employeeID` INT NOT NULL,
  `absenceStartDate` DATE NOT NULL,
  `approvedEndDate` DATE NOT NULL,
  `absenceTypeID` INT NOT NULL,
  PRIMARY KEY (`approvedAbsenceBookingID`),
  INDEX `fk_approvedAbsenceBooking_absenceType1_idx` (`absenceTypeID` ASC),
  INDEX `fk_approvedAbsenceBooking_Employee1_idx` (`employeeID` ASC),
  CONSTRAINT `fk_approvedAbsenceBooking_absenceType1`
    FOREIGN KEY (`absenceTypeID`)
    REFERENCES `mydb`.`absenceTypeTable` (`absenceTypeID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_approvedAbsenceBooking_Employee1`
    FOREIGN KEY (`employeeID`)
    REFERENCES `mydb`.`EmployeeTable` (`employeeID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);";

    performSQL($sql);
}

/* --------------------------------------------------------------------------------------
 * Function CreateApprovedAbsenceBooking
 *
 * This function creates a new ApprovedAbsenceBooking record in the 
 * table.
 *
 * $employeeID (int) ID of the employee record associated with this record.
 * $absenceStartDate (string) Start date of the request. Should be in the form YYYY-MM-DD
 * $absenceEndDate (string)  End date of the request. Should be in the form YYYY-MM-DD
 * $absenceTypeID (int) ID of the absenceType record associated with this record.
 *
 * @return (array) If successful, an array is returned where each key represents a field
 *                 in the record. If unsuccessful, the return will be NULL.
 * ------------------------------------------------------------------------------------- */

function CreateApprovedAbsenceBooking($employeeID, $absenceStartDate, $absenceEndDate, $absenceTypeID) {
    $statusMessage = "";
    $absenceBooking = NULL;
    //--------------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------------
    $inputIsValid = TRUE;

    //ensure employee exists in the database.
    $record = RetrieveEmployeeByID($employeeID);
    if ($record == NULL) {
        $statusMessage.= "Unable to locate employee in database.</br>";
        error_log("employeeID passed to CreateApprovedAbsenceBooking " .
                " does not exist in the database. ID=" . $employeeID);
        $inputIsValid = FALSE;
    }

    if (!isValidDate($absenceStartDate)) {
        $statusMessage.= "Start date is not a valid date.</br>";
        error_log("absenceStartDate passed to CreateApprovedAbsenceBooking " .
                " is invalid. date=" . $absenceStartDate);
        $inputIsValid = FALSE;
    }

    if (!isValidDate($absenceEndDate)) {
        $statusMessage.= "End date is not a valid date.</br>";
        error_log("absenceEndDate passed to CreateApprovedAbsenceBooking " .
                " is invalid. date=" . $absenceEndDate);
        $inputIsValid = FALSE;
    }
    
    if (strtotime($absenceEndDate) < strtotime($absenceStartDate)) 
    {
        $statusMessage.="end Date is before start Date.</br>";
        error_log("End Date is before Start Date.");
        $inputIsValid = FALSE;
    }


    //ensure absence type exists in the database.
    $record = RetrieveAbsenceTypeByID($absenceTypeID);

    if ($record == NULL) {
        $statusMessage.= "Unable to locate absence type in database.</br>";
        error_log("absenceTypeID passed to CreateApprovedAbsenceBooking " .
                " does not exist in the database. ID=" . $absenceTypeID);
        $inputIsValid = FALSE;
    }

    //--------------------------------------------------------------------------------
    // Only attempt to insert a record in the database if the input parameters are ok.
    //--------------------------------------------------------------------------------
    $booking = NULL;

    if ($inputIsValid) {
        $booking[APPR_ABS_BOOKING_ID] = NULL;
        $booking[APPR_ABS_EMPLOYEE_ID] = $employeeID;
        $booking[APPR_ABS_START_DATE] = $absenceStartDate;
        $booking[APPR_ABS_END_DATE] = $absenceEndDate;
        $booking[APPR_ABS_ABS_TYPE_ID] = $absenceTypeID;

        $success = sqlInsertApprovedAbsenceBooking($booking);
        if (!$success) {
            $statusMessage.= "Unexpected error when inserting to database.".
                             "Contact your system administrator.</br>";
            $inputIsValid = false;
            error_log("Failed to create Approved Absence Booking.");
            $booking = NULL;
        }
   
        // Set timezone
        date_default_timezone_set('UTC');
 
        // Start date
        $date = $absenceStartDate;
        // End date
 
        while (strtotime($date) <= strtotime($absenceEndDate)) {
            $dateID = RetrieveDateIDByDate($date);
            if ($dateID <> NULL)
            {
                $result = CreateApprovedAbsenceBookingDate($dateID,
                                            $booking[APPR_ABS_BOOKING_ID]);
            }
            else 
            {
                $statusMessage.="Unable to locate Date of $date in database.</br>";
                $inputIsValid = false;
                error_log("Unable to find date record. Date=".$date);
            }
            $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
        if ($inputIsValid)
        {
            $statusMessage.= "Record created successfully.</br>";
        }
    }
    
    GenerateStatus($inputIsValid, $statusMessage);
    return $booking;
}

/* --------------------------------------------------------------------------------------
 * Function sqlInsertApprovedAbsenceBooking 
 *
 * This function constructs the SQL statement required to insert a new record
 * into the approvedAbsenceBooking table.
 *
 * &$approvedAbsenceBooking (array) Array containing all of the fields required for 
 * the record.
 *
 * @return (bool) TRUE if insert into database was successful, false otherwise.
 * 		   
 * Note: If successful then the APPR_ABS_BOOKING_ID entry in the 
 * array passed by the caller will be set to the ID of the record in the database. 
 * ------------------------------------------------------------------------------------- */

function sqlInsertApprovedAbsenceBooking(&$absenceBooking) {
    $sql = "INSERT INTO approvedAbsenceBookingTable " .
            "(employeeID,absenceStartDate,approvedEndDate,absenceTypeID) " .
            "VALUES ('" .
            $absenceBooking[APPR_ABS_EMPLOYEE_ID] . "','" .
            $absenceBooking[APPR_ABS_START_DATE] . "','" .
            $absenceBooking[APPR_ABS_END_DATE] . "','" .
            $absenceBooking[APPR_ABS_ABS_TYPE_ID] . "');";

    $absenceBooking[APPR_ABS_BOOKING_ID] = performSQLInsert($sql);
    return $absenceBooking[APPR_ABS_BOOKING_ID] <> 0;
}

/* --------------------------------------------------------------------------------------
 * Function RetrieveApprovedAbsenceBookingByID
 *
 * This function uses the ID supplied as a parameter to construct an SQL select statement
 * and then performs this query, returning an array containing the key value pairs of the
 * record (or NULL if no record is found matching the id).
 *
 * $id (int) id of the record to retrieve from the database..
 *
 * @return (array) array of key value pairs representing the fields in the record, or 
 *                 NULL if no record exists with the id supplied.
 * ------------------------------------------------------------------------------------- */

function RetrieveApprovedAbsenceBookingByID($id) {
    $filter[APPR_ABS_BOOKING_ID] = $id;
    $resultArray = performSQLSelect(APPROVED_ABSENCE_BOOKING_TABLE, $filter);

    $result = NULL;

    if (count($resultArray) == 1) {      //Check to see if record was found.
        $result = $resultArray[0];
    }

    return $result;
}

/* --------------------------------------------------------------------------------------
 * Function RetrieveApprovedAbsenceBookings
 *
 * This function constructs the SQL statement required to query the 
 * ApprovedAbsenceBookings table.
 *
 * $filter (array) Optional parameter. If supplied, then the array should contain a set
 *                 of key value pairs, where the keys correspond to one (or more) fields
 *                 in the record (see constants at top of file) and the values correspond
 *                 to the values to filter against (IE: The WHERE clause).
 *
 * @return (array) If successful, an array of arrays, where each element corresponds to 
 *                 a row from the query. If a failure occurs, return will be NULL. 
 * ------------------------------------------------------------------------------------- */

function RetrieveApprovedAbsenceBookings($filter = NULL) {
    $inputIsValid = TRUE;

    //--------------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------------
    if ($filter <> NULL) {
        foreach ($filter as $key => $value) {
            if (strcmp($key, APPR_ABS_BOOKING_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid APPR_ABS_BOOKING_ID of " . $value .
                            " passed to RetrieveApprovedAbsenceBookings.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, APPR_ABS_EMPLOYEE_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid APPR_ABS_EMPLOYEE_ID of " . $value .
                            " passed to RetrieveApprovedAbsenceBookings.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, APPR_ABS_START_DATE) == 0) {
                if (!isValidDate($value)) {
                    error_log("Invalid APPR_ABS_START_DATE of " . $value .
                            " passed to RetrieveApprovedAbsenceBookings.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, APPR_ABS_END_DATE) == 0) {
                if (!isValidDate($value)) {
                    error_log("Invalid APPR_ABS_END_DATE of " . $value .
                            " passed to RetrieveApprovedAbsenceBookings.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, APPR_ABS_ABS_TYPE_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid APPR_ABS_ABS_TYPE_ID of " . $value .
                            " passed to RetrieveApprovedAbsenceBookings.");
                    $inputIsValid = FALSE;
                }
            } else {
                error_log("Unknown Filter " . $key . " passed to " .
                        "RetrieveApprovedAbsenceBookings.");
                $inputIsValid = FALSE;
            }
        }
    }

    //--------------------------------------------------------------------------------
    // Only attempt to perform query in the database if the input parameters are ok.
    //--------------------------------------------------------------------------------
    $result = NULL;
    if ($inputIsValid) {
        $result = performSQLSelect(APPROVED_ABSENCE_BOOKING_TABLE, $filter);
    }
    return $result;
}

/* --------------------------------------------------------------------------------------
 * Function UpdateApprovedAbsenceBooking
 *
 * This function constructs the SQL statement required to update a row in 
 * the ApprovedAbsenceBooking table.
 *
 * $fields (array) array of key value pairs, where keys correspond to fields in the
 *                 record (see constants at start of this file). Note, this array
 *                 MUST provide the id of the record (APPR_ABS_BOOKING_ID) and one or more
 * 				   other fields to be updated. 
 *
 * @return (bool) TRUE if update succeeds. FALSE otherwise. 
 * ------------------------------------------------------------------------------------- */

function UpdateApprovedAbsenceBooking($fields) {
    $statusMessage = "";
    //--------------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------------
    $inputIsValid = TRUE;
    $validID = false;
    $countOfFields = 0;

    foreach ($fields as $key => $value) {
        if ($key == APPR_ABS_BOOKING_ID) {
            $record = RetrieveApprovedAbsenceBookingByID($value);
            if ($record <> NULL) {
                $validID = true;
                $countOfFields++;
            }
        } else if ($key == APPR_ABS_EMPLOYEE_ID) {
            $countOfFields++;

            $record = RetrieveEmployeeByID($value);
            if ($record == NULL) {
                $statusMessage.="Unable to locate employee in database</br>";
                error_log("Invalid EMP_ID passed to " .
                        "UpdateApprovedAbsenceBooking. Value=" . $value);
                $inputIsValid = FALSE;
            }
        } else if ($key == APPR_ABS_START_DATE) {
            $countOfFields++;

            if (!isValidDate($value)) {
                $statusMessage.="Start date is not a valid date.</br>";
                error_log("Invalid APPR_ABS_START_DATE passed to " .
                        "UpdateApprovedAbsenceBooking. Value=" . $value);
                $inputIsValid = FALSE;
            }
        } else if ($key == APPR_ABS_END_DATE) {
            $countOfFields++;

            if (!isValidDate($value)) {
                $statusMessage.="End date is not a valid date.</br>";
                error_log("Invalid APPR_ABS_END_DATE passed to " .
                        "UpdateApprovedAbsenceBooking. Value=" . $value);
                $inputIsValid = FALSE;
            }
        } else if ($key == APPR_ABS_ABS_TYPE_ID) {
            $countOfFields++;

            $record = RetrieveAbsenceTypeByID($value);
            if ($record == NULL) {
                $statusMessage.="Unable to locate absence type in database</br>";
                error_log("Invalid APPR_ABS_ABS_TYPE_ID passed to " .
                        "UpdateApprovedAbsenceBooking. Value=" . $value);
                $inputIsValid = FALSE;
            }
        } else {
            $statusMessage.="Unexpected field found in input</br>";
            error_log("Invalid field passed to UpdateApprovedAbsenceBooking." .
                    " $key=" . $key);
            $inputIsValid = FALSE;
        }
    }
    
    $absenceStartDate = $fields[APPR_ABS_START_DATE];
    $absenceEndDate = $fields[APPR_ABS_END_DATE];
    
    if (strtotime($absenceEndDate) < strtotime($absenceStartDate)) 
    {
        $statusMessage.="end Date is before start Date.</br>";
        error_log("End Date is before Start Date.");
        $inputIsValid = FALSE;
    }


    if (!$validID) {
        $statusMessage.="No valid ID supplied</br>";
        error_log("No valid ID supplied in call to UpdateApprovedAbsenceBooking.");
        $inputIsValid = FALSE;
    }

    if ($countOfFields < 2) {
        $statusMessage.="Insufficent fields supplied</br>";
        error_log("Insufficent fields supplied in call to UpdateApprovedAbsenceBooking.");
        $inputIsValid = FALSE;
    }

    //--------------------------------------------------------------------------------
    // Only attempt to update a record in the database if the input parameters are ok.
    //--------------------------------------------------------------------------------
    $success = false;

    if ($inputIsValid) {
        $success = performSQLUpdate(APPROVED_ABSENCE_BOOKING_TABLE, APPR_ABS_BOOKING_ID, $fields);
        if ($success)
        {
            $statusMessage.="Record updated successfully.</br>";
        }
        else 
        {
            $statusMessage.="Unexpected error encountered when updating database.</br>";
            $inputIsValid = false;
        }
    }
    
    GenerateStatus($inputIsValid, $statusMessage);
    return $success;
}

/* --------------------------------------------------------------------------------------
 * Function DeleteApprovedAbsenceBooking
 *
 * This function constructs the SQL statement required to delete a row in 
 * the ApprovedAbsenceBooking table.
 *
 * $ID(integer) ID of the record to be removed from the table. This should be set to 
 *              the APPR_ABS_BOOKING_ID value of the record you wish to delete.
 *
 * @return (int) count of rows deleted. 0 means delete was unsuccessful. 
 * ------------------------------------------------------------------------------------- */

function DeleteApprovedAbsenceBooking($ID) {
    $result = 0;

    $approvedAbsenceBooking = RetrieveApprovedAbsenceBookingByID($ID);
    if ($approvedAbsenceBooking <> NULL) {
        $sql = "DELETE FROM approvedAbsenceBookingTable WHERE approvedAbsenceBookingID=" . $ID . ";";
        $result = performSQL($sql);


        $filter[APPR_ABS_BOOK_DATE_ABS_BOOK_ID] = $ID;
        $approvedAbsenceBookingDates = RetrieveApprovedAbsenceBookingDates($filter);

        foreach ((array) $approvedAbsenceBookingDates as $approvedAbsenceBookingDate) {
            DeleteApprovedAbsenceBookingDate($approvedAbsenceBookingDate[APPR_ABS_BOOK_DATE_ID]);
        }
    }
    GenerateStatus(true,"Record successfully deleted.");
    return $result;
}

?>
