angular
    .module(
        'mobileApplication.login',
        [
            'ngAnimate',
            'LocalStorageModule',
            'ui.router',
            'ui.bootstrap',
            'toastr',
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
        function LoginController($rootScope, $scope, $state, $stateParams, $http, localStorageService, toastr) {
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
            vm.employeePinNumber = '';
            vm.employeeSelectForLogin = employeeSelectForLogin;
            vm.employeeLoginCancel = employeeLoginCancel;
            vm.employeeLogin = employeeLogin;

            // Logic & stuff
            if (vm.type == 'employee') {
                $http({
                    method: 'GET',
                    url: 'api/mobile/employees',
                }).then(function(response) {
                    var data = response.data;

                    vm.employees = data.employees;
                }, function(response) {
                    console.log('Error');
                    console.log(response);
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

            function employeeLogin() {
                $http({
                    method: 'POST',
                    url: 'api/mobile/login/employee',
                    data: 'user_id='+vm.employeeSelected.id+'&pin_number='+vm.employeePinNumber,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                }).then(function(response) {
                    var data = response.data;

                    toastr.success(
                        'You have successfully logged in!'
                    );

                    var expiresDate = new Date(data.time_access_token_expires);

                    localStorageService.set(
                        'employee',
                        data
                    );

                    $state.go('dashboard', { type: 'employee' });
                }, function(response) {
                    var data = response.data;

                    toastr.error(
                        data.error.message
                    );
                });
            }
            // Employee /END

            return vm;
        }
    )
;
