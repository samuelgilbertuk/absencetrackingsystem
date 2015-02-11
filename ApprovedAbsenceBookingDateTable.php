<?php

/* --------------------------------------------------------------------------------------
 * CONSTANTS
 *
 * These constants should be used when refering to the table and the fields within its
 * records.
 * ------------------------------------------------------------------------------------- */
define("APPROVED_ABSENCE_BOOKING_DATE", "approvedAbsenceBookingDate");
define("APPR_ABS_BOOK_DATE_ID", "approvedAbsenceBookingDateID");
define("APPR_ABS_BOOK_DATE_DATE_ID", "dateID");
define("APPR_ABS_BOOK_DATE_ABS_BOOK_ID", "approvedAbsenceBookingID");

/* --------------------------------------------------------------------------------------
 * Function CreateApprovedAbsenceDateTable
 *
 * This function creates the SQL statement needed to construct the table
 * in the database.
 *
 * @return (bool)  True if table is created successfully, false otherwise.
 * ------------------------------------------------------------------------------------- */

function CreateApprovedAbsenceDateTable() {
    $sql = "CREATE TABLE IF NOT EXISTS `mydb`.`approvedAbsenceBookingDate` (
  `approvedAbsenceBookingDateID` INT NOT NULL AUTO_INCREMENT,
  `dateID` INT NOT NULL,
  `approvedAbsenceBookingID` INT NOT NULL,
  PRIMARY KEY (`approvedAbsenceBookingDateID`),
  INDEX `fk_approvedAbsenceBookingDate_Date1_idx` (`dateID` ASC),
  INDEX `fk_approvedAbsenceBookingDate_approvedAbsenceBooking1_idx` 
  (`approvedAbsenceBookingID` ASC),
  CONSTRAINT `fk_approvedAbsenceBookingDate_Date1`
    FOREIGN KEY (`dateID`)
    REFERENCES `mydb`.`DateTable` (`dateID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_approvedAbsenceBookingDate_approvedAbsenceBooking1`
    FOREIGN KEY (`approvedAbsenceBookingID`)
    REFERENCES `mydb`.`approvedAbsenceBookingTable` (`approvedAbsenceBookingID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);";

    performSQL($sql);
}

/* --------------------------------------------------------------------------------------
 * Function CreateApprovedAbsenceBookingDate
 *
 * This function creates a new ApprovedAbsenceBookingDate record in the 
 * table.
 *
 * $dateID (int) ID of the date record associated with this record.
 * $approvedAbsenceBookingID (int) ID of the approvedAbsenceBooking assocaited with 
 * this record.
 *
 * @return (array) If successful, an array is returned where each key represents a field
 *                 in the record. If unsuccessful, the return will be NULL.
 * ------------------------------------------------------------------------------------- */

function CreateApprovedAbsenceBookingDate($dateID, $approvedAbsenceBookingID) {
    $absenceRequest = NULL;
    //--------------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------------
    $inputIsValid = TRUE;

    // Check that a record with the DateID supplied exists in the database.
    $record = RetrieveDateByID($dateID);

    if ($record == NULL) {
        error_log("dateID passed to CreateApprovedAbsenceBookingDate " .
                " does not exist in the database. ID=" . $dateID);
        $inputIsValid = FALSE;
    }

    // Check that a record with the approvedAbsenceBookingID supplied exists in the database.
    $record = RetrieveApprovedAbsenceBookingByID($approvedAbsenceBookingID);
    if ($record == NULL) {
        error_log("approvedAbsenceBookingID passed to CreateApprovedAbsenceBookingDate " .
                " does not exist in the database. ID=" . $approvedAbsenceBookingID);
        $inputIsValid = FALSE;
    }

    //--------------------------------------------------------------------------------
    // Only attempt to insert a record in the database if the input parameters are ok.
    //--------------------------------------------------------------------------------
    $bookingDate = NULL;

    if ($inputIsValid) {
        $bookingDate[APPR_ABS_BOOK_DATE_ID] = NULL;
        $bookingDate[APPR_ABS_BOOK_DATE_DATE_ID] = $dateID;
        $bookingDate[APPR_ABS_BOOK_DATE_ABS_BOOK_ID] = $approvedAbsenceBookingID;

        $success = sqlInsertApprovedAbsenceBookingDate($bookingDate);
        if (!$success) {
            error_log("Failed to create Approved Absence Booking Date.");
            $bookingDate = NULL;
        }
    }
    return $bookingDate;
}

/* --------------------------------------------------------------------------------------
 * Function sqlInsertApprovedAbsenceBookingDate 
 *
 * This function constructs the SQL statement required to insert a new record
 * into the approvedAbsenceBookingDate table.
 *
 * &$approvedAbsenceBookingDate (array) Array containing all of the fields required for 
 * the record.
 *
 * @return (bool) TRUE if insert into database was successful, false otherwise.
 * 		   
 * Note: If successful then the APPR_ABS_BOOK_DATE_ID entry in the 
 * array passed by the caller will be set to the ID of the record in the database. 
 * ------------------------------------------------------------------------------------- */

function sqlInsertApprovedAbsenceBookingDate(&$absenceBookingDate) {
    $sql = "INSERT INTO approvedAbsenceBookingDate " .
            "(dateID,approvedAbsenceBookingID) " .
            "VALUES ('" .
            $absenceBookingDate[APPR_ABS_BOOK_DATE_DATE_ID] . "','" .
            $absenceBookingDate[APPR_ABS_BOOK_DATE_ABS_BOOK_ID] . "');";

    $absenceBookingDate[APPR_ABS_BOOK_DATE_ID] = performSQLInsert($sql);
    return $absenceBookingDate[APPR_ABS_BOOK_DATE_ID] <> 0;
}

/* --------------------------------------------------------------------------------------
 * Function RetrieveApprovedAbsenceBookingDateByID
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

function RetrieveApprovedAbsenceBookingDateByID($id) {
    $filter[APPR_ABS_BOOK_DATE_ID] = $id;
    $resultArray = performSQLSelect(APPROVED_ABSENCE_BOOKING_DATE, $filter);

    $result = NULL;
    
//Check to see if record was found.
    if (count($resultArray) == 1) {      
        //Yes, record was found.
        $result = $resultArray[0];
    }

    return $result;
}

/* --------------------------------------------------------------------------------------
 * Function RetrieveApprovedAbsenceBookingDates
 *
 * This function constructs the SQL statement required to query the 
 * ApprovedAbsenceBookingDate table.
 *
 * $filter (array) Optional parameter. If supplied, then the array should contain a set
 *                 of key value pairs, where the keys correspond to one (or more) fields
 *                 in the record (see constants at top of file) and the values correspond
 *                 to the values to filter against (IE: The WHERE clause).
 *
 * @return (array) If successful, an array of arrays, where each element corresponds to 
 *                 a row from the query. If a failure occurs, return will be NULL. 
 * ------------------------------------------------------------------------------------- */

function RetrieveApprovedAbsenceBookingDates($filter = NULL) {
    $inputIsValid = TRUE;

    //--------------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------------
    if ($filter <> NULL) {
        foreach ($filter as $key => $value) {
            if (strcmp($key, APPR_ABS_BOOK_DATE_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid approvedAbsenceBookingDateID of " . $value .
                            " passed to RetrieveApprovedAbsenceBookingDates.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, APPR_ABS_BOOK_DATE_DATE_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid dateID of " . $value .
                            " passed to RetrieveApprovedAbsenceBookingDates.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, APPR_ABS_BOOK_DATE_ABS_BOOK_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid approvedAbsenceBookingID of " . $value .
                            " passed to RetrieveApprovedAbsenceBookingDates.");
                    $inputIsValid = FALSE;
                }
            } else {
                error_log("Unknown Filter " . $key . " passed to " .
                        "RetrieveApprovedAbsenceBookingDates.");
                $inputIsValid = FALSE;
            }
        }
    }

    //--------------------------------------------------------------------------------
    // Only attempt to perform query in the database if the input parameters are ok.
    //--------------------------------------------------------------------------------
    $result = NULL;
    if ($inputIsValid) {
        $result = performSQLSelect(APPROVED_ABSENCE_BOOKING_DATE, $filter);
    }

    return $result;
}

/* --------------------------------------------------------------------------------------
 * Function UpdateApprovedAbsenceBookingDate
 *
 * This function constructs the SQL statement required to update a row in 
 * the ApprovedAbsenceBookingDate table.
 *
 * $fields (array) array of key value pairs, where keys correspond to fields in the
 *                 record (see constants at start of this file). Note, this array
 *                 MUST provide the id of the record (AAPPR_ABS_BOOK_DATE_ID) and one or 
 *                 more other fields to be updated. 
 *
 * @return (bool) TRUE if update succeeds. FALSE otherwise. 
 * ------------------------------------------------------------------------------------- */

function UpdateApprovedAbsenceBookingDate($fields) {
    //--------------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------------
    $inputIsValid = TRUE;
    $validID = false;
    $countOfFields = 0;

    foreach ($fields as $key => $value) {
        if ($key == APPR_ABS_BOOK_DATE_ID) {
            $record = RetrieveApprovedAbsenceBookingDateByID($value);
            if ($record <> NULL) {
                $validID = true;
                $countOfFields++;
            }
        } else if ($key == APPR_ABS_BOOK_DATE_DATE_ID) {
            $countOfFields++;

            $record = RetrieveDateByID($value);
            if ($record == NULL) {
                error_log("Invalid APPR_ABS_BOOK_DATE_DATE_ID passed to " .
                        "UpdateApprovedAbsenceBookingDate. Value=" . $value);
                $inputIsValid = FALSE;
            }
        } else if ($key == APPR_ABS_BOOK_DATE_ABS_BOOK_ID) {
            $countOfFields++;

            $record = RetrieveApprovedAbsenceBookingByID($value);
            if ($record == NULL) {
                error_log("Invalid APPR_ABS_BOOKING_ID passed to " .
                        "UpdateApprovedAbsenceBookingDate. Value=" . $value);
                $inputIsValid = FALSE;
            }
        } else {
            error_log("Invalid field passed to UpdateApprovedAbsenceBookingDate." .
                    " $key=" . $key);
            $inputIsValid = FALSE;
        }
    }

    if (!$validID) {
        error_log("No valid ID supplied in call to UpdateApprovedAbsenceBookingDate.");
        $inputIsValid = FALSE;
    }

    if ($countOfFields < 2) {
        error_log("Insufficent fields supplied in call to UpdateApprovedAbsenceBookingDate.");
        $inputIsValid = FALSE;
    }

    //--------------------------------------------------------------------------------
    // Only attempt to update a record in the database if the input parameters are ok.
    //--------------------------------------------------------------------------------
    $success = false;

    if ($inputIsValid) {
        $success = performSQLUpdate(APPROVED_ABSENCE_BOOKING_DATE, APPR_ABS_BOOK_DATE_ID, $fields);
    }
    return $success;
}

/* --------------------------------------------------------------------------------------
 * Function DeleteApprovedAbsenceBookingDate
 *
 * This function constructs the SQL statement required to delete a row in 
 * the ApprovedAbsenceBookingDate table.
 *
 * $ID(integer) ID of the record to be removed from the table. This should be set to 
 *              the   APPR_ABS_BOOK_DATE_ID value of the record you wish to delete.
 *
 * @return (int) count of rows deleted. 0 means delete was unsuccessful. 
 * ------------------------------------------------------------------------------------- */

function DeleteApprovedAbsenceBookingDate($ID) {
    $result = 0;
    $record = RetrieveApprovedAbsenceBookingDateByID($ID);

    if ($record <> NULL) {
        $sql = "DELETE FROM approvedAbsenceBookingDate WHERE approvedAbsenceBookingDateID=" . $ID . ";";
        $result = performSQL($sql);
    }
    return $result;
}

?>
