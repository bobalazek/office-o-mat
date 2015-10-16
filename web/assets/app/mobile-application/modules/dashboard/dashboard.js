angular
    .module(
        'mobileApplication.dashboard',
        [
            'ui.router',
            'ui.bootstrap',
            'ui.bootstrap.datetimepicker',
            'angularMoment',
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
        function DashboardController($rootScope, $scope, $state, $stateParams, $http, $cookies, $uibModal, toastr) {
            var vm = this;

            vm.type = $stateParams.type;

            if (vm.type == '') {
                $state.go('home');
            }

            // Employee
            var employeeCookie = $cookies.getObject('employee');
            vm.employee = null;
            vm.employeeWorkingTimes = [];
            vm.employeeCookie = employeeCookie;
            vm.employeeLogout = employeeLogout;
            vm.employeeWorkingTimeSaveModalOpen = employeeWorkingTimeSaveModalOpen;
            vm.employeeWorkingTimeRemoveModalOpen = employeeWorkingTimeRemoveModalOpen;

            if (vm.type == 'employee') {
                if (employeeCookie) {
                    $http({
                        method: 'GET',
                        url: 'api/me?access_token='+employeeCookie.access_token,
                    }).then(function(response) {
                        var data = response.data;

                        vm.employee = data;
                    }, function(response) {
                        var data = response.data;

                        toastr.error(
                            data.error.message
                        );

                        $state.go('login', { type: 'employee' });
                    });
                } else {
                    toastr.info(
                        'You are not logged in!'
                    );

                    $state.go('login', { type: 'employee' });
                }

                $scope.$watch(function() {
                    return vm.employee;
                }, function(newValue, oldValue) {
                    if (newValue != null) {
                        $http({
                            method: 'GET',
                            url: 'api/me/working-times?access_token='+employeeCookie.access_token,
                        }).then(function(response) {
                            var data = response.data;

                            vm.employeeWorkingTimes = data;
                        }, function(response) {
                            var data = response.data;

                            toastr.error(
                                data.error.message
                            );

                            $state.go('login', { type: 'employee' });
                        });
                    }
                })
            }

            // To-Do: Logout after the session expires

            function employeeLogout() {
                $http({
                    method: 'GET',
                    url: 'api/mobile/logout?access_token='+employeeCookie.access_token,
                }).then(function(response) {
                    var data = response.data;

                    toastr.success(
                        'You have successfully logged out!'
                    );

                    var expiresDate = new Date();

                    $cookies.putObject(
                        'employee',
                        null,
                        {
                            expires: expiresDate,
                        }
                    );

                    $state.go('login', { type: 'employee' });
                }, function(response) {
                    var data = response.data;

                    toastr.error(
                        data.error.message
                    );

                    $state.go('login', { type: 'employee' });
                });
            }

            function employeeWorkingTimeSaveModalOpen(selectedWorkingTime) {
                var employeeWorkingTimesSaveModal = $uibModal.open({
                    templateUrl: 'assets/app/mobile-application/modules/dashboard/dashboard-employee-save-modal.tmpl.html',
                    controller: 'EmployeeWorkingTimesSaveModalController as employeeWorkingTimesSaveModalScope',
                    resolve: {
                        selectedWorkingTime: selectedWorkingTime,
                    },
                });
            }

            function employeeWorkingTimeRemoveModalOpen(selectedWorkingTime) {

            }
            // Employee /END

            return vm;
        }
    )
    .controller (
        'EmployeeWorkingTimesSaveModalController',
        function EmployeeWorkingTimesSaveModalController($scope, $modalInstance, selectedWorkingTime) {
            var vm = this;

            vm.timeStarted = new Date();
            vm.timeEnded = null;

            return vm;
        }
    )
;
