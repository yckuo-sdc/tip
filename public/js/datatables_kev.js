/* Formatting function for row details - modify as you need */
function format ( d ) {
    // `d` is the original data object for the row
    let htmlString = '<table cellpadding="5" cellspacing="0" border="0">' +
        '<tr>' + 
            '<td>Description:</td>' + 
            '<td>' + d.short_description + '</td>'+
        '</tr>' +
        '<tr>' + 
            '<td>Action:</td>' + 
            '<td>' + d.required_action + '</td>'+
        '</tr>' +
        '<tr>' + 
            '<td>Note:</td>' + 
            '<td>' + d.notes + '</td>'+
        '</tr>' +
        '<tr>' + 
            '<td>PoC:</td>' + 
            '<td>' + d.poc + '</td>'+
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
        ajax: '/ajax/kev/',
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
            { data: 'date_added_at' },
            { data: 'due_date_at' },
            { data: 'cve_id',
				render: function(data, type, row, meta) {
                    let render_data = "<a href='https://nvd.nist.gov/vuln/detail/" + data + "' target='_blank'>" + data + "</a>";
                    return render_data;
                }
            },
            { data: 'vendor_project' },
            { data: 'product' },
            { data: 'cvss_v3_score' },
            { data: 'cwe_id',
				render: function(data, type, row, meta) {
                    if (data.startsWith('CWE-')) {
                        const match = data.match(/CWE-(\d+)/);
                        if (match) {
                            const numberAfterHyphen = match[1];
                            let render_data = "<a href='https://cwe.mitre.org/data/definitions/" + numberAfterHyphen + "'.html target='_blank'>" + data + "</a>";
                            return render_data;
                        }
                    }
                    return data;
                }
            },
            { data: 'known_ransomware_campaign_use',
				render: function(data, type, row, meta) {
                    let render_data = data
                    if (data === "Known") {
                        render_data = "<div class='ui olive label'>Used</div>";
                    } else {
                        render_data = "-";
                    }
                    return render_data;
                }
            },
            { data: 'poc',
				render: function(data, type, row, meta) {
                    let render_data = data
                    if (data === "") {
                        render_data = "-";
                    } else {
                        //let decodedString = data.replace(/&quot;/g, '"');
                        //let jsonObject = JSON.parse(decodedString);
                        render_data = "<div class='ui orange label'>Existed</div>";
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
				targets: 2,
				width: "10%",
			},
        ],
        order: [
            [1, 'desc'],
            [2, 'desc'],
            [3, 'desc'],
        ],
        initComplete: function () {
            // Apply column searching (text inputs)
            this.api().columns([1, 2, 3, 4, 5, 6, 7, 8, 9]).every( function () {
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
