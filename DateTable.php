<?php

/* --------------------------------------------------------------------------------------
 * CONSTANTS
 *
 * These constants should be used when refering to the table and the fields within its
 * records.
 * ------------------------------------------------------------------------------------- */
define("DATE_TABLE", "dateTable");
define("DATE_TABLE_DATE_ID", "dateID");
define("DATE_TABLE_DATE", "date");
define("DATE_TABLE_PUBLIC_HOL_ID", "publicHolidayID");

/* --------------------------------------------------------------------------------------
 * Function CreateDateTable
 *
 * This function creates the SQL statement needed to construct the table
 * in the database.
 *
 * @return (bool)  True if table is created successfully, false otherwise.
 * ------------------------------------------------------------------------------------- */

function CreateDateTable() {
    $sql = "CREATE TABLE IF NOT EXISTS `mydb`.`DateTable` (
         `dateID` INT NULL AUTO_INCREMENT,
         `date` DATE NOT NULL,
         `publicHolidayID` INT NULL,
         PRIMARY KEY (`dateID`));";
    performSQL($sql);
}

/* --------------------------------------------------------------------------------------
 * Function CreateDate
 *
 * This function creates a new Date record in the table.
 *
 * $dateParam (string) Date. Should be in the form YYYY-MM-DD
 * $publicHolidayID (int) ID of the publicHoliday record associated with this record.
 *                        May be NULL if date is not a public holiday.
 *
 * @return (array) If successful, an array is returned where each key represents a field
 *                 in the record. If unsuccessful, the return will be NULL.
 * ------------------------------------------------------------------------------------- */

function CreateDate($dateParam, $publicHolidayID) {
    $date = NULL;
    //--------------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------------
    $inputIsValid = TRUE;

    if (!isValidDate($dateParam)) {
        error_log("Invalid date passed to CreateDate. value=" . $dateParam);
        $inputIsValid = FALSE;
    }

    if ($publicHolidayID <> NULL) {
        //ensure publicHolidayID exists in the database.
        $record = RetrievePublicHolidayByID($publicHolidayID);
        if ($record == NULL) {
            error_log("publicHolidayID passed to CreateDate does not exist in " .
                    "the database. ID=" . $publicHolidayID);
            $inputIsValid = FALSE;
        }
    }

    //--------------------------------------------------------------------------------
    // Only attempt to insert a record in the database if the input parameters are ok.
    //--------------------------------------------------------------------------------
    if ($inputIsValid) {
        // Create an array with each field required in the record. 
        $date[DATE_TABLE_DATE_ID] = NULL;
        $date[DATE_TABLE_DATE] = $dateParam;
        $date[DATE_TABLE_PUBLIC_HOL_ID] = $publicHolidayID;

        $success = sqlInsertDate($date);
        if (!$success) {
            error_log("Failed to create Date. " . print_r($date));
            $date = NULL;
        }
    }

    return $date;
}

/* --------------------------------------------------------------------------------------
 * Function sqlInsertDate 
 *
 * This function constructs the SQL statement required to insert a new record
 * into the Date table.
 *
 * &$date(array) Array containing all of the fields required for the record.
 *
 * @return (bool) TRUE if insert into database was successful, false otherwise.
 * 		   
 * Note: If successful then the DATE_TABLE_DATE_ID entry in the 
 * array passed by the caller will be set to the ID of the record in the database. 
 * ------------------------------------------------------------------------------------- */

function sqlInsertDate(&$date) {
    $sql = "INSERT INTO DateTable (date,publicHolidayID) " .
            "VALUES ('" . $date[DATE_TABLE_DATE] . "',";


    if ($date[DATE_TABLE_PUBLIC_HOL_ID] <> NULL) {
        $sql = $sql . "'" . $date[DATE_TABLE_PUBLIC_HOL_ID] . "');";
    } else {
        $sql = $sql . "NULL);";
    }

    $date[DATE_TABLE_DATE_ID] = performSQLInsert($sql);

    return $date[DATE_TABLE_DATE_ID] <> 0;
}

/* --------------------------------------------------------------------------------------
 * Function RetrieveDateByID
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

function RetrieveDateByID($id) {
    $filter[DATE_TABLE_DATE_ID] = $id;
    $resultArray = performSQLSelect(DATE_TABLE, $filter);
    $result = NULL;

    if (count($resultArray) == 1) {      //Check to see if record was found.
        $result = $resultArray[0];
    }

    return $result;
}

/* --------------------------------------------------------------------------------------
 * Function RetrievDates
 *
 * This function constructs the SQL statement required to query the Dates table.
 *
 * $filter (array) Optional parameter. If supplied, then the array should contain a set
 *                 of key value pairs, where the keys correspond to one (or more) fields
 *                 in the record (see constants at top of file) and the values correspond
 *                 to the values to filter against (IE: The WHERE clause).
 *
 * @return (array) If successful, an array of arrays, where each element corresponds to 
 *                 a row from the query. If a failure occurs, return will be NULL. 
 * ------------------------------------------------------------------------------------- */

