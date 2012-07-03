$(function(){
    $('.toggler').live('click', function(e) {
        if (this.innerHTML == 'All ') {
            this.className = 'btn toggler';
            this.innerHTML = 'None';
        } else { // None and Part cases
            this.className = 'btn toggler btn-success';
            this.innerHTML = 'All ';
        }
    });

    $('.show-all').live('click', function(e) {
        if (this.className.indexOf('open') !== -1) {
            // Subform is open, close
            this.className = 'show-all';
            $('.sub.' + this.id).hide();
            this.innerHTML = 'Show <i class="icon-arrow-right icon-white"></i>';
        } else {
            // Subform is closed, open
            this.className = 'show-all open';
            $('.sub.' + this.id).show();

            this.innerHTML = '<i class="icon-arrow-left icon-white"></i> Hide';
        }
    });

});

