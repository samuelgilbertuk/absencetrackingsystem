<?php

/* -----------------------------------------------------------------------------
 * CONSTANTS
 *
 * These constants should be used when refering to the table and the fields 
 * within its records.
 * -------------------------------------------------------------------------- */
define("PUBLIC_HOLIDAY_TABLE", "publicHolidayTable");
define("PUB_HOL_ID", "publicHolidayID");
define("PUB_HOL_NAME", "nameOfPublicHoliday");
define("PUB_HOL_DATE_ID", "dateID");

/* ----------------------------------------------------------------------------
 * Function CreatePublicHolidayTable
 *
 * This function creates the SQL statement needed to construct the PublicHoliday 
 * table in the database.
 *
 * @return (bool)  True if table is created successfully, false otherwise.
 * ---------------------------------------------------------------------------*/

function CreatePublicHolidayTable() {
    $sql = "CREATE TABLE IF NOT EXISTS `mydb`.`publicHolidayTable` (
        `publicHolidayID` INT NOT NULL AUTO_INCREMENT,
        `nameOfPublicHoliday` VARCHAR(40) NOT NULL,
        `dateID` INT NOT NULL,
        PRIMARY KEY (`publicHolidayID`),
        INDEX `fk_publicHoliday_Date_idx` (`dateID` ASC),
        CONSTRAINT `fk_publicHoliday_Date`
        FOREIGN KEY (`dateID`)
        REFERENCES `mydb`.`DateTable` (`dateID`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION);";
    performSQL($sql);
}

/* ----------------------------------------------------------------------------
 * Function CreatePublicHoliday
 *
 * This function creates a new Absence Type row in the AbsenceTypeTable.
 *
 * $absenceTypeName (string) Textual name of the type of absence.
 * $usesAnnual Leave (boolean) Whether or not this type of absence uses annual 
 *                             leave.
 * $canBeDenied (boolean) Whether or not this type of absence can be denied.
 *
 * @return (array) If successful, an array is returned where each key represents
 *                 a field in the record. 
 *                 If unsuccessful, the return will be NULL.
 * ---------------------------------------------------------------------------*/

function CreatePublicHoliday($nameOfPublicHoliday, $dateID) {
    $statusMessage = "";
    
    $publicHoliday = NULL;
    //-------------------------------------------------------------------------
    // Validate Input parameters
    //-------------------------------------------------------------------------
    $inputIsValid = TRUE;
    if (isNullOrEmptyString($nameOfPublicHoliday)) {
        $statusMessage.="Name of public holiday can not be blank</br>";
        error_log("Invalid name passed to CreatePublicHoliday.");
        $inputIsValid = FALSE;
    }

    //-------------------------------------------------------------
    // Check that a record with the DateID exists in the database.
    //-------------------------------------------------------------
    $dateRecord = RetrieveDateByID($dateID);

    if ($dateRecord == NULL) {
        $statusMessage.="Invalid Date supplied.</br>";
        error_log("DateID passed to CreatePublicHoliday doesn't exist ".
                  "in database.");
        $inputIsValid = FALSE;
    }

    if ($inputIsValid) {
        //-------------------------------------------------------------
        // Create the public holiday record in the database.
        //-------------------------------------------------------------
        $publicHoliday[PUB_HOL_ID] = NULL;
        $publicHoliday[PUB_HOL_NAME] = $nameOfPublicHoliday;
        $publicHoliday[PUB_HOL_DATE_ID] = $dateID;

        $success = sqlInsertPublicHoliday($publicHoliday);

        if (!$success) {
            $statusMessage.="Unexpected error when inserting to database.</br>";
            $inputIsValid = false;
            error_log("Failed to create public holiday.");
            $publicHoliday = NULL;
        } else {
            //-------------------------------------------------------------
            // Update the date records public holiday ID field.
            //-------------------------------------------------------------
            $dateRecord[DATE_TABLE_PUBLIC_HOL_ID] = $publicHoliday[PUB_HOL_ID];
            $success = UpdateDate($dateRecord);

            if (!$success) {
                $statusMessage.="Unexpected error when updating date table.</br>";
                $inputIsValid = false;
                error_log("Failed to update date reference to public holiday.");
                $publicHoliday = NULL;
            }
            else
            {
                $statusMessage.="Record successfully created.</br>";
            }
        }
    }
    
    GenerateStatus($inputIsValid, $statusMessage);
    return $publicHoliday;
}

