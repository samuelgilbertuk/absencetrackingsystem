<?php

/* -----------------------------------------------------------------------------
 * Includes
 * -------------------------------------------------------------------------- */

include 'status.php';
include 'AdHocRequestTable.php';
include 'CompanyRoleTable.php';
include 'EmployeeTable.php';
include 'MainVacationRequestTable.php';
include 'AbsenceTypeTable.php';
include 'ApprovedAbsenceBookingTable.php';
include 'ApprovedAbsenceBookingDateTable.php';
include 'DateTable.php';
include 'PublicHolidayTable.php';
include 'MailFunctions.php';
include 'KeyAlgorithms.php';


/* -----------------------------------------------------------------------------
 * Function IsValidDate
 *
 * This function checks the supplied date to determine whether or not
 * the date is a valid date in the format YYYY-MM-DD
 *
 * $date (string) string date to check.
 * @return (bool)  TRUE if date supplied is a valid date, otherwise FALSE.
 * -------------------------------------------------------------------------- */
function isValidDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') == $date;
}
/* -----------------------------------------------------------------------------
 * Function IsNULLOrEmptyString
 *
 * This function performs a basic check to test whether the string supplied
 * is NULL or empty.
 *
 * $inputString (string) string to check.
 * @return (bool)  TRUE if string is NULL or empty, otherwise FALSE.
 * -------------------------------------------------------------------------- */
function IsNullOrEmptyString($inputString) {
    return (!isset($inputString) || trim($inputString) == '');
}

/* -----------------------------------------------------------------------------
 * Function printCalstackAndDie
 *
 * This function is used in the event of a fatal error in processing, where
 * the program is no longer able to function. The code will print out the call
 * stack to the screen which may be helpful in debugging.
 * @return(none)
 * -------------------------------------------------------------------------- */

function printCallstackAndDie() {
    echo "Fatal Error. Please contact your system administrator.<br/>";

    $callers = debug_backtrace();

    echo "Dump Trace<br/>";
    foreach ($callers as $caller) {
        echo "Function:   " . $caller['function'] . "    Line:   " 
                . $caller['line'] . "<br/>";
    }
    die();
}

/* -----------------------------------------------------------------------------
 * Function connectToSQL
 *
 * This function performs the processing necessary to establish a connection
 * with the SQL database.
 *
 * $server (string) server that the database is on
 * $username (string) account username for the database.
 * $password (string) account password for the database.
 * @return (connection) id representing the database connection, or NULL if failed
 *                      to establish a connection.
 * -------------------------------------------------------------------------- */
function connectToSql($server, $username, $password) {
    $connection = mysqli_connect($server, $username, $password);
    if (!$connection) {
        printCallstackAndDie();
    }
    return $connection;
}

/* -----------------------------------------------------------------------------
 * Function performSQL
 *
 * This function takes an SQL string and executes this query agains the database.
 *
 * $sql (string) SQL statement to execute
 * @return (array)  array of rows containing the result, or NULL if the query failed.
 * -------------------------------------------------------------------------- */
function performSQL($sql) {
    $result = FALSE;
    $conn = $GLOBALS["connection"];
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("PerformSQL failed. Sql = $sql");
    }
    return $result;
}

/* -----------------------------------------------------------------------------
 * Function performSQLDelete
 *
 * This function takes an SQL string representing a delete from the database and
 * executes this query agains the database.
 *
 * $sql (string) SQL statement to execute
 * @return (int) count of rows deleted or 0 if no rows were deleted.
 * -------------------------------------------------------------------------- */
function performSQLDelete($sql) {
    $deletedRows = 0;

    $conn = $GLOBALS["connection"];
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $deletedRows = mysqli_affected_rows($conn);
    }

    return $deletedRows;
}

/* -----------------------------------------------------------------------------
 * Function performSQLInsert
 *
 * This function takes an SQL string representing an insert into the database and
 * executes this query agains the database.
 *
 * $sql (string) SQL statement to execute
 * @return (int)id of the record creased in the database or 0 if insert failed.
 * -------------------------------------------------------------------------- */
function performSQLInsert($sql) {
    $conn = $GLOBALS["connection"];
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        printCallstackAndDie();
    }
    return mysqli_insert_id($conn);
}

