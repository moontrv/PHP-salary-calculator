var ClickCounterViewModel = function() {
    var self = this;
    self.calculateSalary = function(){
        alert("dadfa");
        $.ajax( "example.php" )
        .done(function() {
            alert( "success" );
        })
        .fail(function() {
            alert( "error" );
        })
        .always(function() {
            alert( "complete" );
        });
    };
};
 
$( document ).ready(function() {
    ko.applyBindings(new ClickCounterViewModel());
});