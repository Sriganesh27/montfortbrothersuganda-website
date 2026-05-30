// erp/assets/scripts/login.js

document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.querySelector('select[name="role"]');
    const branchGroup = document.getElementById('branchGroup');
    const branchSelect = document.querySelector('select[name="branch_id"]');
    const loginForm = document.getElementById('loginForm');
    const messageDiv = document.getElementById('message');


    // --- 2. Login Submission Logic ---
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        // Reset visibility
        messageDiv.style.display = 'none';

        fetch('/MBU_Website/Website/web/web/erp/api/login_process', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            messageDiv.style.display = 'block'; // Make it visible
            if (data.status === 'success') {
                messageDiv.style.color = '#155724';
                messageDiv.style.backgroundColor = '#d4edda';
                messageDiv.textContent = "Redirecting...";
                window.location.href = data.redirect;
            } else {
                messageDiv.style.color = '#721c24';
                messageDiv.style.backgroundColor = '#f8d7da';
                messageDiv.textContent = data.message;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.style.display = 'block';
            messageDiv.style.color = '#721c24';
            messageDiv.style.backgroundColor = '#f8d7da';
            messageDiv.textContent = "Error connecting to server. Check your database settings.";
        });
    });
});