<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<!DOCTYPE html>
<html ng-app>
    <head>
        <meta charset="UTF-8">
        <title>Amend Absence Type</title>
        <script src="http:////ajax.googleapis.com/ajax/libs/angularjs/1.2.1/angular.min.js"></script>
    </head>
 
    <body>

        <table>
            <tr>
                <td>Name</td>
                <td>Date of Birth</td>
                <td>Salary</td>
            </tr>

            <tr ng-repeat="person in people|orderBy:'salary'">
                <td>{{person.name|uppercase}}</td>
                <td>{{person.dateOfBirth|date:'dd-MM-yy'}}</td>
                <td>{{person.salary | currency:'£'}}</td>
            </tr>
        </table>
        
        
        
        <script>
            function customerController($scope)
            {
                $scope.people=[{name:'Jason',dateOfBirth:'1969-10-03',salary: '97000'},
                           {name:'Karen',dateOfBirth:'1969-12-20',salary: '12000'},
                           {name:'Sam',dateOfBirth:'1995-11-28',salary: '1000'}];
                       
                       
               $scope.doSort = function (propName)
               {
                   
               };
            }
        </script>
        
        
    </body>

</html>

