function nextStep() {
    const currentStep = document.querySelector(".step.active");
    const nextStep = currentStep.nextElementSibling;
    const form = document.getElementById('registerForm');
    let valid = true;
    const errorMessages = {
        step1Error: '',
        step2Error: '',
        step3Error: ''
    };

    // Step 1 Validation
    if (currentStep.classList.contains('step-1')) {
        const fullName = form['full_name'].value;
        const email = form['email'].value;
        const phone = form['phone'].value;

        if (!fullName || !email || !phone) {
            errorMessages.step1Error = "All fields are required in Step 1.";
            valid = false;
        } else if (!validateEmail(email)) {
            errorMessages.step1Error = "Invalid email format.";
            valid = false;
        }
    }

    // Step 2 Validation
    if (currentStep.classList.contains('step-2')) {
        const studentId = form['student_id'].value;
        const level = form['level'].value;
        const program = form['program'].value;

        if (!studentId || !level || !program) {
            errorMessages.step2Error = "All fields are required in Step 2.";
            valid = false;
        }
    }

    // Step 3 Validation
    if (currentStep.classList.contains('step-3')) {
        const password = form['password'].value;
        const confirmPassword = form['confirm_password'].value;

        if (!password || !confirmPassword) {
            errorMessages.step3Error = "Password fields cannot be empty.";
            valid = false;
        } else if (password !== confirmPassword) {
            errorMessages.step3Error = "Passwords do not match.";
            valid = false;
        } else if (password.length < 8) {
            errorMessages.step3Error = "Password must be at least 8 characters.";
            valid = false;
        }
    }

    // Show error messages and proceed if valid
    if (valid) {
        currentStep.classList.remove("active");
        nextStep.classList.add("active");
    } else {
        document.getElementById('step1Error').innerText = errorMessages.step1Error;
        document.getElementById('step2Error').innerText = errorMessages.step2Error;
        document.getElementById('step3Error').innerText = errorMessages.step3Error;
    }
}

function prevStep() {
    const currentStep = document.querySelector(".step.active");
    const prevStep = currentStep.previousElementSibling;

    currentStep.classList.remove("active");
    prevStep.classList.add("active");
}

function validateEmail(email) {
    const re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return re.test(email);
}

function togglePassword() {
    const passwordField = document.getElementById('password');
    passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
}

function generatePassword() {
    const password = Math.random().toString(36).slice(-8); // Generates a random 8-character password
    document.getElementById('password').value = password;
}
