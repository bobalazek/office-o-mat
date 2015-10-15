angular
    .module(
        'mobileApplication.dashboard',
        [
            'ui.router',
            'ui.bootstrap',
        ]
    )
    .config( function($stateProvider) {
        $stateProvider
            .state('dashboard', {
                url : '/dashboard/{type}',
                templateUrl : 'assets/app/mobile-application/modules/dashboard/dashboard.tmpl.html',
            })
        ;
    })
    .controller (
        'DashboardController',
        function DashboardController($rootScope, $scope, $state, $stateParams, $http, $cookies, toastr) {
            var vm = this;

            vm.type = $stateParams.type;

            if (vm.type == '') {
                $state.go('home');
            }

            // Employee
            var employeeCookie = $cookies.getObject('employee');
            vm.employee = null;

            console.log(employeeCookie);

            if (vm.type == 'employee') {
                if (employeeCookie) {
                    $http({
                        method: 'GET',
                        url: 'api/me?access_token='+employeeCookie.access_token,
                    }).then(function(response) {
                        var data = response.data;

                        vm.employee = data;

                        console.log(vm.employee);
                    }, function(response) {
                        console.log('Error');
                        console.log(response);
                    });
                } else {
                    toastr.info(
                        'No employee cookie found!'
                    );

                    $state.go('home');
                }
            }
            // Employee /END

            return vm;
        }
    )
;
