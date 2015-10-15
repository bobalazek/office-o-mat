angular
    .module(
        'mobileApplication.home',
        [
            'ui.router',
            'ui.bootstrap',
        ]
    )
    .config( function($stateProvider) {
        $stateProvider
            .state('home', {
                url : '/',
                templateUrl : 'assets/app/mobile-application/modules/home/home.tmpl.html',
            })
        ;
    })
    .controller (
        'HomeController',
        function HomeController($rootScope, $scope) {
            var vm = this;

            return vm;
        }
    )
;
