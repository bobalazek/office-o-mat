angular
    .module(
        'mobileApplication.dashboard',
        [
            'ui.router',
            'ui.bootstrap',
            'ui.bootstrap.datetimepicker',
            'angularMoment',
            'ngAnimate',
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
        function DashboardController($rootScope, $scope, $state, $stateParams, $http, $interval, $uibModal, localStorageService, toastr) {
            var vm = this;

            vm.type = $stateParams.type;

            if (vm.type == '') {
                $state.go('home');
            }

            // Employee
            vm.employee = null;
            vm.employeeWorkingTimes = [];
            vm.employeeStatistics = [];
            vm.employeeInterval = null;
            vm.employeeSelectedWorkingTime = null;
            vm.employeeData = localStorageService.get('employee');
            vm.employeeLogout = employeeLogout;
            vm.employeeWorkingTimeSaveModalOpen = employeeWorkingTimeSaveModalOpen;
            vm.employeeWorkingTimeRemoveModalOpen = employeeWorkingTimeRemoveModalOpen;

            if (vm.type == 'employee') {
                if (vm.employeeData) {
                    loadEmployee();

                    // Run to see if we have any changes AND to force logout the user when session is over
                    vm.employeeInterval = $interval(
                        loadEmployee,
                        8000
                    );
                } else {
                    toastr.info(
                        'You are not logged in!'
                    );

                    $state.go('login', { type: 'employee' });
                }
            }

            function loadEmployee() {
                $http({
                    method: 'GET',
                    url: 'api/me?access_token='+vm.employeeData.access_token,
                }).then(function(response) {
                    var data = response.data;

                    if (vm.employee != data) {
                        vm.employee = data;

                        loadEmployeeWorkingTimes();
                    }
                }, function(response) {
                    var data = response.data;

                    toastr.error(
                        data.error.message
                    );

                    if (vm.employeeInterval != null) {
                        $interval.cancel(vm.employeeInterval);
                    }

                    localStorageService.remove('employee');
                    
                    vm.employeeData = null;
                    vm.employee = null;
                    vm.employeeWorkingTimes = [];

                    $state.go('login', { type: 'employee' });
                });
            }

            function loadEmployeeWorkingTimes() {
                $http({
                    method: 'GET',
                    url: 'api/me/working-times?access_token='+vm.employeeData.access_token,
                }).then(function(response) {
                    var data = response.data;

                    vm.employeeWorkingTimes = data;

                    loadEmployeeStatistics()
                }, function(response) {
                    var data = response.data;

                    localStorageService.remove('employee');
                    vm.employeeData = null;

                    toastr.error(
                        data.error.message
                    );

                    $state.go('login', { type: 'employee' });
                });
            }

            function loadEmployeeStatistics() {
                $http({
                    method: 'GET',
                    url: 'api/me/statistics?access_token='+vm.employeeData.access_token,
                }).then(function(response) {
                    var data = response.data;

                    vm.employeeStatistics = data;
                }, function(response) {
                    var data = response.data;

                    localStorageService.remove('employee');
                    vm.employeeData = null;

                    toastr.error(
                        data.error.message
                    );

                    $state.go('login', { type: 'employee' });
                });
            }

            function employeeLogout() {
                $http({
                    method: 'GET',
                    url: 'api/mobile/logout?access_token='+vm.employeeData.access_token,
                }).then(function(response) {
                    var data = response.data;

                    if (vm.employeeInterval != null) {
                        $interval.cancel(vm.employeeInterval);
                    }

                    toastr.success(
                        'You have successfully logged out!'
                    );
                }, function(response) {
                    var data = response.data;

                    toastr.error(
                        data.error.message
                    );
                }).finally(function() {
                    localStorageService.remove('employee');
                    vm.employeeData = null;

                    $state.go('login', { type: 'employee' });
                });
            }

            function employeeWorkingTimeSaveModalOpen(selectedWorkingTime) {
                vm.employeeSelectedWorkingTime = selectedWorkingTime;
                var employeeWorkingTimesSaveModal = $uibModal.open({
                    templateUrl: 'assets/app/mobile-application/modules/dashboard/dashboard-employee-save-modal.tmpl.html',
                    controller: 'EmployeeWorkingTimesSaveModalController as employeeWorkingTimesSaveModalScope',
                    resolve: {
                        selectedWorkingTime: selectedWorkingTime,
                        employeeData: vm.employeeData,
                    },
                });

                employeeWorkingTimesSaveModal.result.then(function(data) {
                    loadEmployeeWorkingTimes();
                }, function() {
                    // Dismissed
                }).finally(function() {
                    vm.employeeSelectedWorkingTime = null;;
                });
            }

            function employeeWorkingTimeRemoveModalOpen(selectedWorkingTime) {
                vm.employeeSelectedWorkingTime = selectedWorkingTime;
                var employeeWorkingTimesRemoveModal = $uibModal.open({
                    templateUrl: 'assets/app/mobile-application/modules/dashboard/dashboard-employee-remove-modal.tmpl.html',
                    controller: 'EmployeeWorkingTimesRemoveModalController as employeeWorkingTimesRemoveModalScope',
                    resolve: {
                        selectedWorkingTime: selectedWorkingTime,
                        employeeData: vm.employeeData,
                    },
                });

                employeeWorkingTimesRemoveModal.result.then(function(data) {
                    loadEmployeeWorkingTimes();
                }, function() {
                    // Dismissed
                }).finally(function() {
                    vm.employeeSelectedWorkingTime = null;;
                });
            }
            // Employee /END

            return vm;
        }
    )
    .controller (
        'EmployeeWorkingTimesSaveModalController',
        function EmployeeWorkingTimesSaveModalController($scope, $modalInstance, $state, $http, toastr, selectedWorkingTime, employeeData) {
            var vm = this;

            vm.timeStartedCalendarIsOpen = false;
            vm.timeStartedCalendarOpenCalendar = timeStartedCalendarOpenCalendar;
            vm.timeEndedCalendarIsOpen = false;
            vm.timeEndedCalendarOpenCalendar = timeEndedCalendarOpenCalendar;

            if (typeof selectedWorkingTime !== 'undefined') {
                vm.action = 'edit';
                vm.modalTitle = 'Edit Working Time';

                vm.form = {
                    id: selectedWorkingTime.id,
                    timeStarted: new Date(selectedWorkingTime.time_started),
                    timeEnded: selectedWorkingTime.time_ended != null
                        ? new Date(selectedWorkingTime.time_ended)
                        : null,
                    notes: selectedWorkingTime.notes,
                    location: selectedWorkingTime.location,
                };
            } else {
                vm.action = 'new';
                vm.modalTitle = 'New Working Time';

                vm.form = {
                    id: null,
                    timeStarted: new Date(),
                    timeEnded: null,
                    notes: null,
                    location: null,
                };
            }

            vm.save = save;
            vm.cancel = cancel;

            /*** Functions ***/
            function timeStartedCalendarOpenCalendar(e) {
                e.preventDefault();
                e.stopPropagation();

                vm.timeStartedCalendarIsOpen = true;
            }

            function timeEndedCalendarOpenCalendar(e) {
                e.preventDefault();
                e.stopPropagation();

                vm.timeEndedCalendarIsOpen = true;
            }

            function save() {
                if (vm.action == 'new') {
                    var method = 'POST';
                    var url = 'api/me/working-times?access_token='+employeeData.access_token;
                    var successText = 'The working time has been successfully added!';
                } else {
                    var method = 'PUT';
                    var url = 'api/me/working-times/'+vm.form.id+'?access_token='+employeeData.access_token;
                    var successText = 'The working time has been successfully edited!';
                }

                $http({
                    method: method,
                    url: url,
                    data: jQuery.param(vm.form),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                }).then(function(response) {
                    var data = response.data;

                    toastr.success(successText);

                    $modalInstance.close();
                }, function(response) {
                    var data = response.data;

                    if (typeof data.error !== 'undefined') {
                        toastr.error(
                            data.error.message
                        );

                        $modalInstance.dismiss('cancel');

                        $state.go('login', { type: 'employee' });
                    } else if(typeof data.errors !== 'undefined') {
                        // When plural, then it spits out the validation errors.
                        toastr.error(
                            data.errors[0].message
                        );
                    }
                });
            }

            function cancel() {
                $modalInstance.dismiss('cancel');
            }

            return vm;
        }
    )
    .controller (
        'EmployeeWorkingTimesRemoveModalController',
        function EmployeeWorkingTimesRemoveModalController($scope, $modalInstance, $state, $http, toastr, selectedWorkingTime, employeeData) {
            var vm = this;

            vm.confirm = confirm;
            vm.cancel = cancel;

            /*** Functions ***/
            function confirm() {
                $http({
                    method: 'DELETE',
                    url: 'api/me/working-times/'+selectedWorkingTime.id+'?access_token='+employeeData.access_token,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                }).then(function(response) {
                    var data = response.data;

                    toastr.success(
                        'You have successfully removed the working time!'
                    );

                    $modalInstance.close();
                }, function(response) {
                    var data = response.data;

                    if (typeof data.error !== 'undefined') {
                        toastr.error(
                            data.error.message
                        );

                        $modalInstance.dismiss('cancel');

                        $state.go('login', { type: 'employee' });
                    } else if(typeof data.errors !== 'undefined') {
                        // When plural, then it spits out the validation errors.
                        toastr.error(
                            data.errors[0].message
                        );
                    }
                });
            }

            function cancel() {
                $modalInstance.dismiss('cancel');
            }

            return vm;
        }
    )
;
