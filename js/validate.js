function validateForm(form) {
    return isEmpty(form.name.value) &&
        validateEmail(form.email.value) &&
        validatePhoneNumber(form.phone.value) &&
        isEmpty(form.institution.value);

}

function uppercaseCode(a) {
    a.value = a.value.toUpperCase();
}

function isEmpty(str) {
    if (str && str.replace(/\ /g, '') !== '')
        return true;
    else {
        document.getElementById("validation").style.display = "block";
        document.getElementById("validation").innerHTML = "* All fields must be filled in correctly";
        return false;
    }
}

function validatePhoneNumber(phone) {
    //remove unwanted symbols if there's any, then change the first number to indonesian country code by default if it's 0
    var simple = phone.replace('+', '');
    simple = simple.replace(/[\(\)\.\-\ ]/g, '');
    if (simple.charAt(0) === '0')
        simple = simple.replace('0', '62');

    var phoneno = /^\d{10}$|^\d{11}$|^\d{12}$|^\d{13}$/;

    if (simple.match(phoneno)) {
        document.getElementsByName('phone')[0].value = simple;
        return true;
    }
    else {
        document.getElementById("validation").style.display = "block";
        document.getElementById("validation").innerHTML = "* Invalid phone number format</br>valid example: 0812 3456 7890";
        return false;
    }
}

function validateEmail(email) {
    var mail = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

    if (email.match(mail)) {
        return true;
    }
    else {
        document.getElementById("validation").style.display = "block";
        document.getElementById("validation").innerHTML = "* Invalid email address</br>valid example: john.doe@domainname.com";
        return false;
    }
}  