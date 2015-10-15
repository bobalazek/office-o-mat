angular
    .module(
        'mobileApplication',
        [
            'mobileApplication.home',
            'mobileApplication.login',
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
