var amaseApp = angular.module('amaseApp', [
    'ngRoute',
    'amaseControllers',
    'ui.bootstrap',
]);

amaseApp.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
            when('/home', {
                templateUrl: 'html/home.html',
                controller: 'HomeCtrl',
                activetab: 'home'
            }).
            when('/students', {
                templateUrl: 'html/home.html',
                controller: 'HomeCtrl',
                activetab: 'students'
            }).
            otherwise({
                redirectTo: '/home'
            });
    }]);