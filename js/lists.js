// PLUGIN TO CALL TO CYCLE COLOR
jQuery.fn.nextColor = function() {

    // This would be subject to breakage if ever added multiple classes...
    var curColor = $(this).attr("class");

    if (curColor == "colorBlue") {
        $(this).removeClass("colorBlue").addClass("colorYellow").attr("color","2");
    } else if (curColor == "colorYellow") {
        $(this).removeClass("colorYellow").addClass("colorRed").attr("color","3");
    } else if (curColor == "colorRed") {
        $(this).removeClass("colorRed").addClass("colorGreen").attr("color","4");
    } else {
        $(this).removeClass("colorGreen").addClass("colorBlue").attr("color","1");
    };
};

function saveListOrder(itemID, itemREL){
	var i = 1,
		currentListID = $('#current-list').val();
	$('#list li').each(function() {
		if($(this).attr('id') == itemID) {
			var startPos = itemREL,
				currentPos = i;
			if(startPos < currentPos) {
				var direction = 'down';
			} else {
				var direction = 'up';
			}
			var token = $('#token').val(),
				postURL = "action=sort&currentListID="+currentListID
					+"&startPos="+startPos
					+"&currentPos="+currentPos
					+"&direction="+direction
					+ "&token=" + token;

			$.ajax({
				type: "POST",
				url: "db-interaction/lists.php",
				data: postURL,
				success: function(msg) {
	        		// Resets the rel attribute to reflect current positions
					var count=1;
	        		$('#list li').each(function() {
	        			$(this).attr('rel', count);
	        			count++;
	        		});
	        	},
	        	error: function(msg) {
	        	    // (chris): I commented this out for now, was throwing sometimes...
	        		//alert(msg);
	        	}
			});
	    }
		i++;
	});
}

// This is seperated to a function so that it can be called at page load
// as well as when new list items are appended via AJAX
function bindAllTabs(editableTarget) {
    
    // CLICK-TO-EDIT on list items
	var token = $('#token').val();
    $(editableTarget).editable("db-interaction/lists.php", {
        id        : 'listItemID',
        indicator : 'Saving...',
        tooltip   : 'Double-click to edit...',
        event     : 'dblclick',
        submit    : 'Save',
        submitdata: {action : "update", "token" : token}
    });
    
}

