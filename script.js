document.addEventListener('DOMContentLoaded', function() {
    console.log('Municipality Incident Reporting Web-App Loaded');
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    
    switch(currentPage) {
        case 'index.html':
        case '':
            initLoginPage();
            break;
        case 'register.html':
            initRegisterPage();
            break;
        case 'dashboard.html':
            initDashboard();
            break;
        case 'submit-report.html':
            initSubmitReport();
            break;
        case 'hotlines.html':
            initHotlines();
            break;
        case 'heatmap.html':
            initHeatmap();
            break;
    }
    initNavigation();
});

function initLoginPage() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
}

function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const remember = document.getElementById('remember').checked;

    if (!email || !password) {
        alert('Please fill in all fields');
        return;
    }

    console.log('Login attempt:', { email, remember });

    if (remember) {
        localStorage.setItem('userEmail', email);
    }
    sessionStorage.setItem('isLoggedIn', 'true');
    window.location.href = 'dashboard.html';
}

function initRegisterPage() {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
    
    const password = document.getElementById('regPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    
    if (confirmPassword) {
        confirmPassword.addEventListener('blur', validatePasswordMatch);
    }
}

function handleRegister(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const userData = Object.fromEntries(formData.entries());
    
    const requiredFields = ['fullName', 'emailAddress', 'contactNumber', 'barangay', 'sex', 'age', 'regPassword', 'confirmPassword'];
    
    for (let field of requiredFields) {
        if (!userData[field]) {
            alert(`Please fill in the ${field.replace(/([A-Z])/g, ' $1').toLowerCase()}`);
            return;
        }
    }
    
    if (userData.regPassword !== userData.confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(userData.emailAddress)) {
        alert('Please enter a valid email address');
        return;
    }
    
    console.log('Registration data:', userData);
    
    localStorage.setItem('userData', JSON.stringify(userData));
    
    alert('Registration successful! Please login with your credentials.');
    window.location.href = 'index.html';
}

function validatePasswordMatch() {
    const password = document.getElementById('regPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (password && confirmPassword && password !== confirmPassword) {
        document.getElementById('confirmPassword').style.borderColor = '#dc3545';
        return false;
    } else {
        document.getElementById('confirmPassword').style.borderColor = '#ddd';
        return true;
    }
}

function initDashboard() {
    if (!sessionStorage.getItem('isLoggedIn')) {
        window.location.href = 'index.html';
        return;
    }
    
    initCharts();
    initDashboardInteractions();
}

function initCharts() {
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Incidents',
                    data: [65, 75, 85, 95, 110, 125, 140, 155, 145, 160, 175, 190],
                    borderColor: '#4a7c59',
                    backgroundColor: 'rgba(74, 124, 89, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    const barangayCtx = document.getElementById('barangayChart');
    if (barangayCtx) {
        new Chart(barangayCtx, {
            type: 'bar',
            data: {
                labels: ['Brgy. Poblacion', 'Brgy. Mabolo', 'Brgy. Cagam', 'Brgy. San Jose', 'Brgy. Silot'],
                datasets: [{
                    label: 'Incidents',
                    data: [120, 95, 80, 70, 60],
                    backgroundColor: '#4a7c59'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    const pieCtx = document.getElementById('pieChart');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Road Hazard', 'Public Disturbance', 'Health Emergency', 'Environmental', 'Infrastructure', 'Others'],
                datasets: [{
                    data: [250, 180, 120, 95, 70, 50],
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#fd7e14', '#6f42c1']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
}

function initDashboardInteractions() {
    const generateBtn = document.querySelector('.generate-report-btn');
    if (generateBtn) {
        generateBtn.addEventListener('click', generateReport);
    }
    
    const filterSelects = document.querySelectorAll('.filter-select, .period-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', handleFilterChange);
    });
}

function generateReport() {
    alert('Generating report... This feature will be implemented with backend integration.');
}

function handleFilterChange(e) {
    console.log('Filter changed:', e.target.value);
}

function initSubmitReport() {
    const incidentForm = document.getElementById('incidentForm');
    if (incidentForm) {
        incidentForm.addEventListener('submit', handleIncidentSubmit);
    }
    
    const clearBtn = document.querySelector('.clear-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearIncidentForm);
    }
    
    const fileUpload = document.getElementById('fileUpload');
    if (fileUpload) {
        fileUpload.addEventListener('change', handleFileUpload);
    }
    
    const actionBtns = document.querySelectorAll('.action-btn');
    actionBtns.forEach(btn => {
        btn.addEventListener('click', viewIncidentDetails);
    });
}

function handleIncidentSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const incidentData = Object.fromEntries(formData.entries());
    
    if (!incidentData.incidentType || !incidentData.incidentDescription || !incidentData.incidentLocation) {
        alert('Please fill in all required fields');
        return;
    }
    
    const incidentId = '#RAIN' + String(Math.floor(Math.random() * 1000)).padStart(3, '0');
    
    incidentData.submittedAt = new Date().toISOString().split('T')[0];
    incidentData.id = incidentId;
    incidentData.status = 'pending';
    
    console.log('Incident submitted:', incidentData);
    
    let incidents = JSON.parse(localStorage.getItem('userIncidents') || '[]');
    incidents.unshift(incidentData);
    localStorage.setItem('userIncidents', JSON.stringify(incidents));
    
    alert(`Incident report submitted successfully! Your incident ID is: ${incidentId}`);
    
    clearIncidentForm();
    
    window.location.reload();
}

function clearIncidentForm() {
    const form = document.getElementById('incidentForm');
    if (form) {
        form.reset();
        const uploadText = document.querySelector('.upload-subtext');
        if (uploadText) {
            uploadText.textContent = 'No files chosen';
        }
    }
}

function handleFileUpload(e) {
    const files = e.target.files;
    const uploadText = document.querySelector('.upload-subtext');
    
    if (files.length > 0) {
        const fileNames = Array.from(files).map(file => file.name).join(', ');
        uploadText.textContent = `${files.length} file(s) selected: ${fileNames}`;
    } else {
        uploadText.textContent = 'No files chosen';
    }
}

function viewIncidentDetails(e) {
    const row = e.target.closest('tr');
    const incidentId = row.cells[0].textContent;
    
    alert(`Viewing details for incident ${incidentId}. This feature will be implemented with detailed modal views.`);
}

function initHotlines() {
    const searchInput = document.getElementById('hotlineSearch');
    if (searchInput) {
        searchInput.addEventListener('input', handleHotlineSearch);
    }
    
    const viewDetailsBtns = document.querySelectorAll('.view-details-btn');
    viewDetailsBtns.forEach(btn => {
        btn.addEventListener('click', viewAgencyDetails);
    });
}

function handleHotlineSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const tableRows = document.querySelectorAll('.contacts-table tbody tr');
    
    tableRows.forEach(row => {
        const agency = row.cells[1].textContent.toLowerCase();
        const description = row.cells[2].textContent.toLowerCase();
        
        if (agency.includes(searchTerm) || description.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function viewAgencyDetails(e) {
    const row = e.target.closest('tr');
    const agency = row.cells[1].textContent;
    const description = row.cells[2].textContent;
    const phone = row.cells[3].textContent;
    const landline = row.cells[4].textContent;
    
    alert(`Agency: ${agency}\nDescription: ${description}\nPhone: ${phone}\nLandline: ${landline}`);
}

function initHeatmap() {
    const filterInputs = document.querySelectorAll('#incidentTypeFilter, #startDate, #endDate, #barangaySearch');
    filterInputs.forEach(input => {
        input.addEventListener('change', handleHeatmapFilter);
        input.addEventListener('input', handleHeatmapFilter);
    });
    
    const clearFiltersBtn = document.querySelector('.clear-filters-btn');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearHeatmapFilters);
    }
    
    const markers = document.querySelectorAll('.barangay-marker');
    markers.forEach(marker => {
        marker.addEventListener('click', showBarangayDetails);
    });
    
    initModal();
}

function handleHeatmapFilter() {
    const incidentType = document.getElementById('incidentTypeFilter').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const searchTerm = document.getElementById('barangaySearch').value.toLowerCase();
    
    const markers = document.querySelectorAll('.barangay-marker');
    
    markers.forEach(marker => {
        const barangayName = marker.dataset.barangay.toLowerCase();
        
        if (searchTerm && !barangayName.includes(searchTerm)) {
            marker.style.display = 'none';
        } else {
            marker.style.display = 'block';
        }
    });
    
    console.log('Filters applied:', { incidentType, startDate, endDate, searchTerm });
}

function clearHeatmapFilters() {
    document.getElementById('incidentTypeFilter').value = 'all';
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';
    document.getElementById('barangaySearch').value = '';
    
    const markers = document.querySelectorAll('.barangay-marker');
    markers.forEach(marker => {
        marker.style.display = 'block';
    });
}

function showBarangayDetails(e) {
    const marker = e.currentTarget;
    const barangay = marker.dataset.barangay;
    const incidents = marker.dataset.incidents;
    
    const modal = document.getElementById('incidentModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.textContent = `${barangay} - Incident Details`;
    modalBody.innerHTML = `
        <p><strong>Barangay:</strong> ${barangay}</p>
        <p><strong>Total Incidents:</strong> ${incidents}</p>
        <p><strong>Risk Level:</strong> ${marker.classList.contains('high') ? 'High' : marker.classList.contains('moderate') ? 'Moderate' : 'Low'}</p>
        <hr>
        <h4>Recent Incidents:</h4>
        <ul>
            <li>Road hazard reported on Main Street</li>
            <li>Public disturbance near market area</li>
            <li>Infrastructure damage at bridge approach</li>
        </ul>
    `;
    
    modal.style.display = 'block';
}

function initModal() {
    const modal = document.getElementById('incidentModal');
    const closeBtn = document.querySelector('.close');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
}

function initNavigation() {
    const logoutBtns = document.querySelectorAll('.logout-btn');
    logoutBtns.forEach(btn => {
        btn.addEventListener('click', handleLogout);
    });
    
    const menuToggle = document.querySelector('.menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', toggleMobileMenu);
    }
}

function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        sessionStorage.removeItem('isLoggedIn');
        localStorage.removeItem('userEmail');
        window.location.href = 'index.html';
    }
}

function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function generateId(prefix = 'ID') {
    return prefix + Date.now().toString(36) + Math.random().toString(36).substr(2);
}

window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function(err) {
                console.log('ServiceWorker registration failed');
            });
    });
}
