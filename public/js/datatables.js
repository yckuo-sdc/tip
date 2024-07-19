/* Encoding HTML content */
function encodeHTML(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
}
/* Formatting function for row details - modify as you need */
function format ( d ) {
    // `d` is the original data object for the row
    let htmlString = '<table cellpadding="5" cellspacing="0" border="0">' +
        '<tr>' + 
            '<td>Data:</td>' + 
            '<td>' + encodeHTML(d.Data) + '</td>'+
        '</tr>' +
        '<tr>' + 
            '<td>Department:</td>' + 
            '<td>' + d.DEP + '</td>'+
        '</tr>' +
        '<tr>' + 
            '<td>Field:</td>' + 
            '<td>' + d.Field + '</td>'+
        '</tr>' +
        '<tr>' + 
            '<td>Class:</td>' + 
            '<td>' + d.Class + '</td>'+
        '</tr>' +
    '</table>';

    return htmlString;
}

function getData ( d ) {
    // `d` is the original data object for the row
    return  d.description;
}

$(document).ready(function() {

	// Setup - add a text input to each footer cell
    $('#example_table tfoot th:not(:first-child)').each( function () {
        var title = $(this).text();
        $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
    } );
 

	// DataTable
    var datatable = $('#example_table').DataTable( {
        processing: true,
        //serverSide: true,
        ajax: '/ajax/fetch_gsn_asset/',
        dom: 'Bfrtip',
        buttons: [
           'excel',
			{
			   extend: 'csv',
			   charset: 'UTF-8',
			   bom: true,
			}
        ],
	    columns: [
			{
                data:	null,
                className: 'details-control',
                orderable: false,
                defaultContent: '<i class="green plus circle icon"></i>',
            },
            { data: 'Update_Month' },
            { data: 'ACC' },
            { data: 'Hostname' },
            { data: 'IP' },
            { data: 'Port' },
            { data: 'Scan_Module' },
            { data: 'Data_Source' },
            { data: 'Data' },
        ], 
		columnDefs: [
			{
				targets: 0,
				width: "1%",
			},
			{
				targets: 8,
                visible: false,
			},
        ],
        order: [
            [1, 'desc'],
            [2, 'desc'],
            [3, 'desc'],
        ],
        initComplete: function () {
            // Apply column searching (text inputs)
            this.api().columns([1, 2, 3, 4, 5, 6, 7]).every( function () {
                let that = this;
 
                $( 'input', this.footer() ).on( 'keyup change clear', function () {
                    if ( that.search() !== this.value ) {
                        that
                            .search( this.value )
                            .draw();
                    }
                } );

            } );

        },
    } );

	// Add event listener for opening and closing details
    $('#example_table tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = datatable.row( tr );
        var td = $(tr).children().first();
 
        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
            $(td).html('<i class="green plus circle icon"></i>');
        }
        else {
            // Open this row
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
            $(td).html('<i class="red minus circle icon"></i>');
        }
    } );

});
