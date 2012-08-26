
var savedTimer;

function set_status(item, status) {
  if (status == 1) {
    item.innerHTML = '<i class="icon icon-ok icon-green"></i>';
  } else if (status == 0) {
    item.innerHTML = '<i class="icon icon-remove icon-red"></i>';
  } else {
    item.innerHTML = '<i class="icon icon-adjust icon-yellow"></i>';
  }
  $(item).attr('status', status);
}

function check_group_status(item, status) {
    var user = $(item).attr('user');
    var type = $(item).attr('type');

    var arrs = Array();
    $('.toggler[user="' + user + '"][type="' + type + '"]').each(function(i, item) {
	arrs.push(parseInt($(item).attr('status')));
    });

    var group_toggler = $('.user-group-toggler[user="' + user + '"][type="' + type + '"]').get(0);

    if (arrs.indexOf(0) == -1) {
	// All set to true
	set_status(group_toggler, 1);
    } else if (arrs.indexOf(1) == -1) {
	// All set to false
	set_status(group_toggler, 0);
    } else {
	// Custom
	set_status(group_toggler, 2);
    }
}

function save(action, params, callback) {
  $.ajax({
    type: 'POST',
    url: '/mod/jeelo/?course/' + COURSE_ID + '/save_' + action + '.json',
    data: params,
    dataType: 'json',

    success: function(response, textStatus, jqXHR) {
	$('#saved').css({'display': 'block'});
	savedTimer = setTimeout('close_alert("saved")', 5000);
	callback(response, textStatus, jqXHR);
    },
    error: function(jqXHR, textStatus, errorThrown) {
	$('#err').css({'display': 'block'});
	savedTimer = setTimeout('close_alert("err")', 5000);
    }
  });
}

function close_alert(id) {
    clearTimeout(savedTimer);
    $('#' + id).css({'display': 'none'});
}

$(function(){
    $('.toggler').live('click', function(e) {
	
	var id = $(this).attr('gid');
	var type = $(this).attr('type');


	var userid = $(this).attr('user');

	var status = 1; // Enable action is default
        if ($(this).attr('status') == 1) {
	    status = 0;
	}


	var tglr = this;

	save('one',
             {'id': id, 'type': type, 'status': status, 'userid': userid},
             function(response, textStatus, jqXHR) {
		if (response.status == 'ok') {
                  set_status(tglr, status);
		  // Set group status
		  check_group_status(tglr, status);
		}
             });


    });

    $('.tip').tooltip();

    $('.global-toggler').live('click', function(e) {
        if (this.innerHTML == '<i class="icon icon-ok icon-white"></i>') {
            this.innerHTML = '<i class="icon icon-remove icon-white"></i>';
        } else { // None and Part cases
            this.innerHTML = '<i class="icon icon-ok icon-white"></i>';
        }
    });

    $('.row-toggler').live('click', function(e) {
        var status = 0;
        if (this.innerHTML == '<i class="icon icon-ok icon-white"></i>') {
            status = 1;
        }

        var userid = $(this).attr('user');
        var ith = this;
        save('user',
             {'userid': userid, 'status': status},
             function(response, textStatus, jqXHR) {
               if (status == 1) {
                   ith.innerHTML = '<i class="icon icon-remove icon-white"></i>';
               } else {
                   ith.innerHTML = '<i class="icon icon-ok icon-white"></i>';
               }

               $('a[user="' + userid + '"]').each(function(i, item) {
                   set_status(item, status);
               });
             });
        
    });

    $('.col-toggler').live('click', function(e) {
        var status = 0;
        if (this.innerHTML == '<i class="icon icon-ok icon-white"></i>') {
            status = 1;
        }

	var id = $(this).attr('gid');
        var ith = this;
        save('activity',
             {'activity': $(this).attr('gid'), 'type': $(this).attr('itype'), 'status': status},
             function(response, textStatus, jqXHR) {
               if (status == 1) {
                   ith.innerHTML = '<i class="icon icon-remove icon-white"></i>';
               } else {
                   ith.innerHTML = '<i class="icon icon-ok icon-white"></i>';
               }

               $('a.toggler[gid="' + id + '"]').each(function(i, item) {
                   set_status(item, status);
               });
             });
    });

    $('.group-toggler').live('click', function(e) {
        var status = 0;
        if (this.innerHTML == '<i class="icon icon-ok icon-white"></i>') {
            status = 1;
        }

        var type = $(this).attr('itype');
        var ith = this;
        save('group',
             {'type': type, 'status': status},
             function(response, textStatus, jqXHR) {
               if (status == 1) {
                   ith.innerHTML = '<i class="icon icon-remove icon-white"></i>';
               } else {
                   ith.innerHTML = '<i class="icon icon-ok icon-white"></i>';
               }

               $('a.toggler[type="' + type + '"]').each(function(i, item) {
                   set_status(item, status);
		  // Set group status
		  check_group_status(item, status);
               });
             });
    });

    $('.user-group-toggler').live('click', function(e) {
        var type = $(this).attr('type');
        var user = $(this).attr('user');
        var status = 1;
        if ($(this).attr('status') == 1) {
          status = 0;
        }

        var gt = this;
        save('user_group',
             {'userid': user, 'type': type, 'status': status},
             function(response, textStatus, jqXHR) {
               $('a.toggler[type="' + type + '"][user="' + user + '"]').each(function(i, item){
                   set_status(item, status);
               });
               set_status(gt, status);
             });
    });

    $('.show-all').live('click', function(e) {
        if (this.className.indexOf('open') !== -1) {
            // Subform is open, close
            this.className = 'show-all';
            $('.sub.' + this.id).hide();
            this.innerHTML = 'Toon <i class="icon-arrow-right icon-white"></i>';
        } else {
            // Subform is closed, open
            this.className = 'show-all open';
            $('.sub.' + this.id).show();

            this.innerHTML = '<i class="icon-arrow-left icon-white"></i> Verberg';
        }
    });

});

