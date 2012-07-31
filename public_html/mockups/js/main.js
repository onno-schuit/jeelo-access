$(function(){
    $('.toggler').live('click', function(e) {
        if (this.innerHTML == '<i class="icon icon-ok icon-green"></i>') {
            this.innerHTML = '<i class="icon icon-remove icon-red"></i>';
        } else { // None and Part cases
            this.innerHTML = '<i class="icon icon-ok icon-green"></i>';
        }
    });

    $('.tip').tooltip();

    $('.global-toggler').live('click', function(e) {
        if (this.innerHTML == '<i class="icon icon-ok icon-white"></i>') {
            this.innerHTML = '<i class="icon icon-remove icon-white"></i>';
        } else { // None and Part cases
            this.innerHTML = '<i class="icon icon-ok icon-white"></i>';
        }
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