/* -----------------------------------------------------------------------------
 * Function performSQLSelect
 *
 * This function forms an select query on the database
 *  and executes this query agains the database.
 *
 * $tableName (string) name of the database table that this query should be
 *                     performed on
 * $filter (array) set of key value pairs to use in the WHERE clause of the sql
 *                 query. Note, this value can be NULL if you want to access
 *                 all records in the table.
 * @return (array) set of records matching the filter.
 * -------------------------------------------------------------------------- */
function performSQLSelect($tableName, $filter) {
    $conn = $GLOBALS["connection"];

    $sql = "SELECT * FROM " . $tableName;
    if ($filter <> NULL) {
        $sql = $sql . " WHERE ";

        foreach ($filter as $key => $value) {
            $whereClause[] = $key . "='" . $value . "'";
        }

        $sql = $sql . implode(" AND ", $whereClause);
    }
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        printCallstackAndDie();
    }
    $results = NULL;
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $results[] = $row;
    }


    return $results;
}

/* -----------------------------------------------------------------------------
 * Function performSQLUpdate
 *
 * This function forms an update statement on the database
 *  and executes this query agains the database.
 *
 * $tableName (string) name of the database table that this update should be
 *                     performed on
 * $filter (array) set of key value pairs which contains the field names and 
 *                 values to be applied i this update.
 * @return (bool) TRUE if the update was performed successfully, FALSE otherwise.
 * -------------------------------------------------------------------------- */
function performSQLUpdate($tableName, $idFieldName, $fields) {
    $conn = $GLOBALS["connection"];
    $sql = "UPDATE " . $tableName . " SET ";

    if ($fields <> NULL) {
        foreach ($fields as $key => $value) {
            if (!is_numeric($key) AND $key <> $idFieldName) {
                if ($value <> NULL) {
                    $updateClause[] = $key . "='" . $value . "'";
                } else {
                    $updateClause[] = $key . "=NULL";
                }
            }
        }

        $sql = $sql . implode(",", $updateClause);
    }
    $sql = $sql . " WHERE " . $idFieldName . "='" . $fields[$idFieldName] . "';";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        echo mysqli_error($conn);
        printCallstackAndDie();
    }

    return TRUE;
}

/* -----------------------------------------------------------------------------
 * Function UseDB
 * 
 * Very simple function that performs the SQL necessary to use the database.
 * @return (none) 
 * -------------------------------------------------------------------------- */
function UseDB() {
    $sql = "USE mydb;";
    performSQL($sql);
}

/* -----------------------------------------------------------------------------
 * Function DropDB
 *
 * Very simple function that performs the SQL necessary to drop the database.
 * @return (none) 
 * -------------------------------------------------------------------------- */
function DropDB() {
    $sql = "DROP DATABASE IF EXISTS `mydb`;";
    performSQL($sql);
}

/* -----------------------------------------------------------------------------
 * Function CreateDB
 *
 * Very simple function that performs the SQL necessary to create the database.
 * @return (none) 
 * -------------------------------------------------------------------------- */
function CreateDB() {
    $sql = "CREATE SCHEMA IF NOT EXISTS `mydb`" .
            "DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;";
    performSQL($sql);
}

/* -----------------------------------------------------------------------------
 * Function CreateDefaultRecords
 *
 * This function is used to populate some of the tables in the database with 
 * basic date required for operation. This includes creating the admin account
 * based on parametes supplied and creating the date entries in the
 * date table.
 *
 * @return (none) 
 * -------------------------------------------------------------------------- */
