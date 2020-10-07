/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function initDoc(id_course, id_course_instance) {

	var lastCol = $j('table#videochatlog thead th').length;
	var colDefs = [
		{
			"data": "details.id_room",
			"render": function (data, type, row) {
				if (type === 'display') {
					if ('users' in row && row.users.length > 0) {
						return '<i class="sign icon add green" data-roomid="' + data + '"></i>';
					} else {
						return '&nbsp;';
					}
				}
				return data;
			},
			"sClass": "details-control",
			"bSearchable": false,
			"bSortable": false,
		},
		{
			"data": "details.descrizione_videochat",
			"render": function (data, type) {
				if (type === 'display') {
					return data.split('-')[0].trim();
				}
				return data;
			},
		},
		{
			"data": "details.inizio",
			"render": function (data, type) {
				return (type === 'display') ? ts2date(data) : data;
			},
		},
		{
			"data": "details.fine",
			"render": function (data, type) {
				return (type === 'display') ? ts2date(data) : data;
			},
		},
		{
			"data": "users",
			"render": function (data, type) {
				return (type === 'display') ? data.length : data;
			},
		},
		{
			"data": "details.tipo_videochat_descr",
		}
	];

	const datatable = $j('table#videochatlog').DataTable({
		// "stateSave": true,
		// "stateDuration": -1,
		"deferRender": true,
		"processing": true,
		"autoWidth": false,
		"rowId": 'details.rowId',
		"searchDelay": 500, //in millis
		"order": [[1, 'asc']],
		"retrieve": true,
		"columns": colDefs,
		"ajax": "ajax/getVideoChatLog.php?id_course=" + id_course + "&id_course_instance=" + id_course_instance,
		"oLanguage": {
			"sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
		}
	});

	$j('table#videochatlog').on('click', 'tbody>tr>td.details-control>i', function () {
		// hook details-control cell click to open child row
		var tr = $j(this).closest('tr');
		var row = datatable.row(tr);
		if (row.child.isShown()) {
			$j(this).removeClass('minus red').addClass('add green');
			tr.removeClass('details');
			row.child.hide();
		} else {
			const data = row.data();
			$j(this).removeClass('add green').addClass('minus red');
			tr.addClass('details');
			const template = $j('div#videochatlog-details').clone();
			const childTable = $j('table', template);
			childTable.prop('id', 'details-' + data.details.id_room);
			data.users.forEach((user, index) => {
				if ('events' in user && user.events.length > 0) {
					user.events.forEach((uevent, uindex) => {
						const rowHtml = [];
						rowHtml.push('<tr>');
						rowHtml.push('<td>' + user.nome + ' ' + user.cognome + '</td>');
						rowHtml.push('<td>' + uevent.entrata + '</td>');
						rowHtml.push('<td>' + uevent.uscita + '</td>');
						rowHtml.push('</tr>');
						$j('tbody', childTable).prepend(rowHtml.join(''));
					});
				}
			});
			row.child($j(template).html(), 'videochatroomDetails').show();
			var groupColumn = 0;
			var table = $j('#details-' + data.details.id_room).DataTable({
				"dom": "<'ui stackable grid'" +
					"<'row dt-custom-headercontrols'" +
					"<'eight wide column'Bl>" +
					"<'right aligned eight wide column'f>" +
					">" +
					"<'row dt-table'" +
					"<'sixteen wide column'tr>" +
					">" +
					"<'row'" +
					"<'seven wide column'i>" +
					"<'right aligned nine wide column'p>" +
					">" +
					">",
				buttons: [
					{
						title: data.details.descrizione_videochat.split('-')[0].trim() + '-' + ts2date(data.details.inizio).replace(/[\/:\s]/g, ""),
						extend: 'excel',
						text: $j('#exportExcelBtnText').html(),
						className: 'ui tiny button'
					},
					{
						title: data.details.descrizione_videochat.split('-')[0].trim() + '-' + ts2date(data.details.inizio).replace(/[\/:\s]/g, ""),
						extend: 'pdf',
						text: $j('#exportPDFBtnText').html(),
						className: 'ui tiny button'
					}
				],
				"columnDefs": [
					{ "visible": false, "targets": groupColumn },
					{
						"render": function (data, type) {
							return (type === 'display') ? ts2date(data) : data;
						},
						"targets": [1, 2],
					},
				],
				"order": [[groupColumn, 'asc'], [1, 'asc']],
				"oLanguage": {
					"sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
				},
				"drawCallback": function (settings) {
					var api = this.api();
					var rows = api.rows({ page: 'current' }).nodes();
					var last = null;

					api.column(groupColumn, { page: 'current' }).data().each(function (group, i) {
						if (last !== group) {
							$j(rows).eq(i).before(
								'<tr class="group"><td colspan="2">' + group + '</td></tr>'
							);
							last = group;
						}
					});
				}
			});
			// Order by the grouping
			$j('#details-' + data.details.id_room + ' tbody').on('click', 'tr.group', function () {
				var currentOrder = table.order()[0];
				if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
					table.order([groupColumn, 'desc']).draw();
				}
				else {
					table.order([groupColumn, 'asc']).draw();
				}
			});
		}
	});
}

function ts2date(timestamp) {
	const theDate = new Date(timestamp * 1000);
	// return date and month padded with zero
	return ('0' + theDate.getDate()).slice(-2) + '/'
		+ ('0' + (theDate.getMonth() + 1)).slice(-2) + '/'
		+ theDate.getFullYear() + ' ' + ('0' + theDate.getHours()).slice(-2) + ':' + ('0' + theDate.getMinutes()).slice(-2) + ':' + ('0' + theDate.getSeconds()).slice(-2);
}
