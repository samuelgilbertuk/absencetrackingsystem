<?php

/* -----------------------------------------------------------------------------
 * CONSTANTS
 *
 * These constants should be used when refering to the table and the fields 
 * within its records.
 * ---------------------------------------------------------------------------*/
define("COMPANY_ROLE_TABLE", "companyRoleTable");
define("COMP_ROLE_ID", "companyRoleID");
define("COMP_ROLE_NAME", "roleName");
define("COMP_ROLE_MIN_STAFF", "minimumStaffingLevel");

/* ----------------------------------------------------------------------------
 * Function CreateCompanyRoleTable
 *
 * This function creates the SQL statement needed to construct the table
 * in the database.
 *
 * @return (bool)  True if table is created successfully, false otherwise.
 * ---------------------------------------------------------------------------*/

function CreateCompanyRoleTable() {
    $sql = "CREATE TABLE IF NOT EXISTS `mydb`.`companyRoleTable` (
         `companyRoleID` INT NOT NULL AUTO_INCREMENT,
         `roleName` VARCHAR(30) NOT NULL,
         `minimumStaffingLevel` INT(1) NOT NULL,
          PRIMARY KEY (`companyRoleID`));";
    performSQL($sql);
}

/* ---------------------------------------------------------------------------
 * Function CreateCompanyRole
 *
 * This function creates a new CompanyRole record in the 
 * table.
 *
 * $roleName (string) name of the role.
 * $minStaffLevel (int) minimum number of staff that needs to be maintained.
 *
 * @return (array) If successful, an array is returned where each key represents 
 *                 a field in the record. If unsuccessful, the return will be 
 *                 NULL.
 * ------------------------------------------------------------------------- */

function CreateCompanyRole($roleName, $minStaffLevel) {
    $role = NULL;
    //-------------------------------------------------------------------------
    // Validate Input parameters
    //-------------------------------------------------------------------------
    $inputIsValid = TRUE;
    $statusMessage = "";
    if ($roleName == NULL) {
        $statusMessage .= "Invalid Company Role Name."; 
        error_log("Invalid NULL name passed to CreateCompanyRole.");
        $inputIsValid = FALSE;
    }

    if (isNullOrEmptyString($roleName)) {
        $statusMessage .= "Company Role Name must be specified."; 
        error_log("Invalid roleName passed to CreateCompanyRole.");
        $inputIsValid = FALSE;
    }

    if (!is_numeric($minStaffLevel)) {
        $statusMessage .= "Minimum Staff Level must be numeric."; 
        error_log("Invalid minStaffLevel parameter passed to CreateCompanyRole.");
        $inputIsValid = FALSE;
    }
    
 
    //-------------------------------------------------------------------------
    // Only attempt to insert a record in the database if the input parameters 
    // are ok.
    //-------------------------------------------------------------------------
    if ($inputIsValid) {
        // Create an array with each field required in the record. 
        $role[COMP_ROLE_ID] = NULL;
        $role[COMP_ROLE_NAME] = $roleName;
        $role[COMP_ROLE_MIN_STAFF] = $minStaffLevel;

        $success = sqlInsertCompanyRole($role);
        if (!$success) {
            $statusMessage .= "Failed to add company role to the database.".
                             "Please contact your system administrator."; 
            error_log("Failed to create company Role. " . print_r($role));
            $role = NULL;
        }
        else
        {
            $statusMessage .= "Company Role created successfully.";
        }
        
    }
    GenerateStatus($inputIsValid, $statusMessage);
    return $role;
}

/* -----------------------------------------------------------------------------
 * Function sqlInsertCompanyRole 
 *
 * This function constructs the SQL statement required to insert a new record
 * into the companyRole table.
 *
 * &$role (array) Array containing all of the fields required for the record.
 *
 * @return (bool) TRUE if insert into database was successful, false otherwise.
 * 		   
 * Note: If successful then the COMP_ROLE_ID entry in the 
 * array passed by the caller will be set to the ID of the record in the database. 
 * ---------------------------------------------------------------------------*/

function sqlInsertCompanyRole(&$role) {
    $sql = "INSERT INTO companyroletable (roleName,minimumStaffingLevel) " .
            "VALUES ('" . $role[COMP_ROLE_NAME] . "'," . $role[COMP_ROLE_MIN_STAFF] . ");";

    $role[COMP_ROLE_ID] = performSQLInsert($sql);
    return ($role[COMP_ROLE_ID] <> 0);
}

