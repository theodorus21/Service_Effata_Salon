const passwordInput = document.querySelector("#password");
const passwordToggle = document.querySelector(".password-toggle");

passwordToggle?.addEventListener("click", () => {
    const isPasswordHidden = passwordInput.type === "password";
    passwordInput.type = isPasswordHidden ? "text" : "password";
    passwordToggle.setAttribute("aria-pressed", String(isPasswordHidden));
    passwordToggle.setAttribute(
        "aria-label",
        isPasswordHidden ? "Sembunyikan kata sandi" : "Tampilkan kata sandi",
    );
});
