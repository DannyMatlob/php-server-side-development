function validateName(name) {
    if (name=="") {
        return "Name must not be empty.\n"
    }
    return ""
}

function validateID(id) {
    if (!/^\d{9}$/.test(id)) {
        return "ID must be 9 digits.\n"
    }
    return ""
}

function validateEmail(email) {
    let dot = email.indexOf(".")
    let at = email.indexOf("@")
    if (email=="") return "Email cannot be empty.\n"
    else if (!((at>0) && (dot > at)) || /[^a-zA-Z0-9.@_-]/.test(email)) {
        return "The Email address is invalid.\n"
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
