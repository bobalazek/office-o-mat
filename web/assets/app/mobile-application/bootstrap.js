angular
    .module(
        'mobileApplication',
        [
            'mobileApplication.home',
            'mobileApplication.login',
            'mobileApplication.dashboard',
        ]
    )
    .config( function($urlRouterProvider) {
        // Routes
        $urlRouterProvider.otherwise('/');
    })
    .controller (
        'MobileApplicationController',
        function MobileApplicationController($rootScope, $scope) {
            var vm = this;

            return vm;
        }
    )
;