/* ----------------------------------------------------------------------------
 * Function RetrieveCompanyRoleByID
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

function RetrieveCompanyRoleByID($id) {
    $filter[COMP_ROLE_ID] = $id;
    $resultArray = performSQLSelect(COMPANY_ROLE_TABLE, $filter);

    $result = NULL;

    if (count($resultArray) == 1) {      //Check to see if record was found.
        $result = $resultArray[0];
    }

    return $result;
}

/* -----------------------------------------------------------------------------
 * Function RetrieveCompanyRoles
 *
 * This function constructs the SQL statement required to query the CompanyRole 
 * table.
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

function RetrieveCompanyRoles($filter = NULL) {
    $inputIsValid = TRUE;

    //--------------------------------------------------------------------------------
    // Validate Input parameters
    //--------------------------------------------------------------------------------
    if ($filter <> NULL) {
        foreach ($filter as $key => $value) {
            if (strcmp($key, COMP_ROLE_ID) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid COMP_ROLE_ID of " . $value .
                            " passed to RetrieveCompanyRoles.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, COMP_ROLE_NAME) == 0) {
                if (isNullOrEmptyString($value)) {
                    error_log("Invalid COMP_ROLE_NAME of " . $value .
                            " passed to RetrieveCompanyRoles.");
                    $inputIsValid = FALSE;
                }
            } else if (strcmp($key, COMP_ROLE_MIN_STAFF) == 0) {
                if (!is_numeric($value)) {
                    error_log("Invalid COMP_ROLE_MIN_STAFF of " . $value .
                            " passed to RetrieveCompanyRoles.");
                    $inputIsValid = FALSE;
                }
            } else {
                error_log("Unknown Filter " . $key . " passed to RetrieveCompanyRoles.");
                $inputIsValid = FALSE;
            }
        }
    }

    //--------------------------------------------------------------------------
    // Only attempt to perform query in the database if the input parameters are
    // ok.
    //--------------------------------------------------------------------------
    $result = NULL;
    if ($inputIsValid) {
        $result = performSQLSelect(COMPANY_ROLE_TABLE, $filter);
    }
    return $result;
}

/* -----------------------------------------------------------------------------
 * Function UpdateCompanyRole
 *
 * This function constructs the SQL statement required to update a row in 
 * the CompanyRole table.
 *
 * $fields (array) array of key value pairs, where keys correspond to fields 
 *                 in the record (see constants at start of this file). Note, 
 *                 this array MUST provide the id of the record (COMP_ROLE_ID) 
 *                 and one or more other fields to be updated. 
 *
 * @return (bool) TRUE if update succeeds. FALSE otherwise. 
 * ---------------------------------------------------------------------------*/

function UpdateCompanyRole($fields) {
    //-------------------------------------------------------------------------
    // Validate Input parameters
    //-------------------------------------------------------------------------
    $statusMessage="";
    $inputIsValid = TRUE;
    $validID = false;
    $countOfFields = 0;

    foreach ($fields as $key => $value) {
        if ($key == COMP_ROLE_ID) {
            $record = RetrieveCompanyRoleByID($value);
            if ($record <> NULL) {
                $validID = true;
                $countOfFields++;
            }
            else
            {
                $statusMessage.= "Unable to locate Company Role in the database.";
                error_log("No valid ID supplied in call to UpdateCompanyRole.");
                $inputIsValid = FALSE;
            }
        } else if ($key == COMP_ROLE_NAME) {
            $countOfFields++;

            if (isNullOrEmptyString($value)) {
                $statusMessage  .= "You must enter a company role name.";
                error_log("Invalid COMP_ROLE_NAME passed to UpdateCompanyRole.");
                $inputIsValid = FALSE;
            }
        } else if ($key == COMP_ROLE_MIN_STAFF) {
            $countOfFields++;

            if (!is_numeric($value)) {
                $statusMessage  .= "You must enter a numeric value for minimum staff";
                error_log("Invalid COMP_ROLE_MIN_STAFF passed to UpdateCompanyRole.");
                $inputIsValid = FALSE;
            }
        } else {
            $statusMessage  .= "Invalid field.";
            error_log("Invalid field passed to UpdateCompanyRole. $key=" . $key);
            $inputIsValid = FALSE;
        }
    }

    if ($countOfFields < 2) {
        $statusMessage  .= "You must alter at least one field before updating.";
        error_log("Insufficent fields supplied in call to UpdateCompanyRole.");
        $inputIsValid = FALSE;
    }

    //-------------------------------------------------------------------------
    // Only attempt to update a record in the database if the input parameters 
    // are ok.
    //-------------------------------------------------------------------------
    $success = false;

    if ($inputIsValid) {
        $success = performSQLUpdate(COMPANY_ROLE_TABLE, COMP_ROLE_ID, $fields);
        $statusMessage .= "Record has been updated successfully.";
    }
    
    GenerateStatus($inputIsValid, $statusMessage);
    return $success;
}

/* ----------------------------------------------------------------------------
 * Function DeleteCompanyRole
 *
 * This function constructs the SQL statement required to delete a row in 
 * the CompanyRole table.
 *
 * $ID(integer) ID of the record to be removed from the table. This should be set  
 *              to the COMP_ROLE_ID value of the record you wish to delete.
 *
 * @return (int) count of rows deleted. 0 means delete was unsuccessful. 
 * -------------------------------------------------------------------------- */

function DeleteCompanyRole($ID) {
    $statusMessage = "";
    $result = 0;
    $record = RetrieveCompanyRoleByID($ID);
    if ($record <> NULL) {
        $filter[EMP_COMPANY_ROLE] = $ID;
        $employees = RetrieveEmployees($filter);

        if ($employees <> NULL) {
            foreach ($employees as $employee) {
                DeleteEmployee($employee[EMP_ID]);
            }
        }

        $sql = "DELETE FROM companyroletable WHERE companyRoleID=" . $ID . ";";
        $result = performSQL($sql);
        $statusMessage = "Role Deleted.</br>";
        GenerateStatus(true, $statusMessage);
    }
    return $result;
}

?>