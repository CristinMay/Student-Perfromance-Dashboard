
//Attendance

document.addEventListener('DOMContentLoaded', function() {
    var editButton = document.getElementById('editButton');
    var saveButton = document.getElementById('saveButton');
    var formControls = document.querySelectorAll('.form-control');

    function setDisplayMode() {
        formControls.forEach(function(input) {
            input.disabled = true;
        });
        document.getElementById('attendanceCard').classList.remove('border', 'border-primary');
        saveButton.classList.add('d-none');
        editButton.classList.remove('d-none');
    }

    function setEditMode() {
        formControls.forEach(function(input) {
            input.disabled = false;
        });
        document.getElementById('attendanceCard').classList.add('border', 'border-primary');
        saveButton.classList.remove('d-none');
        editButton.classList.add('d-none');
    }

    editButton.addEventListener('click', function() {
        setEditMode();
    });

    saveButton.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent form submission
        setDisplayMode();
    });

    // Initialize form controls as disabled
    setDisplayMode();
});

//Student_Master_List

document.addEventListener('DOMContentLoaded', function () {
    const sectionSelect = document.getElementById('sectionSelect');
    const studentList = document.getElementById('studentList');

    const students = {
        'A1': [
            { id: '12345', lastName: 'Joson', firstName: 'Jodeo', section: 'A1', email: 'jodeo.joson@example.com' },
            { id: '67890', lastName: 'Luis', firstName: 'Ira', section: 'A1', email: 'ira.luis@example.com' },
            { id: '10001', lastName: 'Uzumaki', firstName: 'Naruto', section: 'A1', email: 'naruto.uzumaki@konoha.com' },
            { id: '10002', lastName: 'Haruno', firstName: 'Sakura', section: 'A1', email: 'sakura.haruno@konoha.com' },
            { id: '10003', lastName: 'Uchiha', firstName: 'Sasuke', section: 'A1', email: 'sasuke.uchiha@konoha.com' },
            { id: '10004', lastName: 'Hatake', firstName: 'Kakashi', section: 'A1', email: 'kakashi.hatake@konoha.com' },
            { id: '10005', lastName: 'Monkey D.', firstName: 'Luffy', section: 'A1', email: 'luffy.mugiwara@onepiece.com' },
            { id: '10006', lastName: 'Roronoa', firstName: 'Zoro', section: 'A1', email: 'zoro.swordsman@onepiece.com' },
            { id: '10007', lastName: 'Vinsmoke', firstName: 'Sanji', section: 'A1', email: 'sanji.chef@onepiece.com' },
            { id: '10008', lastName: 'Nami', firstName: '', section: 'A1', email: 'nami.navigator@onepiece.com' },
            { id: '10009', lastName: 'Tony Tony', firstName: 'Chopper', section: 'A1', email: 'chopper.doctor@onepiece.com' },
            { id: '10010', lastName: 'Nico', firstName: 'Robin', section: 'A1', email: 'robin.archaeologist@onepiece.com' },
            { id: '10011', lastName: 'Hyuuga', firstName: 'Hinata', section: 'A1', email: 'hinata.hyuga@konoha.com' },
            { id: '10012', lastName: 'Temari', firstName: 'Temari', section: 'A1', email: 'temari.suna@konoha.com' }
        ],
        'B1': [
            { id: '20001', lastName: 'Luffy', firstName: 'Monkey D.', section: 'B1', email: 'luffy.mugiwara@onepiece.com' },
            { id: '20002', lastName: 'Zoro', firstName: 'Roronoa', section: 'B1', email: 'zoro.swordsman@onepiece.com' },
            { id: '20003', lastName: 'Sanji', firstName: 'Vinsmoke', section: 'B1', email: 'sanji.chef@onepiece.com' },
            { id: '20004', lastName: 'Nami', firstName: 'Nami', section: 'B1', email: 'nami.navigator@onepiece.com' },
            { id: '20005', lastName: 'Chopper', firstName: 'Tony Tony', section: 'B1', email: 'chopper.doctor@onepiece.com' },
            { id: '20006', lastName: 'Robin', firstName: 'Nico', section: 'B1', email: 'robin.archaeologist@onepiece.com' },
            { id: '20007', lastName: 'Sakura', firstName: 'Haruno', section: 'B1', email: 'sakura.haruno@konoha.com' },
            { id: '20008', lastName: 'Sasuke', firstName: 'Uchiha', section: 'B1', email: 'sasuke.uchiha@konoha.com' },
            { id: '20009', lastName: 'Kakashi', firstName: 'Hatake', section: 'B1', email: 'kakashi.hatake@konoha.com' },
            { id: '20010', lastName: 'Ino', firstName: 'Yamanaka', section: 'B1', email: 'ino.yamanaka@konoha.com' },
            { id: '20011', lastName: 'Shikamaru', firstName: 'Nara', section: 'B1', email: 'shikamaru.nara@konoha.com' },
            { id: '20012', lastName: 'Choji', firstName: 'Akimichi', section: 'B1', email: 'choji.akimichi@konoha.com' }
        ]
    };

    function renderStudents(section) {
        studentList.innerHTML = '';
        const selectedStudents = students[section];
        selectedStudents.forEach(student => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${student.id}</td>
                <td>${student.lastName}</td>
                <td>${student.firstName}</td>
                <td>${student.section}</td>
                <td>${student.email}</td>
            `;
            studentList.appendChild(row);
        });
    }

    sectionSelect.addEventListener('change', function () {
        renderStudents(this.value);
    });

    // Initial render
    renderStudents('A1');
});


//class_Record.php

document.addEventListener('DOMContentLoaded', function() {
    const editButton = document.getElementById('edit-toggle-btn');
    const gradeSectionSelect = document.getElementById('grade-section-select');
    const assessmentSelect = document.getElementById('assessment-select');
    const tableBody = document.querySelector('#grading-system-table tbody');

    // Define student data for each grade and section
    const students = {
        'grade9-sectionA': [
            { firstName: 'Naruto', lastName: 'Uzumaki' },
            { firstName: 'Sasuke', lastName: 'Uchiha' },
            { firstName: 'Sakura', lastName: 'Haruno' },
            { firstName: 'Kakashi', lastName: 'Hatake' },
            { firstName: 'Hinata', lastName: 'Hyuga' },
            { firstName: 'Shikamaru', lastName: 'Nara' },
            { firstName: 'Ino', lastName: 'Yamanaka' },
            { firstName: 'Choji', lastName: 'Akimichi' },
            { firstName: 'Kiba', lastName: 'Inuzuka' },
            { firstName: 'Shino', lastName: 'Aburame' }
        ],
        'grade9-sectionB': [
            { firstName: 'Goku', lastName: 'Son' },
            { firstName: 'Vegeta', lastName: 'Prince' },
            { firstName: 'Gohan', lastName: 'Son' },
            { firstName: 'Piccolo', lastName: 'Daimo' },
            { firstName: 'Krillin', lastName: 'Krilin' },
            { firstName: 'Bulma', lastName: 'Briefs' },
            { firstName: 'Trunks', lastName: 'Briefs' },
            { firstName: 'Frieza', lastName: 'Emperor' },
            { firstName: 'Cell', lastName: 'Android' },
            { firstName: 'Majin Buu', lastName: 'Evil' }
        ],
        'grade10-sectionA': [
            { firstName: 'Monkey D.', lastName: 'Luffy' },
            { firstName: 'Roronoa', lastName: 'Zoro' },
            { firstName: 'Nami', lastName: '' },
            { firstName: 'Sanji', lastName: '' },
            { firstName: 'Usopp', lastName: '' },
            { firstName: 'Tony Tony', lastName: 'Chopper' },
            { firstName: 'Nico', lastName: 'Robin' },
            { firstName: 'Franky', lastName: '' },
            { firstName: 'Brook', lastName: '' },
            { firstName: 'Jinbe', lastName: '' }
        ],
        'grade10-sectionB': [
            { firstName: 'Edward', lastName: 'Elric' },
            { firstName: 'Alphonse', lastName: 'Elric' },
            { firstName: 'Roy', lastName: 'Mustang' },
            { firstName: 'Winry', lastName: 'Rockbell' },
            { firstName: 'Riza', lastName: 'Hawkeye' },
            { firstName: 'Hohenheim', lastName: '' },
            { firstName: 'Greed', lastName: '' },
            { firstName: 'Ling', lastName: 'Yao' },
            { firstName: 'Envy', lastName: '' },
            { firstName: 'Scar', lastName: '' }
        ]
    };

    function updateTable() {
        const gradeSectionValue = gradeSectionSelect.value;
        const studentData = students[gradeSectionValue] || [];

        // Build rows for student data
        let rows = '';

        studentData.forEach((student, index) => {
            rows += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${student.lastName}</td>
                    <td>${student.firstName}</td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="false"></td>
                    <td contenteditable="true"></td>
                    </tr>
            `;
        });

        // Replace the current table body content with new rows
        tableBody.innerHTML = rows;
    }

    function toggleEditMode() {
        const isEditing = editButton.textContent === 'Save';

        if (isEditing) {
            editButton.textContent = 'Edit Score';
            document.querySelectorAll('td[contenteditable]').forEach(cell => {
                cell.style.backgroundColor = ''; // Reset color
                cell.contentEditable = 'false'; // Disable editing
            });
        } else {
            editButton.textContent = 'Save';
            document.querySelectorAll('td[contenteditable]').forEach(cell => {
                cell.style.backgroundColor = '#e9ecef'; // Change color to indicate edit mode
                cell.contentEditable = 'true'; // Enable editing
            });
        }
    }

    // Add event listeners
    editButton.addEventListener('click', toggleEditMode);
    gradeSectionSelect.addEventListener('change', updateTable);
    assessmentSelect.addEventListener('change', updateTable);

    // Initialize the table display on page load
    updateTable();
    // Ensure the table starts in display mode
    editButton.textContent = 'Edit Score'; // Ensure button text is correct on load
});

//Dashboard.php

