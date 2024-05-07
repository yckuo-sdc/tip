/* Formatting function for row details - modify as you need */
function format ( d ) {
    // `d` is the original data object for the row
    let htmlString = '<table cellpadding="5" cellspacing="0" border="0">' +
        '<tr>' + 
            '<td>Description:</td>' + 
            '<td>' + d.description + '</td>'+
        '</tr>' +
    '</table>';

    return htmlString;
    //return '<table cellpadding="5" cellspacing="0" border="0">' +
    //    '<tr>' + 
    //        '<td>Description:</td>' + 
    //        '<td class="kkc">' + d.description + '</td>'+
    //    '</tr>' +
    //'</table>';
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
        ajax: '/ajax/news/',
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
            { data: 'published_at' },
            { data: 'hyperlink',
				render: function(data, type, row, meta) {
                    var render_data = data;
                    var label = data;
                    var sections = data.split('|');
                    var title = sections[0];
                    var link = sections[1];

                    render_data = "<a href='" + link + "' target='_blank'>" + title + "</a>";
                    return render_data;
                }
            },
            { data: 'source' },
            { data: 'tag',
				render: function(data, type, row, meta) {
                    var render_data = "";
                    if (data) {
                        var tag_array = data.split("|");
                        tag_array.forEach(function(item) {
                           render_data += "<div class='ui basic grey label'>" + item + "</div>";
                        });
                    }
                    return render_data;
                }
            },
        ], 
		columnDefs: [
			{
				targets: 0,
				width: "1%",
			},
			{
				targets: 1,
				width: "10%",
			},
			{
				targets: 3,
				width: "10%",
			},
        ],
        order: [
            [1, 'desc'],
            [2, 'asc'],
            [3, 'asc'],
        ],
        initComplete: function () {
            // Apply column searching (text inputs)
            this.api().columns([1,2]).every( function () {
                let that = this;
 
                $( 'input', this.footer() ).on( 'keyup change clear', function () {
                    if ( that.search() !== this.value ) {
                        that
                            .search( this.value )
                            .draw();
                    }
                } );

            } );

            // Apply column searching (select inputs)
            this.api().column(3).every( function () {
                let column = this;
 
                // Create select element
                let select = document.createElement('select');
                select.style.cssText = 'max-width: 100%';

                select.add(new Option(''));
                column.footer('source').replaceChildren(select);
 
                // Apply listener for user change in value
                select.addEventListener('change', function () {
                    var val = DataTable.util.escapeRegex(select.value);
 
                    column
                        .search(val ? '^' + val + '$' : '', true, false)
                        .draw();
                });
 
                // Add list of options
                column
                    .data()
                    .unique()
                    .sort()
                    .each(function (d, j) {
                        select.add(new Option(d));
                    });

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
