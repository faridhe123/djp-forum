$(document).ready(function () {

    $(".action-logout.djpconnect").click(function () {
        
        var x = window.open('http://10.254.208.134:8081/logout');
        x.close();

    });


});

