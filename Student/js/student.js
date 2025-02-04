document.addEventListener("DOMContentLoaded", function() {
    // Calendar Variables
    const monthName = document.getElementById('monthName');
    const calendarDays = document.getElementById('calendarDays');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const addEventBtn = document.getElementById('addEvent');

    let currentDate = new Date();

    function renderCalendar() {
        calendarDays.innerHTML = '';  // Clear the previous calendar
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth();

        const firstDayOfMonth = new Date(currentYear, currentMonth, 1);
        const lastDayOfMonth = new Date(currentYear, currentMonth + 1, 0);

        const firstDayOfWeek = firstDayOfMonth.getDay();  // Day of the week (0 = Sunday, 1 = Monday, etc.)
        const lastDate = lastDayOfMonth.getDate();

        monthName.textContent = currentDate.toLocaleDateString('default', { month: 'long', year: 'numeric' });

        // Padding days before the first day of the current month
        for (let i = 0; i < firstDayOfWeek; i++) {
            const paddingDay = document.createElement('div');
            paddingDay.classList.add('calendar-day', 'inactive');
            calendarDays.appendChild(paddingDay);
        }

        // Days of the current month
        for (let day = 1; day <= lastDate; day++) {
            const dayElement = document.createElement('div');
            dayElement.classList.add('calendar-day', 'active');
            dayElement.textContent = day;

            // Mark today's date
            const today = new Date();
            if (today.getDate() === day && today.getMonth() === currentMonth && today.getFullYear() === currentYear) {
                dayElement.classList.add('today');
            }

            calendarDays.appendChild(dayElement);
        }
    }

    prevMonthBtn.addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    nextMonthBtn.addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    addEventBtn.addEventListener('click', function() {
        alert('Add Event button clicked!');
    });

    renderCalendar();  // Initial render

    // Attendance Pie Chart
    const ctx = document.getElementById('attendancePieChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Present', 'Absent', 'Late'],
            datasets: [{
                label: 'Attendance',
                data: [60, 25, 15], // Example data, adjust as needed
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(255, 206, 86, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw + '%';
                        }
                    }
                }
            }
        }
    });

    // Missing Assignments
    const missingAssignments = [
        { assignment: 'Math Homework', dueDate: '2024-08-20', status: 'Not Submitted' },
        { assignment: 'Science Project', dueDate: '2024-08-25', status: 'Not Submitted' },
        { assignment: 'History Essay', dueDate: '2024-08-18', status: 'Submitted Late' },
        { assignment: 'English Literature Analysis', dueDate: '2024-08-22', status: 'Not Submitted' },
        { assignment: 'Biology Lab Report', dueDate: '2024-08-28', status: 'Not Submitted' },
        { assignment: 'Chemistry Worksheet', dueDate: '2024-08-30', status: 'Not Submitted' },
        { assignment: 'Art Sketch', dueDate: '2024-08-15', status: 'Submitted' },
        { assignment: 'Geography Presentation', dueDate: '2024-08-24', status: 'Not Submitted' },
        { assignment: 'Physical Education Log', dueDate: '2024-08-29', status: 'Submitted Late' },
        { assignment: 'Computer Science Project', dueDate: '2024-08-31', status: 'Not Submitted' }
    ];
    

    function populateMissingAssignmentsTable() {
        const tableBody = document.getElementById('missingAssignmentsTableBody');
        tableBody.innerHTML = ''; // Clear existing rows

        missingAssignments.forEach(item => {
            const row = document.createElement('tr');
            
            const assignmentCell = document.createElement('td');
            assignmentCell.textContent = item.assignment;
            row.appendChild(assignmentCell);
            
            const dueDateCell = document.createElement('td');
            dueDateCell.textContent = item.dueDate;
            row.appendChild(dueDateCell);
            
            const statusCell = document.createElement('td');
            statusCell.textContent = item.status;
            row.appendChild(statusCell);
            
            tableBody.appendChild(row);
        });
    }

    populateMissingAssignmentsTable();  // Populate the missing assignments table
});

//Student Profile

    const editProfileBtn = document.getElementById('editProfileBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const profileView = document.getElementById('profileView');
    const profileEdit = document.getElementById('profileEdit');

    // Show edit form
    editProfileBtn.addEventListener('click', function() {
        profileView.classList.add('d-none');
        profileEdit.classList.remove('d-none');
    });

    // Cancel edit form
    cancelEditBtn.addEventListener('click', function() {
        profileEdit.classList.add('d-none');
        profileView.classList.remove('d-none');
    });
