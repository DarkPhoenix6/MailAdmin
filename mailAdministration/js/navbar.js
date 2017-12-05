/**
 * @author Chris
 *
 * @notes requires 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'
 */

//require('https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js');
//require('https://code.jquery.com/jquery-2.1.3.min.js'); 
//require('js/jquery.slicknav.min.js');

/**
 *
 * @param {Object} n
 */
function setNavClasses(n) {
    var navList = document.getElementById("navbar");
    var navULLIA = navList.getElementsByTagName("a");
    for (i = 0; i < navULLIA.length; i++) {
        if (navULLIA[i].className == "current" && i != n) {
            navULLIA[i].className = "";
        } else if (i != n) {
            continue;
        } else {
            navULLIA[i].className = "current";
        }
    }
}

function adjustWidth() {
    var parentWidth = $(".parent").width();
    $(".child").width(parentWidth);

}

function getParentHeight() {
    var totalHeight = 0;

    $(".parent").children().each(function () {
        if ($(this).css('display') != 'none' && $(this).css('position') == 'fixed') {
            totalHeight = totalHeight + $(this).outerHeight(true);
        }
    });
    // $(".parent").height(totalHeight);
    return totalHeight;
}

function adjust_main_margin() {
    var parentHeight = getParentHeight();
    // var parentHeight = $(".parent").height();
    $(".main").css('margin-top', parentHeight);
}

function adjust_main_padding() {
    var parentHeight = getParentHeight();
    // var parentHeight = $(".parent").height();
    $(".main").css('padding-top', parentHeight);
}


$(window).resize(function () {
    adjustWidth();
    adjust_main_padding();
});
$(document).ready(function () {
    adjustWidth();
    adjust_main_padding();
    $('#navbar').slicknav({prependTo: "#mobile_menu"});
});