function RetrieveDates($filter = NULL) {
    $inputIsValid = TRUE;
    //--------------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------------
    if ($filter <> NULL) {
        foreach ($filter as $key => $value) {
            if (strcmp($key, DATE_TABLE_DATE_ID) == 0) {
                if (!is_numeric($value)) {
                    printCallStackAndDie();
                    error_log("Invalid DATE_TABLE_DATE_ID of " . $value .
                            " passed to RetrieveDates.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, DATE_TABLE_DATE) == 0) {
                if (!isValidDate($value)) {
                    error_log("Invalid DATE_TABLE_DATE of " . $value .
                            " passed to RetrieveDates.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, DATE_TABLE_PUBLIC_HOL_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid DATE_TABLE_PUBLIC_HOL_ID of " . $value .
                            " passed to RetrieveDates.");
                    $inputIsValid = FALSE;
                }
            } else {
                error_log("Unknown Filter " . $key . " passed to RetrieveDates.");
                $inputIsValid = FALSE;
            }
        }
    }

    //--------------------------------------------------------------------------------
    // Only attempt to perform query in the database if the input parameters are ok.
    //--------------------------------------------------------------------------------
    $result = NULL;
    if ($inputIsValid) {
        $result = performSQLSelect(DATE_TABLE, $filter);
    }
    return $result;
}

/* --------------------------------------------------------------------------------------
 * Function UpdateDate
 *
 * This function constructs the SQL statement required to update a row in 
 * the Date table.
 *
 * $fields (array) array of key value pairs, where keys correspond to fields in the
 *                 record (see constants at start of this file). Note, this array
 *                 MUST provide the id of the record (DATE_TABLE_DATE_ID) and one or more 
 *                 other fields to be updated. 
 *
 * @return (bool) TRUE if update succeeds. FALSE otherwise. 
 * ------------------------------------------------------------------------------------- */

function UpdateDate($fields) {
    //--------------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------------
    $inputIsValid = TRUE;
    $validID = false;
    $countOfFields = 0;

    foreach ($fields as $key => $value) {
        if ($key == DATE_TABLE_DATE_ID) {
            $record = RetrieveDateByID($value);
            if ($record <> NULL) {
                $validID = true;
                $countOfFields++;
            }
        } else if ($key == DATE_TABLE_DATE) {
            $countOfFields++;

            if (!isValidDate($value)) {
                error_log("Invalid DATE_TABLE_DATE passed to UpdateDate.");
                $inputIsValid = FALSE;
            }
        } else if ($key == DATE_TABLE_PUBLIC_HOL_ID) {
            $countOfFields++;

            if ($value <> NULL)
            {
                $record = RetrievePublicHolidayByID($value);
                if ($record == NULL) {
                    error_log("Invalid DATE_TABLE_PUBLIC_HOL_ID passed to UpdateDate.");
                    $inputIsValid = FALSE;
                }
            }
        } else {
            error_log("Invalid field passed to UpdateDate. $key=" . $key);
            $inputIsValid = FALSE;
        }
    }

    if (!$validID) {
        error_log("No valid ID supplied in call to UpdateDate.");
        $inputIsValid = FALSE;
    }

    if ($countOfFields < 2) {
        error_log("Insufficent fields supplied in call to UpdateDate.");
        $inputIsValid = FALSE;
    }

    //--------------------------------------------------------------------------------
    // Only attempt to update a record in the database if the input parameters are ok.
    //--------------------------------------------------------------------------------
    $success = false;

    if ($inputIsValid) {
        $success = performSQLUpdate(DATE_TABLE, DATE_TABLE_DATE_ID, $fields);
    }
    return $success;
}

/* --------------------------------------------------------------------------------------
 * Function DeleteDate
 *
 * This function constructs the SQL statement required to delete a row in 
 * the Date table.
 *
 * $ID(integer) ID of the record to be removed from the table. This should be set to 
 *              the DATE_TABLE_DATE_ID value of the record you wish to delete.
 *
 * @return (int) count of rows deleted. 0 means delete was unsuccessful. 
 * ------------------------------------------------------------------------------------- */

function DeleteDate($ID) {
    $result = 0;

    $date = RetrieveDateByID($ID);
    if ($date <> NULL) {
        $filter[APPR_ABS_BOOK_DATE_DATE_ID] = $date[DATE_TABLE_DATE_ID];
        $approvedAbsenceBookingDates = RetrieveApprovedAbsenceBookingDates($filter);

        if ($approvedAbsenceBookingDates <> NULL) {
            foreach ($approvedAbsenceBookingDates as $value) {
                DeleteApprovedAbsenceBooking($value[APPR_ABS_BOOK_DATE_ABS_BOOK_ID]);
            }
        }

        if ($date[DATE_TABLE_PUBLIC_HOL_ID] <> NULL) 
        {
            DeletePublicHoliday($date[DATE_TABLE_PUBLIC_HOL_ID]);
        }
  
        $sql = "DELETE FROM dateTable WHERE dateID=" . $ID . ";";
        $result = performSQL($sql);
    }
    return $result;
}

/* --------------------------------------------------------------------------------------
 * Function RetrieveDateRecordByDate
 *
 * This function uses the date supplied to locate the corresponding date record from 
 * the database.
 *
 * $date(string) string in the form YYYY-MM-DD for which the matching date table record
 *               is needed.
 *
 * @return (array) array of key value pairs, representing the fields from the date record.
 *                 Will be NULL if no matching record is found. 
 * ------------------------------------------------------------------------------------- */
function RetrieveDateRecordByDate($date)
{
	$result = NULL;
	
	$filter[DATE_TABLE_DATE] = $date;
	$records = RetrieveDates($filter);
	
	if (count($records) == 1)
	{
		$result = $records[0];
	}
	
	return $result;
}



/* --------------------------------------------------------------------------------------
 * Function RetrievDateIDFromDate
 *
 * This function takes a string representing a date and returns the id of the date
 * record in the database which matches this date.
 * *
 * $date (string) Date in the form YYYY-MM-DD.
 * @return (int) If successful, this number is the key of the record in the databse
 *               which is for the date supplied. If a failure occurs, will be NULL. 
 * ------------------------------------------------------------------------------------- */

function RetrieveDateIDByDate($date) 
{
    $result = NULL;
    
    $record = RetrieveDateRecordByDate($date);
    
    if ($record)
    {
        $result = $record[DATE_TABLE_DATE_ID];
    }

    return $result;
}
?>
