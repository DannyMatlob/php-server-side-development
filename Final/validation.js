function validateUsername(name) {
    if (name=="") {
        return "Name must not be empty.\n"
    }
    return ""
}

function validatePassword(password) {
    if (password=="") return "Password cannot be empty.\n"
    if (password.length < 6) return "Password must be atleast 6 characters.\n"
    if (!/[a-z]/.test(password) || !/[A-Z]/.test(password)) {
        return "Password must include at least 1 uppercase and at least 1 lowercase.\n"
    }
    return "";
}
