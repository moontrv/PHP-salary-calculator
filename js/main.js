function AppViewModel(loginMenuViewModel) {
    var self = this;

    self.readExcel = function () {
        console.log(this);
        console.log($(this).text());
        $.ajax({
            type: 'POST',
            url: base_url + 'index.php/Welcome/readExcel/' + $(this).text(),
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