function CreateDefaultRecords($adminName,$adminEmail,$adminPassword,
                              $adminDateJoined,$adminLeaveEntitlement) {
    
    $filter[COMP_ROLE_NAME] = "Admin";
    $records = RetrieveCompanyRoles($filter);
    if (count($records) == 0)
    {
        $role = CreateCompanyRole("Admin", 0);
    }
    else 
    {
        $role = $records[0];
    }
    
    $success = CreateEmployee($adminName, $adminEmail, 
                              $adminPassword, $adminDateJoined, 
                              $adminLeaveEntitlement, NULL, 
                              $role[COMP_ROLE_ID], 1, 1);
    if ($success)
    {
        date_default_timezone_set('UTC');
        $date = '2015-01-01';
        $end_date = '2055-12-31';

        while (strtotime($date) <= strtotime($end_date)) {
            CreateDate($date, NULL);
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
    }
    return $success;
}


/* -----------------------------------------------------------------------------
 * Function CreateNewDatabase
 *
 * This function creates the database and all tables within it.
 *
 * $destroyExistingDB(bool) ic TRUE, will destroy existing database
 * $createWithTestData(bool) if TRUE, will populate new database with test data.
 * @return (none) 
 * -------------------------------------------------------------------------- */
function CreateNewDatabase($destroyExistingDB = false, $createWithTestData = false) {
    if ($destroyExistingDB) {
        DropDB();
    }
    CreateDB();
    UseDB();
    CreateDateTable();
    CreatePublicHolidayTable();
    CreateAbsenceTypeTable();
    CreateCompanyRoleTable();
    CreateEmployeeTable();
    CreateApprovedAbsenceBookingTable();
    CreateApprovedAbsenceDateTable();
    CreateAdHocAbsenceRequestTable();
    CreateMainVacationRequestTable();

    if ($createWithTestData) {

        $annualLeave = CreateAbsenceType("Annual Leave", 1, 1);
        $training = CreateAbsenceType("Training", 0, 1);
        $sickness = CreateAbsenceType("Sickness", 0, 0);
        $compasionate = CreateAbsenceType("Compasionate Leave", 0, 1);

        $cashier = CreateCompanyRole("Cashier", 3);
        $customerAdvisor = CreateCompanyRole("Customer Advisor", 2);
        $manager = CreateCompanyRole("Manager", 1);

        $steveBrookstein = CreateEmployee("Steve Brookstein", 
                                          "stevebrookstein@test.com", 
                                           "Zaq12wsx", "2005-01-01", 20, NULL, 
                                           $cashier[COMP_ROLE_ID], 0, 0);

        $shayneWard = CreateEmployee("Shane Ward", 
                                     "shaneWard@test.com", 
                                     "Zaq12wsx", "2006-01-01", 20, NULL, 
                                     $cashier[COMP_ROLE_ID], 0, 0);


        $leonJackson = CreateEmployee("Leon Jackson", "leonjackson@test.com", 
                                      "Zaq12wsx", "2008-01-01", 20, NULL, 
                                      $manager[COMP_ROLE_ID], 0, 0);

        $alexandraBurke = CreateEmployee("Alexandra Burke", "alexburke@test.com",
                                        "Zaq12wsx", "2009-01-01", 20, NULL, 
                                        $cashier[COMP_ROLE_ID], 0, 0);

        $joeMcElderry = CreateEmployee("Joe McElderry", "JoeMcElderry@test.com",
                                        "Zaq12wsx", "2010-01-01", 20, NULL, 
                                        $customerAdvisor[COMP_ROLE_ID], 0, 0);

        $mattCardle = CreateEmployee("Matt Cardle", "mattCardle@test.com", 
                                     "Zaq12wsx", "2011-01-01", 20, NULL, 
                                     $customerAdvisor[COMP_ROLE_ID], 0, 0);
        
        $jamesArthur = CreateEmployee("James Arthur", "jamesarthur@test.com", 
                                      "Zaq12wsx", "2012-01-01", 20, NULL, 
                                      $customerAdvisor[COMP_ROLE_ID], 0, 0);

        $samBailey = CreateEmployee("Sam Bailey", "sambailey@test.com", 
                                    "Zaq12wsx", "2013-01-01", 20, NULL, 
                                    $customerAdvisor[COMP_ROLE_ID], 0, 0);

        $benHaenow = CreateEmployee("Ben Haenow", "benHaenow@test.com", 
                                    "Zaq12wsx", "2014-01-01", 20, NULL, 
                                    $manager[COMP_ROLE_ID], 0, 1);


        $dates = RetrieveDates();

        if (count($dates) == 0) {
            date_default_timezone_set('UTC');

            // Start date
            $date = '2015-01-01';

            // End date
            $end_date = '2055-12-31';

            while (strtotime($date) <= strtotime($end_date)) {
                CreateDate($date, NULL);
                $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            }
        }

        $dateID = RetrieveDateIDByDate("2015-04-03");
        $goodFriday = CreatePublicHoliday("Good Friday", $dateID);

        $dateID = RetrieveDateIDByDate("2015-04-06");
        $easterMonday = CreatePublicHoliday("Easter Monday", $dateID);

        $dateID = RetrieveDateIDByDate("2015-05-04");
        $earlyMay = CreatePublicHoliday("Early May Bank Holiday", $dateID);

        $dateID = RetrieveDateIDByDate("2015-05-25");
        $springHoliday = CreatePublicHoliday("Spring Bank Holiday", $dateID);

        $dateID = RetrieveDateIDByDate("2015-08-31");
        $summerHoliday = CreatePublicHoliday("Summer Bank Holiday", $dateID);

        $dateID = RetrieveDateIDByDate("2015-12-25");
        $christmasDay = CreatePublicHoliday("Christmas Day", $dateID);

        $dateID = RetrieveDateIDByDate("2015-12-28");
        $boxingDay = CreatePublicHoliday("Boxing Day (substitute day)", $dateID);

        $request = CreateMainVactionRequest($steveBrookstein[EMP_ID], 
                    "2015-01-10", "2015-01-15", "2015-02-10", "2015-02-15");

        $request = CreateMainVactionRequest($shayneWard[EMP_ID], 
                    "2015-01-10", "2015-01-15", "2015-02-10", "2015-02-15");
        

        
        $request = CreateMainVactionRequest($leonJackson[EMP_ID], 
                    "2015-01-10", "2015-01-15", "2015-02-10", "2015-02-15");
        
        $request = CreateMainVactionRequest($alexandraBurke[EMP_ID], 
                    "2015-01-10", "2015-01-15", "2015-02-10", "2015-02-15");
        
        $request = CreateMainVactionRequest($joeMcElderry [EMP_ID], 
                    "2015-01-10", "2015-01-15", "2015-02-10", "2015-02-15");
        
        $request = CreateMainVactionRequest($jamesArthur [EMP_ID], 
                    "2015-01-10", "2015-01-15", "2015-02-10", "2015-02-15");
        
        $request = CreateMainVactionRequest($mattCardle[EMP_ID], 
                    "2015-01-10", "2015-01-15", "2015-02-10", "2015-02-15");
        
        $request = CreateMainVactionRequest($samBailey[EMP_ID], 
                    "2015-01-10", "2015-01-15", "2015-02-10", "2015-02-15");
        
        $request = CreateMainVactionRequest($benHaenow [EMP_ID], 
                    "2015-01-10", "2015-01-15", "2015-02-10", "2015-02-15");


        $request = CreateAdHocAbsenceRequest($steveBrookstein[EMP_ID], 
                    "2015-03-10", "2015-03-15", $annualLeave[ABS_TYPE_ID]);
        
        $request = CreateAdHocAbsenceRequest($shayneWard[EMP_ID], 
                    "2015-03-10", "2015-03-15", $annualLeave[ABS_TYPE_ID]);
        

        
        $request = CreateAdHocAbsenceRequest($leonJackson[EMP_ID], 
                    "2015-03-10", "2015-03-15", $sickness[ABS_TYPE_ID]);
        
        $request = CreateAdHocAbsenceRequest($alexandraBurke[EMP_ID], 
                    "2015-03-10", "2015-03-15", $training[ABS_TYPE_ID]);
        
        $request = CreateAdHocAbsenceRequest($joeMcElderry[EMP_ID], 
                    "2015-03-10", "2015-03-15", $training[ABS_TYPE_ID]);
        
        $request = CreateAdHocAbsenceRequest($mattCardle[EMP_ID], 
                    "2015-03-10", "2015-03-15", $training[ABS_TYPE_ID]);
        
        $request = CreateAdHocAbsenceRequest($jamesArthur[EMP_ID], 
                    "2015-03-10", "2015-03-15", $training[ABS_TYPE_ID]);
        
        $request = CreateAdHocAbsenceRequest($samBailey[EMP_ID], 
                    "2015-03-10", "2015-03-15", $compasionate[ABS_TYPE_ID]);
        
        $request = CreateAdHocAbsenceRequest($benHaenow[EMP_ID], 
                    "2015-03-10", "2015-03-15", $compasionate[ABS_TYPE_ID]);
        
        
        $leonaLewis = CreateEmployee("Leona Lewis", "leonalewis@test.com", 
                                     "Zaq12wsx", "2007-01-01", 20, NULL, 
                                     $cashier[COMP_ROLE_ID], 1, 1);
        
        $request = CreateAdHocAbsenceRequest($leonaLewis[EMP_ID], 
                    "2015-03-10", "2015-03-15", $sickness[ABS_TYPE_ID]);
                
        $request = CreateMainVactionRequest($leonaLewis[EMP_ID], 
                    "2015-01-10", "2015-01-15", "2015-02-10", "2015-02-15");
        
        $request = CreateApprovedAbsenceBooking($leonaLewis[EMP_ID], 
                                                "2015-04-01","2015-04-10",
                                                $training[ABS_TYPE_ID]);
    }
}

?>