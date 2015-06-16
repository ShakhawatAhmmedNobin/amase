var amaseControllers = angular.module('amaseControllers');

amaseControllers.controller('amaseCtrl', ['$scope', '$http', '$interval', '$route',
    function ($scope, $http, $interval, $route) {

        $scope.$route = $route;
        $scope.user = {
            "username": "John"
        }

    }]);

amaseControllers.controller('HomeCtrl',  ['$scope', '$http', '$interval',
    function ($scope, $http, $interval) {

    }]);

