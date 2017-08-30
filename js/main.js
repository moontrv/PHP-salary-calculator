function AppViewModel() {
    var self = this;
        
    self.filesArray = ko.observableArray();
    self.getFilesAj = function () {
        self.filesArray.removeAll();
        $.ajax({
            type: 'POST',
            url: base_url + 'index.php/Welcome/getFilesAj',
            contentType: 'application/json; charset=utf-8'
        })
        .done(function (dataReturn) {
            self.filesArray(dataReturn);
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            alert("fail to execute: " + errorThrown);
        })
        .always(function (data) {
        });
    }
    self.getFilesAj();
    
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
            //console.log(dataReturn);
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
    self.deleteFile = function () {
        $.ajax({
            type: 'POST',
            url: base_url + 'index.php/Welcome/deleteFile',
            contentType: 'application/json; charset=utf-8',
            data: ko.toJSON({
                whole_data : this
            })
        })
        .done(function (dataReturn) {
            location.reload();
;           console.log(dataReturn);
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            alert("fail to execute: " + errorThrown);
        })
        .always(function (data) {
        });
    }    
    self.generateExcel = function () {
        //console.log(this);
        $.ajax({
            type: 'POST',
            url: base_url + 'index.php/Welcome/generateExcel',
            contentType: 'application/json; charset=utf-8',
            data: ko.toJSON({
                whole_data : this
            })
        })
        .done(function (dataReturn) {
            alert("Generation completed.Please check the downloads folder.");
            console.log(dataReturn);
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


