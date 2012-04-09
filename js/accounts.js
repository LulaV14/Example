
// WHEN DOM IS READY...
$(function() {

    $("#delete-account-submit").click(function(){
    
        var answer = confirm("Are you absolutely sure? Deleting your account cannot be undone.");
        
    	if (answer){
    		// proceed to account deletion, default behavior
    	}
    	else{
    		return false;
    	}
    
    });

});