// WHEN DOM IS READY...
function initialize() {

    // WRAP LIST TEXT IN A SPAN, AND APPLY FUNCTIONALITY TABS
    $("#list li")
    	.wrapInner("<span>")
        .append("<div class='draggertab tab'></div><div class='colortab tab'></div><div class='deletetab tab'></div><div class='donetab tab'></div>")
        .each(function(){
	    	if ($(this).find("img.crossout").length)
	    	{
	    		$(this).find("span").css({
	    			opacity : .5
	    		})
	    	}
	    });

    bindAllTabs("#list li span");

	// MAKE THE LIST SORTABLE VIA JQUERY UI
	// calls the SaveListOrder function after a change
	// waits for one second first, for the DOM to set, otherwise it's too fast.
    $("#list").sortable({
    	handle   : ".draggertab",
    	update   : function(event, ui){
    		var id = ui.item.attr('id'),
    			rel = ui.item.attr('rel'),
    			t = setTimeout("saveListOrder('"+id+"', '"+rel+"')",500);
    	},
    	forcePlaceholderSize: true
    });

    // THE SPANS NEED ID's for the CLICK-TO-EDIT
    // "listitem" is appended to avoid conflicting ID's and stripped by PHP
    $('#list li span').each(function(){
        $(this).attr("id", $(this).parent().attr("id") + "listitem");
    });

    // AJAX style adding of list items
    $('#add-new').submit(function(){
    
        // HTML tag whitelist. All other tags are stripped.
    	var $whitelist = '<b><i><strong><em><a>',
    		forList = $("#current-list").val();
    		newListItemText = strip_tags(cleanHREF($("#new-list-item-text").val()), $whitelist),
    		URLtext = escape(newListItemText),
    		newListItemRel = $('#list li').size()+1,
    		token = $('#token').val();
        
        if (newListItemText.length > 0) {
        
            // prevent multiple submissions by disabling button until save is successful
            $("#add-new-submit").attr("disabled", true);
        
            $.ajax({
    			type: "POST",
    			url: "db-interaction/lists.php",
    			data: "action=add&list=" + forList
    				+ "&text=" + URLtext
    				+ "&pos=" + newListItemRel
    				+ "&token=" + token,
    			success: function(theResponse){
                  $("#list").append("<li color='1' class='colorBlue' rel='"+newListItemRel+"' id='" + theResponse + "'><span id=\""+theResponse+"listitem\" title='Click to edit...'>" + newListItemText + "</span><div class='draggertab tab'></div><div class='colortab tab'></div><div class='deletetab tab'></div><div class='donetab tab'></div></li>");
                  bindAllTabs("#list li[rel='"+newListItemRel+"'] span");
                  $("#new-list-item-text").val("");
                  $("#add-new-submit").removeAttr("disabled");
    			},
    			error: function(){
    			    // should be some error functionality here
    			}
    		});
        } else {
        	$("#new-list-item-text").val("");
        }
        return false; // prevent default form submission
    });
    
    $(".donetab").live("click", function() {
        var id = $(this).parent().attr('id');
    	if(!$(this).siblings('span').children('img.crossout').length)
    	{
        	$(this)
        	    .parent()
                    .find("span")
                    .append("<img src='images/crossout.png' class='crossout' />")
                    .find(".crossout")
                    .animate({
                        width: "100%"
                    })
                    .end()
                .animate({
                    opacity: "0.5"
                },
    			"slow",
    			"swing",
    			toggleDone(id, 1));
    	}
        else
        {
        	$(this)
        		.siblings('span')
        			.find('img.crossout')
        				.remove()
        				.end()
    				.animate({
    					opacity : 1
    				},
    				"slow",
    				"swing",
    				toggleDone(id, 0));
    			
        }
    });

    function toggleDone(id, isDone)
    {
    	var token = $('#token').val();
    	$.ajax({
    		type: "POST",
    		url: "db-interaction/lists.php",
    		data: "action=done&id="+id
    			+ "&done=" + isDone
				+ "&token=" + token
    	})
    }

    // COLOR CYCLING
    // Does AJAX save, but no visual feedback
    $(".colortab").live("click", function(){
        $(this).parent().nextColor();
        
        var id = $(this).parent().attr("id"),
        	color = $(this).parent().attr("color"),
        	token = $('#token').val();
    
        $.ajax({
    		type: "POST",
    		url: "db-interaction/lists.php",
    		data: "action=color&id=" + id
    			+ "&color=" + color
				+ "&token=" + token,
    		success: function(msg) {
        		//alert(msg); // Debugging
        	}
    	});
    });
    
    // AJAX style deletion of list items
    $(".deletetab").live("click", function(){
        var thiscache = $(this),
        	list = $('#current-list').val(),
        	id = thiscache.parent().attr("id"),
        	pos = thiscache.parents('li').attr('rel'),
        	token = $('#token').val();
    			
        if (thiscache.data("readyToDelete") == "go for it") {
        	$.ajax({
    			type: "POST",
    			url: "db-interaction/lists.php",
    			data: {
    					"list":list,
    					"id":id,
    					"action":"delete",
    					"pos":pos,
    					"token":token
    				},
    			success: function(r){
    					var $li = $('#list').children('li'),
    						position = 0;
    	            	thiscache
    	            		.parent()
    	            			.hide("explode", 400, function(){$(this).remove()});
    	            	$('#list')
            				.children('li')
            					.not(thiscache.parent())
            					.each(function(){
    				            		$(this).attr('rel', ++position);
    				            	});
    				},
    			error: function() {
    			    $("#main").prepend("Deleting the item failed...");
    			}
    		});
        }
        else
    	{
        	thiscache.animate({
        		width: "44px",
        		right: "-64px"
        	}, 200)
        	.data("readyToDelete", "go for it");
    	}
    });
}


// Check for JS in the href attribute
function cleanHREF(str)
{
	return str.replace(/\<a(.*?)href=['"](javascript:)(.+?)<\/a>/gi, "Naughty!");
}

// Strip HTML tags with a whitelist
function strip_tags(str, allowed_tags) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Luke Godfrey
    // +      input by: Pul
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +      input by: Alex
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Marc Palau
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Brett Zamir (http://brettz9.blogspot.com)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Eric Nagel
    // +      input by: Bobby Drake
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: strip_tags('<p>Kevin</p> <br /><b>van</b> <i>Zonneveld</i>', '<i><b>');
    // *     returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
    // *     example 2: strip_tags('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');
    // *     returns 2: '<p>Kevin van Zonneveld</p>'
    // *     example 3: strip_tags("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
    // *     returns 3: '<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>'
    // *     example 4: strip_tags('1 < 5 5 > 1');
    // *     returns 4: '1 < 5 5 > 1'
 
    var key = '', allowed = false;
    var matches = [];
    var allowed_array = [];
    var allowed_tag = '';
    var i = 0;
    var k = '';
    var html = '';
 
    var replacer = function(search, replace, str) {
        return str.split(search).join(replace);
    };
 
    // Build allowes tags associative array
    if (allowed_tags) {
        allowed_array = allowed_tags.match(/([a-zA-Z]+)/gi);
    }
  
    str += '';
 
    // Match tags
    matches = str.match(/(<\/?[\S][^>]*>)/gi);
 
    // Go through all HTML tags
    for (key in matches) {
        if (isNaN(key)) {
            // IE7 Hack
            continue;
        }
 
        // Save HTML tag
        html = matches[key].toString();
 
        // Is tag not in allowed list? Remove from str!
        allowed = false;
 
        // Go through all allowed tags
        for (k in allowed_array) {
            // Init
            allowed_tag = allowed_array[k];
            i = -1;
 
            if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+'>');}
            if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+' ');}
            if (i != 0) { i = html.toLowerCase().indexOf('</'+allowed_tag)   ;}
 
            // Determine
            if (i == 0) {
                allowed = true;
                break;
            }
        }
 
        if (!allowed) {
            str = replacer(html, "", str); // Custom replace. No regexing
        }
    }
 
    return str;
}