/* ----------------------------------------------------------------------------
 * Function sqlInsertPublicHoliday
 *
 * This function constructs the SQL statement required to insert a new record
 * into the publicHolidayTable
 *
 * &$publicHoliday (array) Array containing all of the fields required for the 
 *                         record.
 * @return (bool) TRUE if insert into database was successful, false otherwise.
 * 		   
 * Note: If successful then the PUB_HOL_ID entry in the publicHoliday array
 * 	 passed by the caller will be set to the ID of the record in the database. 
 * ---------------------------------------------------------------------------*/

function sqlInsertPublicHoliday(&$publicHoliday) {
    $sql = "INSERT INTO publicHolidayTable (nameOfPublicHoliday,dateID) " .
            "VALUES ('"
            . $publicHoliday[PUB_HOL_NAME] . "','"
            . $publicHoliday[PUB_HOL_DATE_ID] . "');";

    $publicHoliday[PUB_HOL_ID] = performSQLInsert($sql);
    return $publicHoliday[PUB_HOL_ID] <> 0;
}

/* ----------------------------------------------------------------------------
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
 * --------------------------------------------------------------------------*/

function RetrievePublicHolidayByID($id) {
    $filter[PUB_HOL_ID] = $id;
    $resultArray = performSQLSelect(PUBLIC_HOLIDAY_TABLE, $filter);

    $result = NULL;

    if (count($resultArray) == 1) {      //Check to see if record was found.
        $result = $resultArray[0];
    }

    return $result;
}

/* ----------------------------------------------------------------------------
 * Function RetrievePublicHolidays
 *
 * This function constructs the SQL statement required to query the 
 * PublicHolidayTable.
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

function RetrievePublicHolidays($filter = NULL) {
    $inputIsValid = TRUE;

    //-------------------------------------------------------------------------
    // Validate Input parameters
    //-------------------------------------------------------------------------
    if ($filter <> NULL) {
        foreach ($filter as $key => $value) {
            if (strcmp($key, PUB_HOL_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid PUB_HOL_ID of " . $value .
                            " passed to RetrievePublicHolidays.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, PUB_HOL_NAME) == 0) {
                if (isNullOrEmptyString($value)) {
                    error_log("Invalid PUB_HOL_NAME passed to ".
                              "RetrievePublicHolidays.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, PUB_HOL_DATE_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid PUB_HOL_DATE_ID of " . $value .
                            " passed toRetrievePublicHolidays.");
                    $inputIsValid = FALSE;
                }
            } else {
                error_log("Unknown Filter " . $key . " passed to ".
                          "RetrievePublicHolidays.");
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
        $result = performSQLSelect(PUBLIC_HOLIDAY_TABLE, $filter);
    }
    return $result;
}

/* ----------------------------------------------------------------------------
 * Function UpdatePublicHoliday
 *
 * This function constructs the SQL statement required to update a row in 
 * the publicHolidayTable.
 *
 * $fields (array) array of key value pairs, where keys correspond to fields 
 *                 in the record (see constants at start of this file). Note, 
 *                 this array MUST provide the id of the record (PUB_HOL_ID) 
 *                 and one or more other fields to be updated. 
 *
 * @return (bool) TRUE if update succeeds. FALSE otherwise. 
 * --------------------------------------------------------------------------*/

