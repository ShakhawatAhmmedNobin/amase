var amaseApp = angular.module('amaseApp', [
    'ngRoute',
    'amaseControllers',
    'ui.bootstrap',
]);

amaseApp.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
            when('/machine', {
                templateUrl: 'html/home.html',
                controller: 'HomeCtrl',
                activetab: 'home'
            }).
            otherwise({
                redirectTo: '/home'
            });
    }]);