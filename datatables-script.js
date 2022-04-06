jQuery(document).ready(function($) {

    $('#post_ajax_table').DataTable( {
        "processing": true,
        "serverSide": true,
        "columns" : [
            { data: 'title' },
            { data: 'status' },
            { data: 'created_at' }            
        ],
        "ajax": {
          "dataSrc":"data",
          url: datatables_obj.ajaxurl + '?action=get_products_for_table',
          dataFilter: function(data){
                var json = JSON.parse( data );                
                json.recordsTotal = json.total;
                json.recordsFiltered = json.total;
                json.data = json.data;
                return JSON.stringify( json ); 
            }
        },
    } );

} );