function UpdatePublicHoliday($fields) {
    $statusMessage = "";
    //-------------------------------------------------------------------------
    // Validate Input parameters
    //-------------------------------------------------------------------------
    $inputIsValid = TRUE;
    $validID = false;
    $countOfFields = 0;
    $oldDateRecord = NULL;

    foreach ($fields as $key => $value) {
        if ($key == PUB_HOL_ID) {
            $record = RetrievePublicHolidayByID($value);
            if ($record <> NULL) {
                $validID = true;
                $countOfFields++;
                $oldDateRecord = RetrieveDateByID($record[PUB_HOL_DATE_ID]);
            }
        } else if ($key == PUB_HOL_NAME) {
            $countOfFields++;

            if (isNullOrEmptyString($value)) {
                $statusMessage.="Public holiday name must be entered.</br>";
                error_log("Invalid PUB_HOL_NAME passed to UpdatePublicHoliday.");
                $inputIsValid = FALSE;
            }
        } else if ($key == PUB_HOL_DATE_ID) {
            $countOfFields++;

            $record = RetrieveDateByID($value);

            if ($record == NULL) {
                $statusMessage.="Unable to located date in database.</br>";
                error_log("Invalid  PUB_HOL_DATE_ID passed to UpdatePublicHoliday.");
                $inputIsValid = FALSE;
            }
        } else {
            $statusMessage.="Unexpected field encountered in input.</br>";
            error_log("Invalid field passed to UpdatePublicHoliday.");
            $inputIsValid = FALSE;
        }
    }

    if (!$validID) {
        $statusMessage.="No valid ID supplied in call to UpdatePublicHoliday.</br>";
        error_log("No valid ID supplied in call to UpdatePublicHoliday.");
        $inputIsValid = FALSE;
    }

    if ($countOfFields < 2) {
        $statusMessage.="Insufficent fields supplied in call to UpdatePublicHoliday.</br>";
        error_log("Insufficent fields supplied in call to UpdatePublicHoliday.");
        $inputIsValid = FALSE;
    }

    //--------------------------------------------------------------------------
    // Only attempt to update a record in the database if the input parameters 
    // are ok.
    //--------------------------------------------------------------------------
    $success = false;

    if ($inputIsValid) {
        $success = performSQLUpdate(PUBLIC_HOLIDAY_TABLE, PUB_HOL_ID, $fields);
        if ($success)
        {
            $oldDateRecord[DATE_TABLE_PUBLIC_HOL_ID] = NULL;
            $success = UpdateDate($oldDateRecord);
            
            $dateRecord = RetrieveDateByID($fields[PUB_HOL_DATE_ID]);
            //-------------------------------------------------------------
            // Update the date records public holiday ID field.
            //-------------------------------------------------------------
            $dateRecord[DATE_TABLE_PUBLIC_HOL_ID] = $fields[PUB_HOL_ID];
            $success = UpdateDate($dateRecord);
            
            $statusMessage.="Record successfully updated.</br>";
        }
        else 
        {
            $statusMessage.="Unexpected error when updating the database.</br>";
            $inputIsValid = false;
        }
    }

    GenerateStatus($inputIsValid, $statusMessage);
    return $success;
}

/* -----------------------------------------------------------------------------
 * Function DeletePublicHoliday
 *
 * This function constructs the SQL statement required to delete a row in 
 * the PublicHolidayTable.
 *
 * $ID(integer) ID of the record to be removed from the table. This should be 
 *              set to the PUB_HOL_ID value of the record you wish to delete.
 *
 * @return (int) count of rows deleted. 0 means delete was unsuccessful. 
 * ---------------------------------------------------------------------------*/

function DeletePublicHoliday($ID) {
    $result = 0;
    $record = RetrievePublicHolidayByID($ID);

    if ($record <> NULL) {
        $date = RetrieveDateByID($record[PUB_HOL_DATE_ID]);
        $date[DATE_TABLE_PUBLIC_HOL_ID] = NULL;
        UpdateDate($date);

        $sql = "DELETE FROM publicHolidayTable WHERE publicHolidayID=". $ID. ";";
        $result = performSQL($sql);
    }
    GenerateStatus(true,"Record successfully deleted.");
    return $result;
}

?>