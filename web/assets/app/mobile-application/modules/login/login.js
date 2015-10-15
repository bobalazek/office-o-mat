angular
    .module(
        'mobileApplication.login',
        [
            'ui.router',
            'ui.bootstrap',
        ]
    )
    .config( function($stateProvider) {
        $stateProvider
            .state('login', {
                url : '/login/{type}',
                templateUrl : 'assets/app/mobile-application/modules/login/login.tmpl.html',
            })
        ;
    })
    .controller (
        'LoginController',
        function LoginController($rootScope, $scope, $state, $stateParams, $http) {
            var vm = this;

            vm.type = $stateParams.type;

            if (vm.type == '') {
                $state.go('home');
            }

            // Employee
            vm.employees = [];
            vm.employeesListShown = true;
            vm.employeesLoginFormShown = false;
            vm.employeeSelected = null;
            vm.employeeSelectForLogin = employeeSelectForLogin;
            vm.employeeLoginCancel = employeeLoginCancel;

            // Logic & stuff
            if (vm.type == 'employee') {
                $http({
                    method: 'GET',
                    url: 'api/mobile/employees',
                }).then(function(response) {
                    var data = response.data;

                    vm.employees = data.employees;
                }, function() {
                    console.log('Something Failed!');
                });
            }

            function employeeSelectForLogin(employee) {
                vm.employeeSelected = employee;
                vm.employeesListShown = false;
                vm.employeesLoginFormShown = true;
            }

            function employeeLoginCancel() {
                vm.employeeSelected = null;
                vm.employeesListShown = true;
                vm.employeesLoginFormShown = false;
            }
            // Employee /END

            return vm;
        }
    )
;
