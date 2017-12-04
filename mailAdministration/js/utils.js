/**
 * @author Chris
 */

function getByID(id) {
    return document.getElementById(id);
}

function getByTag(tag) {
    return document.getElementsByTagName(tag);
}

function getElementsByClass(className) {
    return document.getElementsByClassName(className);
}

function filterInt(value) {
    if (/^(\-|\+)?([0-9]+|Infinity)$/.test(value)) {
        return Number(value);
    }
    return NaN;
}
function filterEmail(value) {
    if (/^([\w+-.%]+@[\w\-.]+\.[A-Za-z]{2,4},*[\W]*)+$/.test(value)) {
        return true;
    } else {
        return false;
    }
}
function filterFloat(value) {
    if (/^(\-|\+)?([0-9]+(\.[0-9]+)?|Infinity)$/.test(value))
        return Number(value);
    return NaN;
}

/**
 * Returns a random integer between min (inclusive) and max (inclusive)
 * Using Math.round() will give you a non-uniform distribution!
 */

function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

/**
 * Returns a random number between min (inclusive) and max (exclusive)
 */
function getRandomArbitrary(min, max) {
    return Math.random() * (max - min) + min;
}

var generatedNumbers = [];

function generateRandomNumber(digits) {// precision --> number precision in integer
    if (digits <= 20) {
        var adjustment = Math.pow(10, digits);
        var randomNum = Math.round(Math.random().toFixed(digits) * adjustment);
        if (generatedNumbers.indexOf(randomNum) > -1) {
            if (generatedNumbers.length == adjustment)
                return "Generated all values with this number of digits";
            return generateRandomNumber(digits);
        } else {
            generatedNumbers.push(randomNum);
            return randomNum;
        }
    } else
        return "Number of digits should not exceed 20!";
}


function submitSortForm(option, id) {
//    document.forms.Overview.sortType.value = option;
    getByID('sortType').value = option;
    getByID(id).submit();
}

function submitDelete(username, domain, formID) {
    getByID('deleteUsername').value = username;
    submitDomainDelete(domain, formID);
}

function submitDomainDelete(domain, formID) {
    getByID('deleteDomain').value = domain;
    getByID(formID).submit();
}

function onReady(fn) {
    if (document.readyState != 'loading') {
        fn();
    } else if (document.addEventListener) {
        document.addEventListener('DOMContentLoaded', fn);
    } else {
        document.attachEvent('onreadystatechange', function () {
            if (document.readyState != 'loading')
                fn();
        });
    }
}

function varDump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }
    console.log(out);
    //
    // alert(out);
    //
    // // or, if you wanted to avoid alerts...
    //
    // var pre = document.createElement('pre');
    // pre.innerHTML = out;
    // document.body.appendChild(pre);
}

function toggleClass(classToToggle, elementID) {
    $(elementID).toggleClass(classToToggle);
}