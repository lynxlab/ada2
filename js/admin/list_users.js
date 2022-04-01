function initDoc(isAdmin) {
    createDataTable(isAdmin === 1);
}

function createDataTable(isAdmin) {
    datatable = $j('#admin_list_users').DataTable({
        "paging": true,
        "ordering": true,
        "info": true,
        "filter": true,
        "autoWidth": true,
        "stateSave": true,
        "order": [[0, 'asc']],
        "language": {
            "url": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        },
        "columnDefs": [{
            "targets": -1,
            "orderable": false,
            "searchable": false,
        },{
            "targets": [1, 2, 3, 4],
            "className": "truncate",
        }],
    });
}
