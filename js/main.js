function AppViewModel(loginMenuViewModel) {
    var self = this;

    self.readExcel = function (passString) {
        $.ajax({
            type: 'POST',
            url: base_url + 'index.php/Welcome/readExcel/' + passString,
            contentType: 'application/json; charset=utf-8',
            //data: ko.toJSON({
            //    whole_data : $(this).value
            //})
        })
        .done(function (dataReturn) {
            console.log(dataReturn);
            /*if (data == true) {
                self.checkIfUserCanIvestbtn(true);
            } else {
                window.location.href = BASEURL + 'index.php/';
            }*/
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            alert("fail to execute: " + errorThrown);
        })
        .always(function (data) {
        });
    }
}

$(document).ready(function () {
    $.ajaxSetup({cache: false});
    var myViewModel = new AppViewModel();
    ko.applyBindings(myViewModel, document.getElementById('hung_wrapper'));
});


