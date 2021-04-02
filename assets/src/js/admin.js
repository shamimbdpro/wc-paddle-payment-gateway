(function($){
    "use strict";

    $(document).ready(function(){

        //Notice span click handler
        $(document).on('click', '.non-sensitive', function(){
            $('.paddle-track-list').toggleClass('visible');
        });


    });

})(jQuery);