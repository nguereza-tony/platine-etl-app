
$(document).ready(function () {
    //Initialization of the page
    initPage($);
});

/**
* Function will be called after the page is loaded
*
* @param $ the JQuery instance
*/
function initPage($){
    var body = $('body');

    body.tooltip({
        selector: '[data-toggle="tooltip"]'
    });
    body.popover({
        selector: '[data-toggle="popover"]'
    });

    $('.sidebar-toggle').on('click', function (e) {
        e.preventDefault();
        $('.sidebar').toggleClass('toggled');
    });
    
    // For confirm message
    $('[data-text-confirm]').bind('click', function(e) {
        const message = $(this).attr('data-text-confirm');
        return confirm(message);
    });
    
    /**
     * for number format
     */
    $("input.number, .number").each(function () {
        $(this).number($(this).text(), 0, '.', ',');
    });

    /**
     * for permissions dependancy checked auto
     */
    $('.role-permission').change(function(){
         var checked = $(this).prop('checked');
         if(checked){
             var depend = $(this).attr('data-depend');
             if(depend){
                $('input#'+depend).prop('checked', true);
             }
        }
        else{
            var id = $(this).attr('id');
            $('input[data-depend='+id+']').prop('checked', false);
        }
    });
    
    /**
     * select/deselect all the checkbox for batch actions
     */
    $(".list-actions-checkbox").change(function(e){
        e.preventDefault();
        var checked = $(this).prop('checked');
        $(".list-action:checkbox").prop('checked', checked);
        
    });

    /**
     * select/deselect all the checkbox
     */
    $("#select_checkbox").click(function(e){
        e.preventDefault();
        $(":checkbox").prop('checked', true);
    });

    $("#deselect_checkbox").click(function(e){
        e.preventDefault();
        $(":checkbox").prop('checked', false);
    });
    
    
    $('.depense_product_prices').change(function(e){
       var prices = $('input[name="prices[]"]').map(function () {
            return this.value;
       }).get();    
       var totalAmount = 0;
       
       prices.forEach(function(p){
          totalAmount += parseInt(p); 
       });
       
       $('#depense_total_amount').html(totalAmount);
    });
    
    /**
     For select2 search
    */
    $(".select2js").select2({
        allowClear: false
    });
    
    $('.depense_product_prices').trigger('change');
}

