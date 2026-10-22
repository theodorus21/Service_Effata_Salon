const signupForm = document.querySelector("#signup-form");
const passwordInput = document.querySelector("#signup-password");
const confirmationInput = document.querySelector("#password-confirmation");
const signupMessage = document.querySelector("#signup-message");
const submitButton = signupForm.querySelector('button[type="submit"]');
const usersEndpoint = document.querySelector('meta[name="api-users-endpoint"]').content;

const validatePasswordConfirmation = () => {
    if (confirmationInput.value && confirmationInput.value !== passwordInput.value) {
        confirmationInput.setCustomValidity("Konfirmasi kata sandi belum cocok.");
        return;
    }

    confirmationInput.setCustomValidity("");
};

passwordInput.addEventListener("input", validatePasswordConfirmation);
confirmationInput.addEventListener("input", validatePasswordConfirmation);

signupForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    validatePasswordConfirmation();

    if (!signupForm.reportValidity()) {
        return;
    }

    signupMessage.textContent = "Sedang membuat akun...";
    signupMessage.dataset.state = "loading";
    submitButton.disabled = true;

    const formData = new FormData(signupForm);
    const user = {
        name: formData.get("name").trim(),
        email: formData.get("email").trim(),
        password: formData.get("password"),
        role: "user",
    };

    try {
        const response = await fetch(usersEndpoint, {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify(user),
        });
        const contentType = response.headers.get("content-type") || "";
        let result = null;

        if (contentType.includes("application/json")) {
            try {
                result = await response.json();
            } catch {
                signupMessage.textContent = "Respons dari server tidak dapat dibaca. Silakan coba lagi.";
                signupMessage.dataset.state = "error";
                return;
            }
        }

        if (!response.ok) {
            const validationMessage = result?.errors
                ? Object.values(result.errors).flat().find((message) => typeof message === "string")
                : null;
            signupMessage.textContent =
                validationMessage ||
                result?.message ||
                `Pendaftaran gagal (HTTP ${response.status}). Silakan coba lagi.`;
            signupMessage.dataset.state = "error";
            return;
        }

        if (!result || typeof result.id === "undefined") {
            signupMessage.textContent = "Server menerima permintaan, tetapi data akun tidak dapat dikonfirmasi.";
            signupMessage.dataset.state = "error";
            return;
        }

        signupForm.reset();
        signupMessage.textContent = "Akun berhasil dibuat. Silakan masuk menggunakan akun baru.";
        signupMessage.dataset.state = "success";
    } catch {
        signupMessage.textContent =
            "Tidak dapat terhubung ke server. Periksa koneksi dan pastikan backend sedang berjalan.";
        signupMessage.dataset.state = "error";
    } finally {
        submitButton.disabled = false;
    }
});
