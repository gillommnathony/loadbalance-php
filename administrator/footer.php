<?php
if (!defined('BASE_DIR')) exit();
?>
</main>
<footer id="footer" class="row py-5 bg-dark flex-shrink-0">
    <div class="col-12">
        <ul class="nav justify-content-center">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>terms/">Terms</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>privacy/">Privacy</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="https://forms.gle/zbMrMTtwjdUahtwq6" target="_blank">DMCA</a>
            </li>
        </ul>
        <p class="text-center text-white my-3">&copy; 2020 - <?php echo date('Y'); ?>. Made with <i class="fa fa-heart text-danger"></i> by <?php echo sitename(); ?>.</p>
    </div>
</footer>
</div>
<a href="javascript:void(0)" id="gotoTop" class="bg-custom shadow">
    <span class="gotoContent">
        <i class="fa fa-chevron-up"></i>
    </span>
</a>
<script src="<?php echo BASE_URL; ?>assets/js/popper.min.js" defer></script>
<script src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js" defer></script>
<script src="<?php echo BASE_URL; ?>assets/js/sweetalert.min.js" defer></script>
<script>
    jQuery(document).ready(function($) {
        $('[data-toggle="tooltip"], [data-tooltip="true"]').tooltip();
        $('[data-toggle="popover"], [data-popover="true"]').popover({
            container: 'body',
            placement: 'top',
            html: true
        });
        $('#user').blur(function() {
            if ($(this).val() !== '') {
                $.ajax({
                    url: './ajax/public.ajax.php',
                    type: 'POST',
                    data: {
                        action: 'check_username',
                        username: $(this).val()
                    },
                    success: function(res) {
                        if (res.status === 'fail') swal('Warning!', res.message, 'warning');
                    },
                    error: function(xhr) {
                        swal('Error!', xhr.responseText, 'error');
                    }
                });
            }
        });
        $('#email').blur(function() {
            if ($(this).val() !== '') {
                $.ajax({
                    url: './ajax/public.ajax.php',
                    type: 'POST',
                    data: {
                        action: 'check_email',
                        email: $(this).val()
                    },
                    success: function(res) {
                        if (res.status === 'fail') swal('Warning!', res.message, 'warning');
                    },
                    error: function(xhr) {
                        swal('Error!', xhr.responseText, 'error');
                    }
                });
            }
        });
    });
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(e) {
                    if (form.checkValidity() === false) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
<?php if ($login->cek_login()) : ?>
    <div class="modal fade" id="modalUploadSub" tabindex="-1" role="dialog" aria-labelledby="modalUploadSubLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUploadSubLabel">Upload Subtitle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="subRef">
                    <form id="frmUploadSub" method="post" enctype="multipart/form-data">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="uploadSubFile" name="uploadSubFile">
                            <label class="custom-file-label" for="uploadSubFile">Choose file</label>
                        </div>
                    </form>
                    <div id="upsProgress" class="progress mt-2 d-none">
                        <div id="uploadSubProgress" class="progress-bar active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                            <span class="sr-only">0%</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="btnUploadSub" class="btn btn-primary" disabled>Upload Now</button>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo BASE_URL; ?>assets/js/jquery.multi-select.js" defer></script>
    <script src="<?php echo BASE_URL; ?>assets/vendor/datatables/datatables.min.js" defer></script>
    <script src="<?php echo BASE_URL; ?>assets/vendor/jquery-datatables-pageLoadMore/js/dataTables.pageLoadMore.min.js" defer></script>
    <script src="<?php echo BASE_URL; ?>assets/vendor/select2/js/select2.min.js" defer></script>
    <script src="<?php echo BASE_URL; ?>assets/vendor/jquery-wheelcolorpicker/jquery.wheelcolorpicker.min.js" defer></script>
    <script src="<?php echo BASE_URL; ?>assets/js/md5.js" defer></script>
    <script>
        var $ = jQuery.noConflict();

        $(document).ready(function() {
            var showIDFormat = localStorage.getItem('hostIDFormat');
            if (showIDFormat) {
                $('#hostIDFormat').collapse('show');
            }
            $('#hostIDFormat').on('shown.bs.collapse', function() {
                localStorage.setItem('hostIDFormat', true);
            }).on('hidden.bs.collapse', function() {
                localStorage.removeItem('hostIDFormat', true);
            });

            $('form .btn-info').click(function() {
                $('#hostIDFormat').collapse('show');
                $('html,body').animate({
                    scrollTop: $('#hostIDFormat').offset().top
                }, 'slow');
            });

            $.fn.DataTable.ext.pager.simple_numbers_no_ellipses = function(page, pages) {
                var numbers = [];
                var buttons = 5;
                var half = Math.floor(buttons / 2);

                var _range = function(len, start) {
                    var end;
                    var out = [];
                    if (typeof start === "undefined") {
                        start = 0;
                    } else {
                        end = start;
                        start = len;
                    }
                    for (var i = start; i < end; i++) {
                        out.push(i);
                    }
                    return out;
                };
                if (pages <= buttons) {
                    numbers = _range(0, pages);
                } else if (page <= half) {
                    numbers = _range(0, buttons);
                } else if (page >= pages - 1 - half) {
                    numbers = _range(pages - buttons, pages);
                } else {
                    numbers = _range(page - half, page + half + 1);
                }
                numbers.DT_el = 'span';
                return ['previous', numbers, 'next'];
            };

            $.extend(true, $.fn.dataTable.defaults, {
                destroy: true,
                stateSave: true,
                responsive: true,
                processing: true,
                paging: true,
                pagingType: 'simple_numbers_no_ellipses',
                deferRender: true,
                rowReorder: true,
                language: {
                    paginate: {
                        previous: '<i class="fa fa-chevron-left"></i>',
                        next: '<i class="fa fa-chevron-right"></i>'
                    }
                }
            });

            users.list();
            videos.list();
            subtitles.list();
            load_balancers.list();
            gdrive_accounts.list();
            gdrive_files.list();
            sessions.list();

            $('#bypass_host, #disable_host').multiSelect();

            $('#ckAllGDA, #ckAllGDA1').change(function() {
                var isChecked = $(this).prop('checked');
                var $ckItem = $('#tbGDAccounts').find('tbody').find('input.custom-control-input');
                if (isChecked) {
                    $ckItem.prop('checked', true);
                } else {
                    $ckItem.prop('checked', false);
                }
            });

            $('#ckAllSessions, #ckAllSessions1').change(function() {
                var isChecked = $(this).prop('checked');
                var $ckItem = $('#tbSessions').find('tbody').find('input.custom-control-input');
                if (isChecked) {
                    $ckItem.prop('checked', true);
                } else {
                    $ckItem.prop('checked', false);
                }
            });

            $('#public_video_user').select2({
                theme: 'bootstrap4'
            });

            if ($('select#email').length) {
                localStorage.removeItem('DataTables_tbGDFiles_/administrator/admin.php');

                $('select#email').change(function() {
                    localStorage.removeItem('DataTables_tbGDFiles_/administrator/admin.php');
                    gdrive_files.list();
                });

                $('#ckAll, #ckAll1').change(function() {
                    var isChecked = $(this).prop('checked');
                    var $ckItem = $('#tbGDFiles').find('tbody').find('input.custom-control-input');
                    if (isChecked) {
                        $ckItem.prop('checked', true);
                    } else {
                        $ckItem.prop('checked', false);
                    }
                });

                $('th[aria-controls="tbGDFiles"]').click(function() {
                    gdrive_files.reload();
                });

                $('#tbGDFiles_filter input[type="search"]').blur(function() {
                    gdrive_files.reload();
                })
            }

            $('#ckAllVideos, #ckAllVideos1').change(function() {
                var isChecked = $(this).prop('checked');
                var $ckItem = $('#tbVideos').find('tbody').find('input.custom-control-input');
                if (isChecked) {
                    $ckItem.prop('checked', true);
                } else {
                    $ckItem.prop('checked', false);
                }
            });

            $('#ckAllSubtitles, #ckAllSubtitles1').change(function() {
                var isChecked = $(this).prop('checked');
                var $ckItem = $('#tbSubtitles').find('tbody').find('input.custom-control-input');
                if (isChecked) {
                    $ckItem.prop('checked', true);
                } else {
                    $ckItem.prop('checked', false);
                }
            });

            if ($('#frmVideoBulkLink').length) {
                $('#frmVideoBulkLink').on('submit', function(e) {
                    videos.saveBulkLink();
                    e.preventDefault();
                });
                $('#frmVideoBulkLink').on('reset', function(e) {
                    $('#links').val('');
                    $('#bulk-result').html('');
                    e.preventDefault();
                });
            }

            $('#multiEmbedCollapse').on('shown.bs.collapse', function() {
                $('#bulkLinkCollapse').collapse('hide');
            });

            $('#bulkLinkCollapse').on('shown.bs.collapse', function() {
                $('#multiEmbedCollapse').collapse('hide');
            });

            $('#frmMultiEmbed').on('submit', function(e) {
                e.preventDefault();
                $(this).find('[type=submit]').prop('disabled', true);
                console.log($(this).serialize());
            });

            $('#uploadSubFile').on('change', function() {
                var fileName = $(this).val();
                $(this).next('.custom-file-label').html(fileName);
                $('#btnUploadSub').prop('disabled', false);
            })

            $('#frmUploadSub').on('submit', function(e) {
                e.preventDefault();
                var refId = $('#subRef').val();
                $('#uploadSubFile').next('.custom-file-label').html('Choose file');
                $('#btnUploadSub').prop('disabled', true);
                if (refId !== '') {
                    var $url = $('.form-group[data-index=' + refId + ']').find('input[type=url]');
                    videos.uploadSubtitle($url);
                } else {
                    videos.uploadSubtitle();
                }
            });

            $('#btnUploadSub').click(function() {
                $('#frmUploadSub').trigger('submit');
            });

            $(window).on('scroll', function() {
                var g = $("#gotoTop");
                if (document.body.scrollTop > 640 || document.documentElement.scrollTop > 640) {
                    g.fadeIn();
                } else {
                    g.fadeOut();
                }
            });

            $('#gotoTop').on('click', function() {
                $('html,body').animate({
                    scrollTop: 0
                }, 'slow');
            });
        });

        function copyText(text) {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(text).select();
            document.execCommand("copy");
            $temp.remove();
            alert('Embed code was copied.');
        }

        var hosts = JSON.parse('<?php echo vidhost_supported('', '', false, true); ?>');

        var subtitles = {
            removeIndex: function(id) {
                var $row = $('#tbSubtitles').find('tbody').find('tr#' + id);
                $row.next('tr.child').remove();
                $row.remove();
                if ($('#tbSubtitles').find('tbody').find('tr').length == 0) {
                    subtitles.reload();
                }
            },
            deleteChecked: function(dbOnly) {
                var ids = [];
                var $ckItem = $('#tbSubtitles').find('tbody').find('input[type=checkbox]:checked');
                $ckItem.each(function() {
                    ids.push($(this).val());
                });
                if (ids.length) {
                    $('#ckAllSubtitles, #ckAllSubtitles1').prop('checked', false);
                    swal({
                            title: "Are you sure?",
                            text: "Are you sure you want to delete these " + ids.length + " subtitles? Deleted subtitles cannot be restored anymore!",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            setTimeout(function() {
                                var ids_deleted = [];
                                var ids_failed = [];
                                var j = 0;
                                var deleteFile = function(id, i, dbOnly) {
                                    var action = typeof dbOnly !== 'undefined' ? 'delete_db_only' : 'delete';
                                    $.ajax({
                                        url: './ajax/subtitles.ajax.php',
                                        type: 'POST',
                                        data: {
                                            action: action,
                                            id: id
                                        },
                                        success: function(result) {
                                            if (result.status === 'ok') {
                                                ids_deleted.push(id);
                                                subtitles.removeIndex(id);
                                            } else {
                                                ids_failed.push(id);
                                            }
                                        },
                                        error: function() {
                                            ids_failed.push(id);
                                        },
                                        complete: function() {
                                            j++;
                                            if (j >= ids.length) {
                                                if (ids_deleted.length > 0) {
                                                    swal({
                                                        title: 'Delete Subtitles',
                                                        text: ids_deleted.length + ' subtitles have been deleted and ' + ids_failed.length + ' subtitles have failed to delete.',
                                                        type: "info",
                                                        showLoaderOnConfirm: false
                                                    });
                                                    return;
                                                } else {
                                                    swal({
                                                        title: "Files does not exist",
                                                        text: ids_failed.length + " subtitle files not found on this website. Do you still want to delete it?",
                                                        type: "warning",
                                                        showLoaderOnConfirm: true,
                                                        showCancelButton: true,
                                                        cancelButtonClass: "btn-secondary",
                                                        confirmButtonClass: "btn-danger",
                                                        closeOnConfirm: false
                                                    }, function(isConfirm) {
                                                        if (!isConfirm) return;
                                                        var ids_deleted2 = [];
                                                        var ids_failed2 = [];
                                                        var j = 0;
                                                        for (var x = 0; x < ids_failed.length; x++) {
                                                            $.ajax({
                                                                url: "./ajax/subtitles.ajax.php",
                                                                type: "POST",
                                                                data: {
                                                                    id: ids_failed[x],
                                                                    action: "delete_db_only"
                                                                },
                                                                complete: function() {
                                                                    j++;
                                                                    if (j >= ids_failed.length) {
                                                                        swal({
                                                                            title: 'Delete Subtitles',
                                                                            text: ids_deleted2.length + ' subtitles have been deleted and ' + ids_failed2.length + ' subtitles have failed to delete.',
                                                                            type: "info",
                                                                            showLoaderOnConfirm: false
                                                                        });
                                                                        subtitles.reload();
                                                                        return;
                                                                    }
                                                                },
                                                                success: function(res) {
                                                                    if (res.status === 'ok') {
                                                                        ids_deleted2.push(ids_failed[x]);
                                                                    } else {
                                                                        ids_failed2.push(ids_failed[x]);
                                                                    }
                                                                },
                                                                error: function(xhr) {
                                                                    ids_failed2.push(ids_failed[x]);
                                                                }
                                                            });
                                                        }
                                                    });
                                                }
                                            }
                                        }
                                    });
                                };
                                for (var i = 0; i < ids.length; i++) {
                                    deleteFile(ids[i], i, dbOnly);
                                }
                            }, 1000);
                        }
                    );
                } else {
                    swal('Warning!', 'Please select the subtitle you want to delete!', 'warning')
                }
            },
            list: function() {
                if ($('#tbSubtitles').length) {
                    $('#tbSubtitles').DataTable({
                        serverSide: true,
                        ajax: "./ajax/subtitles.datatables.ajax.php",
                        columns: [{
                                data: 'DT_RowId',
                                responsivePriority: 0,
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    var html = '';
                                    html += '<div class="custom-control custom-checkbox">';
                                    html += '<input type="checkbox" class="custom-control-input" id="row-' + meta.row + '" value="' + value + '">';
                                    html += '<label class="custom-control-label" for="row-' + meta.row + '"></label>';
                                    html += '</div>';
                                    return html;
                                }
                            }, {
                                data: 'file_name',
                                responsivePriority: 1,
                            },
                            {
                                data: 'language',
                                className: 'text-center',
                            },
                            {
                                data: 'name',
                            },
                            {
                                data: 'host',
                                render: function(value, type, row) {
                                    if (value == null) {
                                        return '';
                                    }
                                    return '<a href="' + value + '" target="_blank">' + value + '</a>';
                                }
                            },
                            {
                                data: 'added',
                                className: 'text-right',
                            },
                            {
                                data: 'id',
                                className: 'text-center',
                                responsivePriority: 2,
                                render: function(value, type, row) {
                                    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-custom dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button><div class="dropdown-menu dropdown-menu-right border-0 shadow"><a href="' + value.link + '" class="dropdown-item" title="Download"><i class="fa fa-download"></i> Download</a><button type="button" class="dropdown-item" onclick="subtitles.delete(' + value.id + ')" title="Delete"><i class="fa fa-trash"></i> Delete</button></div></div>';
                                }
                            }
                        ],
                        columnDefs: [{
                                orderable: false,
                                targets: [0, 6]
                            },
                            {
                                visible: true,
                                targets: [0, 1, 6],
                                className: 'noVis'
                            }
                        ],
                        order: [
                            [5, "desc"]
                        ]
                    });
                }
            },
            delete: function(id) {
                if (typeof id !== 'undefined' && id !== '') {
                    swal({
                            title: "Are you sure?",
                            text: "Deleted data cannot be restored anymore!",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            $.ajax({
                                url: "./ajax/subtitles.ajax.php",
                                type: "POST",
                                data: {
                                    id: id,
                                    action: "delete"
                                },
                                success: function(res) {
                                    if (res.status === 'ok') {
                                        swal("Success!", res.message, "success");
                                        setTimeout(function() {
                                            $('#tbSubtitles').DataTable().ajax.reload(null, false);
                                        }, 1000);
                                    } else {
                                        if (res.message.indexOf('any response') > -1) {
                                            swal({
                                                title: "Are you sure?",
                                                text: "The subtitle file does not exist on this website. Do you still want to delete it?",
                                                type: "warning",
                                                showLoaderOnConfirm: true,
                                                showCancelButton: true,
                                                cancelButtonClass: "btn-secondary",
                                                confirmButtonClass: "btn-danger",
                                                closeOnConfirm: false
                                            }, function(isConfirm) {
                                                if (!isConfirm) return;
                                                $.ajax({
                                                    url: "./ajax/subtitles.ajax.php",
                                                    type: "POST",
                                                    data: {
                                                        id: id,
                                                        action: "delete_db_only"
                                                    },
                                                    success: function(res) {
                                                        if (res.status === 'ok') {
                                                            swal("Success!", res.message, "success");
                                                            setTimeout(function() {
                                                                $('#tbSubtitles').DataTable().ajax.reload(null, false);
                                                            }, 1000);
                                                        } else {
                                                            swal("Error!", res.message, "error");
                                                        }
                                                    },
                                                    error: function(xhr) {
                                                        swal("Error!", xhr.responseText, "error");
                                                    }
                                                });
                                            });
                                        } else {
                                            swal("Error!", res.message, "error");
                                        }
                                    }
                                },
                                error: function(xhr) {
                                    swal("Error!", xhr.responseText, "error");
                                }
                            });
                        });
                }
            },
            reload: function() {
                $('#tbSubtitles').DataTable().ajax.reload(null, false);
            }
        };

        var videos = {
            checker: function($btn) {
                var ids = [];
                var $ckItem = $('#tbVideos').find('tbody').find('input[type=checkbox]:checked');
                $ckItem.each(function() {
                    ids.push($(this).val());
                });
                if (ids.length) {
                    var oText = $btn.html();
                    $btn.html('<i class="fa fa-refresh fa-spin"></i> Checking').prop('disabled', true);
                    $('#ckAllVideos, #ckAllVideos1').prop('checked', false);
                    setTimeout(function() {
                        var ids_exist = [];
                        var ids_failed = [];
                        var j = 0;
                        var checkFile = function(id, i) {
                            $.ajax({
                                url: './ajax/videos.ajax.php',
                                type: 'POST',
                                data: {
                                    action: 'checker',
                                    id: id
                                },
                                success: function(data) {
                                    var wasChecked = false;
                                    var checker = localStorage.getItem('video_checker') !== null ? JSON.parse(localStorage.getItem('video_checker')) : [];
                                    if (data.result === false) {
                                        ids_failed.push(id);
                                        $.each(checker, function(i, v) {
                                            if (v !== null && v.id === id) {
                                                wasChecked = true;
                                                return false;
                                            }
                                        });
                                        if (wasChecked !== true) {
                                            checker.push({
                                                id: id,
                                                status: data.result
                                            });
                                        }
                                        $('#tbVideos').find('tr#' + id).addClass('unavailable');
                                    } else {
                                        $.each(checker, function(i, v) {
                                            if (v === null || v.id === id) {
                                                delete checker[i];
                                                return false;
                                            }
                                        });
                                        ids_exist.push(id);
                                        $('#tbVideos').find('tr#' + id).removeClass('unavailable');
                                    }
                                    localStorage.setItem('video_checker', JSON.stringify(checker));
                                },
                                error: function() {
                                    ids_failed.push(id);
                                },
                                complete: function() {
                                    j++;
                                    if (j >= ids.length) {
                                        swal({
                                            title: 'Videos Checker',
                                            text: ids_exist.length + ' videos exist and ' + ids_failed.length + ' videos are missing.',
                                            type: "info",
                                            showLoaderOnConfirm: false
                                        });
                                        $btn.html(oText).prop('disabled', false);
                                        return;
                                    }
                                }
                            });
                        };
                        for (var i = 0; i < ids.length; i++) {
                            checkFile(ids[i], i);
                        }
                    }, 1000);
                } else {
                    swal('Warning!', 'Please select the video you want to check!', 'warning')
                }
            },
            removeIndex: function(id) {
                var $row = $('#tbVideos').find('tbody').find('tr#' + id);
                $row.next('tr.child').remove();
                $row.remove();
                if ($('#tbVideos').find('tbody').find('tr').length == 0) {
                    videos.reload();
                }
            },
            deleteChecked: function() {
                var ids = [];
                var $ckItem = $('#tbVideos').find('tbody').find('input[type=checkbox]:checked');
                $ckItem.each(function() {
                    ids.push($(this).val());
                });
                if (ids.length) {
                    $('#ckAllVideos, #ckAllVideos1').prop('checked', false);
                    swal({
                            title: "Are you sure?",
                            text: "Are you sure you want to delete these " + ids.length + " videos? Deleted videos cannot be restored anymore!",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            setTimeout(function() {
                                var ids_deleted = [];
                                var ids_failed = [];
                                var j = 0;
                                var deleteFile = function(id, i) {
                                    $.ajax({
                                        url: './ajax/videos.ajax.php',
                                        type: 'POST',
                                        data: {
                                            action: 'delete',
                                            id: id
                                        },
                                        success: function(result) {
                                            if (result.status === 'ok') {
                                                ids_deleted.push(id);
                                                videos.removeIndex(id);
                                            } else {
                                                ids_failed.push(id);
                                            }
                                        },
                                        error: function() {
                                            ids_failed.push(id);
                                        },
                                        complete: function() {
                                            j++;
                                            if (j >= ids.length) {
                                                swal({
                                                    title: 'Delete Videos',
                                                    text: ids_deleted.length + ' videos have been deleted and ' + ids_failed.length + ' videos have failed to delete.',
                                                    type: "info",
                                                    showLoaderOnConfirm: false
                                                });
                                                return;
                                            }
                                        }
                                    });
                                };
                                for (var i = 0; i < ids.length; i++) {
                                    deleteFile(ids[i], i);
                                }
                            }, 1000);
                        }
                    );
                } else {
                    swal('Warning!', 'Please select the video you want to delete!', 'warning')
                }
            },
            checkHostId: function(e) {
                var link = e.val().trim();
                var $hid = $('#' + e.attr('id').replace('_id', ''));
                if (link !== '' && link !== null) {
                    $.ajax({
                        url: './ajax/videos.ajax.php',
                        type: 'post',
                        data: {
                            action: 'get_host_id',
                            url: link
                        },
                        success: function(res) {
                            if (res.status === 'ok') {
                                e.val(res.result.host_id);
                                $hid.val(res.result.host);
                            }
                        },
                        error: function(xhr) {
                            swal("Error!", xhr.responseText, "error");
                        }
                    });
                }
            },
            saveBulkLink: function() {
                var $frm = $('#frmVideoBulkLink');
                var $submit = $frm.find('button[type=submit]');
                var $host = $frm.find('#links');
                $.ajax({
                    url: './ajax/videos.ajax.php',
                    type: 'post',
                    data: {
                        action: 'save_bulk_link',
                        links: $host.val().split("\n")
                    },
                    beforeSend: function() {
                        $submit.find('i').attr('class', 'fa fa-spin fa-refresh');
                        $submit.prop('disabled', true);
                    },
                    success: function(res) {
                        if (res.status === 'ok') {
                            var embedLinks = '<ul>';
                            $.each(res.results, function(i, val) {
                                embedLinks += '<li>' + val.data + "</li>";
                            });
                            embedLinks += '</ul>';
                            $('#bulk-result').html(embedLinks);

                            setTimeout(function() {
                                $('#tbVideos').DataTable().ajax.reload(null, false);
                            }, 1000);
                            swal('Success!', res.message, 'success');
                        } else {
                            swal('Error!', res.message, 'error');
                        }
                        $submit.find('i').attr('class', 'fa fa-save');
                        $submit.prop('disabled', false);
                    },
                    error: function(xhr) {
                        swal("Error!", xhr.responseText, "error");
                    }
                });
            },
            delete: function(id) {
                if (typeof id !== 'undefined' && id !== '') {
                    swal({
                            title: "Are you sure?",
                            text: "Deleted data cannot be restored anymore!",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            $.ajax({
                                url: "./ajax/videos.ajax.php",
                                type: "POST",
                                data: {
                                    id: id,
                                    action: "delete"
                                },
                                success: function(res) {
                                    if (res.status === 'ok') {
                                        swal("Success!", res.message, "success");
                                        setTimeout(function() {
                                            $('#tbVideos').DataTable().ajax.reload(null, false);
                                        }, 1000);
                                    } else {
                                        swal("Error!", res.message, "error");
                                    }
                                },
                                error: function(xhr) {
                                    swal("Error!", xhr.responseText, "error");
                                }
                            });
                        });
                }
            },
            clearCache: function() {
                var ids = [];
                var $ckItem = $('#tbVideos').find('tbody').find('input[type=checkbox]:checked');
                $ckItem.each(function() {
                    ids.push($(this).val());
                });
                if (ids.length) {
                    $('#ckAllVideos, #ckAllVideos1').prop('checked', false);
                    swal({
                            title: "Are you sure?",
                            text: "Are you sure you want to clear the cache of " + ids.length + " videos?",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            setTimeout(function() {
                                var ids_deleted = [];
                                var ids_failed = [];
                                var j = 0;
                                var clearCache = function(id, i) {
                                    $.ajax({
                                        url: "./ajax/videos.ajax.php",
                                        type: "POST",
                                        data: {
                                            id: id,
                                            action: "delete_cache"
                                        },
                                        success: function(res) {
                                            if (res.status === 'ok') {
                                                ids_deleted.push(id);
                                            } else {
                                                ids_failed.push(id);
                                            }
                                        },
                                        error: function(xhr) {
                                            ids_failed.push(id);
                                        },
                                        complete: function() {
                                            j++;
                                            if (j >= ids.length) {
                                                swal({
                                                    title: 'Clear Cache',
                                                    text: ids_deleted.length + ' video cache cleared successfully. While the ' + ids_failed.length + ' video cache failed to clear.',
                                                    type: "info",
                                                    showLoaderOnConfirm: false
                                                });
                                                return;
                                            }
                                        }
                                    });
                                };
                                for (var i = 0; i < ids.length; i++) {
                                    clearCache(ids[i], i);
                                }
                            }, 1000);
                        }
                    );
                } else {
                    swal('Warning!', 'Please select the video to be cleared the cache!', 'warning')
                }
            },
            deleteCache: function(e) {
                $.ajax({
                    url: "./ajax/videos.ajax.php",
                    type: "POST",
                    data: {
                        id: e.attr('data-id'),
                        action: "delete_cache"
                    },
                    beforeSend: function() {
                        e.prop('disabled', true);
                    },
                    complete: function() {
                        e.prop('disabled', false);
                    },
                    success: function(res) {
                        if (res.status === 'ok') {
                            swal("Success!", res.message, "success");
                        } else {
                            swal("Error!", res.message, "error");
                        }
                    },
                    error: function(xhr) {
                        swal("Error!", xhr.responseText, "error");
                    }
                });
            },
            list: function() {
                if ($('#tbVideos').length) {
                    $('#tbVideos').DataTable({
                        serverSide: true,
                        ajax: "./ajax/videos.datatables.ajax.php",
                        columns: [{
                                data: 'id',
                                responsivePriority: 0,
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    var html = '';
                                    html += '<div class="custom-control custom-checkbox">';
                                    html += '<input type="checkbox" class="custom-control-input" id="row-' + meta.row + '" value="' + value + '">';
                                    html += '<label class="custom-control-label" for="row-' + meta.row + '"></label>';
                                    html += '</div>';
                                    return html;
                                }
                            },
                            {
                                data: 'title',
                                responsivePriority: 1,
                            },
                            {
                                data: 'host',
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    var html = '';
                                    for (var i = 0; i < value.length; i++) {
                                        html += '<span class="badge badge-primary badge-' + value[i] + '">' + hosts[value[i]] + '</span>';
                                    }
                                    return html;
                                }
                            },
                            {
                                data: 'links',
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    var html = '';
                                    html += '<div class="dropdown">';
                                    html += '<a class="btn btn-success btn-sm dropdown-toggle" href="#" role="button" id="ddLinks-' + (meta.row + 1) + '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Links</a>';
                                    html += '<div class="dropdown-menu shadow border-0" aria-labelledby="ddLinks-' + (meta.row + 1) + '">';
                                    html += '<a class="dropdown-item" href="' + value.original + '" target="_blank">Main Video Link</a>';
                                    if (value.alternative !== '#') {
                                        html += '<a class="dropdown-item" href="' + value.alternative + '" target="_blank">Alternative Video Link</a>';
                                    }
                                    html += '<div class="dropdown-divider"></div>';
                                    html += '<a class="dropdown-item" href="javascript:void(0)" onclick="copyText(\'' + value.embed_code + '\')">Embed Code</a>';
                                    html += '<a class="dropdown-item" href="' + value.embed + '" target="_blank">Embed Link</a>';
                                    html += '<a class="dropdown-item" href="' + value.download + '" target="_blank">Download Link</a>';
                                    html += '</div></div>';
                                    return html;
                                }
                            },
                            {
                                data: 'subtitles',
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    var html = '';
                                    if (value.length) {
                                        for (var i = 0; i < value.length; i++) {
                                            html += '<span class="badge badge-info mr-1">' + value[i].label + '</span>';
                                        }
                                    }
                                    return html;
                                }
                            },
                            {
                                data: 'name',
                            },
                            {
                                data: 'added',
                                className: 'text-right',
                            },
                            {
                                data: 'updated',
                                className: 'text-right',
                            },
                            {
                                data: 'id',
                                className: 'text-center',
                                responsivePriority: 1,
                                render: function(value, type, row) {
                                    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-custom dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button><div class="dropdown-menu dropdown-menu-right border-0 shadow"><a href="admin.php?go=videos/edit&id=' + value + '" class="dropdown-item" type="button"><i class="fa fa-pencil"></i> Edit</a><button onclick="videos.delete(' + value + ')" class="dropdown-item" type="button"><i class="fa fa-trash"></i> Delete</button><button onclick="videos.deleteCache($(this))" data-id="' + value + '" class="dropdown-item" type="button"><i class="fa fa-eraser"></i> Clear Cache</button></div></div>';
                                }
                            }
                        ],
                        columnDefs: [{
                                orderable: false,
                                targets: [0, 3, 4, 8]
                            },
                            {
                                visible: true,
                                targets: [0, 1, 8],
                                className: 'noVis'
                            }
                        ],
                        order: [
                            [6, "desc"]
                        ],
                        drawCallback: function(settings) {
                            var checker = localStorage.getItem('video_checker');
                            if (checker !== null) {
                                checker = JSON.parse(checker);
                                for (var i = 0; i < checker.length; i++) {
                                    if (checker[i] !== null) $('#tbVideos').find('tr#' + checker[i].id).addClass('unavailable');
                                    else delete checker[i];
                                }
                                localStorage.setItem('video_checker', JSON.stringify(checker));
                            }
                        }
                    });
                }
            },
            removeSubtitleHTML: function(index) {
                var $wrap = $('#subsWrapper');
                $wrap.find('.form-group[data-index=' + index + ']').remove();
            },
            addSubtitleHTML: function() {
                var $wrap = $('#subsWrapper');
                var languages = '<?php echo subtitle_languages('language[]'); ?>';
                var subIndex = $wrap.find('.form-group').length;
                var html = '<div class="form-group" data-index="' + subIndex + '">';

                html += '<div class="input-group">';
                html += '<div class="input-group-prepend">';
                html += languages;
                html += '</div>';
                html += '<input type="url" name="subtitle[]" class="form-control" placeholder="Link subtitle (.srt/.vtt)">';
                html += '<div class="input-group-append">';
                html += '<button type="button" title="Upload Subtitle" class="btn btn-primary" onclick="videos.modalSubtitle($(this))"><i class="fa fa-upload"></i></button>';
                html += '<button type="button" class="btn btn-danger" onclick="videos.removeSubtitleHTML(' + subIndex + ')"><i class="fa fa-minus"></i></button>';
                html += '</div>';
                html += '</div>';
                $wrap.append(html);
            },
            modalSubtitle: function(e) {
                var $modal = $('#modalUploadSub');
                if (typeof e !== 'undefined') {
                    var $grp = e.closest('.form-group');
                    var $url = $grp.find('[type=url]');
                    $modal.modal('show');
                    $('#subRef').val($grp.attr('data-index'));
                } else {
                    $modal.modal('show');
                }
            },
            uploadSubtitle: function($ref) {
                var fd = new FormData();
                var files = $('#uploadSubFile')[0].files[0];

                fd.append('media', files);

                $('#upsProgress').removeClass('d-none');

                $.ajax({
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                var percent = Math.round((e.loaded / e.total) * 100);
                                $('#uploadSubProgress').attr('aria-valuenow', percent).css('width', percent + '%').text(percent + '%');
                            }
                        });
                        return xhr;
                    },
                    type: 'POST',
                    url: '<?php echo BASE_URL; ?>upload.php',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#upsProgress').addClass('d-none');
                        $('#modalUploadSub').modal('hide');
                        $('#frmUploadSub')[0].reset();
                        if (response.status == "fail") {
                            swal('Error!', response.result, 'error');
                        } else if (response.status == "ok") {
                            if ($ref) {
                                $ref.val(response.result);
                            } else {
                                subtitles.list();
                            }
                        }
                    },
                    error: function(xhr) {
                        $('#upsProgress').addClass('d-none');
                        $('#modalUploadSub').modal('hide');
                        $('#frmUploadSub')[0].reset();
                        swal('Error!', 'Uploading failed!', 'error');
                    }
                });
            },
            reload: function() {
                $('#tbVideos').DataTable().ajax.reload(null, false);
            }
        };

        var users = {
            delete: function(id) {
                if (typeof id !== 'undefined' && id !== '') {
                    swal({
                            title: "Are you sure?",
                            text: "Deleted data cannot be restored anymore!",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            $.ajax({
                                url: "./ajax/users.ajax.php",
                                type: "POST",
                                data: {
                                    id: id,
                                    action: "delete"
                                },
                                success: function(res) {
                                    if (res.status === 'ok') {
                                        swal("Success!", res.message, "success");
                                        setTimeout(function() {
                                            $('#tbUsers').DataTable().ajax.reload(null, false);
                                        }, 1000);
                                    } else {
                                        swal("Error!", res.message, "error");
                                    }
                                },
                                error: function(xhr) {
                                    swal("Error!", xhr.responseText, "error");
                                }
                            });
                        });
                }
            },
            list: function() {
                if ($('#tbUsers').length) {
                    $('#tbUsers').DataTable({
                        serverSide: true,
                        ajax: "./ajax/users.datatables.ajax.php",
                        columns: [{
                                data: 'name',
                                responsivePriority: 0,
                            },
                            {
                                data: 'user',
                            },
                            {
                                data: 'email',
                            },
                            {
                                data: 'status',
                                className: 'text-center',
                                render: function(value, type, row) {
                                    if (value === 1) {
                                        return '<i class="fa fa-check-circle text-success fa-lg" title="Active"></i>';
                                    } else if (value === 2) {
                                        return '<i class="fa fa-question-circle text-info fa-lg" title="Need Approval"></i>';
                                    } else {
                                        return '<i class="fa fa-times-circle text-danger fa-lg" title="Inactive"></i>';
                                    }
                                }
                            },
                            {
                                data: 'added',
                                className: 'text-right',
                            },
                            {
                                data: 'updated',
                                className: 'text-right',
                            },
                            {
                                data: 'role',
                            },
                            {
                                data: 'videos',
                                className: 'text-right',
                            },
                            {
                                data: 'id',
                                className: 'text-center',
                                responsivePriority: 1,
                                render: function(value, type, row) {
                                    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-custom dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button><div class="dropdown-menu dropdown-menu-right border-0 shadow"><a href="admin.php?go=users/edit&id=' + value + '" class="dropdown-item"><i class="fa fa-pencil"></i> Edit</a><button type="button" class="dropdown-item" onclick="users.delete(' + value + ')"><i class="fa fa-trash"></i> Delete</button></div></div>';
                                }
                            }
                        ],
                        columnDefs: [{
                                orderable: false,
                                targets: [7, 8]
                            },
                            {
                                visible: true,
                                targets: [0, 8],
                                className: 'noVis'
                            }
                        ],
                        order: [
                            [4, "desc"]
                        ]
                    });
                }
            },
            changeEmail: function(email) {
                if (typeof email !== 'undefined' && email !== '') {
                    $.ajax({
                        url: './ajax/profile.ajax.php',
                        type: 'POST',
                        data: {
                            action: 'editEmail',
                            email: email
                        },
                        success: function(res) {
                            var title = res.status !== 'fail' ? 'Success' : 'Error';
                            swal(title + '!', res.message, title.toLowerCase());
                        },
                        error: function(xhr) {
                            swal('Error!', xhr.responseText, 'error');
                        }
                    });
                }
            },
            changeUsername: function(user) {
                if (typeof user !== 'undefined' && user !== '') {
                    $.ajax({
                        url: './ajax/profile.ajax.php',
                        type: 'POST',
                        data: {
                            action: 'editUsername',
                            user: user
                        },
                        success: function(res) {
                            var title = res.status !== 'fail' ? 'Success' : 'Error';
                            swal(title + '!', res.message, title.toLowerCase());
                        },
                        error: function(xhr) {
                            swal('Error!', xhr.responseText, 'error');
                        }
                    });
                }
            },
        };

        var settings = {
            deleteVideosWithWords: function() {
                swal({
                        title: 'Delete Videos!',
                        text: 'Are you sure you want to remove blacklisted videos?',
                        type: "warning",
                        showLoaderOnConfirm: true,
                        showCancelButton: true,
                        cancelButtonClass: "btn-secondary",
                        confirmButtonClass: "btn-danger",
                        closeOnConfirm: false
                    },
                    function(isConfirm) {
                        if (!isConfirm) return;

                        var $cache = $('#deleteVideosBlacklisted');
                        var oText = $cache.html();
                        $.ajax({
                            url: './ajax/settings.ajax.php?action=delete_videos_blacklisted',
                            type: 'GET',
                            beforeSend: function() {
                                $cache.html(oText + ' <i class="fa fa-spin fa-refresh"></i>').prop('disabled', true);
                            },
                            success: function(data) {
                                if (data.status === 'success') {
                                    swal('Success!', data.message, 'success');
                                } else {
                                    swal('Error!', data.message, 'error');
                                }
                                $cache.html(oText).prop('disabled', false);
                            }
                        });
                    });
            },
            smtp: function() {
                var pv = $('#smtp_provider').val();
                var $host = $('#smtp_host'),
                    $port = $('#smtp_port'),
                    $tls = $('#smtp_tls');
                var provider = {
                    gmail: {
                        host: "smtp.gmail.com",
                        port: 465,
                        tls: false
                    },
                    ymail: {
                        host: "smtp.mail.yahoo.com",
                        port: 587,
                        tls: true
                    },
                    outlook: {
                        host: "smtp.office365.com",
                        port: 587,
                        tls: true
                    }
                };
                var selected = {};
                if (pv !== 'other') {
                    selected = provider[pv];
                    $host.val(selected.host);
                    $port.val(selected.port);
                    $tls.prop('checked', true);
                } else {
                    $host.val('');
                    $port.val('');
                    $tls.prop('checked', false);
                }
            },
            removeVastHTML: function(index) {
                var $wrap = $('#vastWrapper');
                $wrap.find('.form-group[data-index=' + index + ']').remove();
            },
            addVastHTML: function() {
                var $wrap = $('#vastWrapper');
                var subIndex = $wrap.find('.form-group').length;
                var html = '<div class="form-group" data-index="' + subIndex + '">';

                html += '<div class="input-group">';
                html += '<div class="input-group-prepend" style="max-width:110px">';
                html += '<input type="text" placeholder="Ad Position" name="opt[vast_offset][]" id="vast_offset-' + subIndex + '" class="form-control">';
                html += '</div>';
                html += '<input type="url" name="opt[vast_xml][]" id="vast_xml-' + subIndex + '" placeholder="VAST Link (.xml)" class="form-control">';
                html += '<div class="input-group-append">';
                html += '<button type="button" class="btn btn-danger" onclick="settings.removeVastHTML(' + subIndex + ')"><i class="fa fa-minus-circle"></i></button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                $wrap.append(html);
            },
            premiumProxyChecker: function() {
                var oText = $('#checkProxy').html();
                $.ajax({
                    url: '<?php echo BASE_URL . 'includes/cron_proxy.php'; ?>',
                    dataType: 'json',
                    beforeSend: function() {
                        $('#checkProxy').html(oText + ' <i class="fa fa-spin fa-refresh"></i>').prop('disabled', true);
                    },
                    success: function(data) {
                        $('#checkProxy').html(oText).prop('disabled', false);
                        if (data.status === 'success') {
                            swal('Success!', data.message, 'success');
                            $('#proxy_list').val(data.result);
                        } else {
                            swal('Error!', data.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        swal('Error!', xhr.responseText, 'error');
                    }
                });
            },
            checkProxy: function() {
                var $ckFree = $('#premium_proxy');
                if ($ckFree.prop('checked')) {
                    swal({
                            title: 'Proxy Checker',
                            text: 'Before you check premium proxy, make sure you have saved it first.',
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            settings.premiumProxyChecker();
                        });
                } else {
                    settings.premiumProxyChecker();
                }
            },
            clearCache: function() {
                swal({
                        title: 'Clear Cache!',
                        text: 'Are you sure you want to clear the cache?',
                        type: "warning",
                        showLoaderOnConfirm: true,
                        showCancelButton: true,
                        cancelButtonClass: "btn-secondary",
                        confirmButtonClass: "btn-danger",
                        closeOnConfirm: false
                    },
                    function(isConfirm) {
                        if (!isConfirm) return;

                        var $cache = $('#clearCache');
                        var oText = $cache.html();
                        $.ajax({
                            url: './ajax/settings.ajax.php?action=clear_cache',
                            type: 'GET',
                            beforeSend: function() {
                                $cache.html(oText + ' <i class="fa fa-spin fa-refresh"></i>').prop('disabled', true);
                            },
                            success: function(data) {
                                swal('Success!', 'Cache cleared successfully!', 'success');
                                $cache.html(oText).prop('disabled', false);
                            }
                        });
                    });
            },
            clearVideoInfoCache: function() {
                var oText = $('#clearVideoInfo').html();
                swal({
                        title: 'Clear Cache!',
                        text: 'Are you sure you want to clear the video info cache?',
                        type: "warning",
                        showLoaderOnConfirm: true,
                        showCancelButton: true,
                        cancelButtonClass: "btn-secondary",
                        confirmButtonClass: "btn-danger",
                        closeOnConfirm: false
                    },
                    function(isConfirm) {
                        if (!isConfirm) return;
                        $.ajax({
                            url: './ajax/settings.ajax.php?action=clear_video_info_cache',
                            type: 'GET',
                            beforeSend: function() {
                                $('#clearVideoInfo').html(oText + ' <i class="fa fa-spin fa-refresh"></i>').prop('disabled', true);
                            },
                            success: function(data) {
                                $('#clearVideoInfo').html(oText).prop('disabled', false);
                                if (data.status === 'success') {
                                    swal('Success!', data.message, 'success');
                                } else {
                                    swal('Error!', data.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                swal('Error!', xhr.responseText, 'error');
                            },
                            statusCode: {
                                502: function() {
                                    swal('Error!', '502: Bad Request', 'error');
                                }
                            }
                        });
                    });
            },
            resetHost: function() {
                var oText = $('#resetHost').html();
                swal({
                        title: 'Reset Hosts!',
                        text: 'Return the hosts to their original position. Please clear the embed cache.',
                        type: "warning",
                        showLoaderOnConfirm: true,
                        showCancelButton: true,
                        cancelButtonClass: "btn-secondary",
                        confirmButtonClass: "btn-warning",
                        closeOnConfirm: false
                    },
                    function(isConfirm) {
                        if (!isConfirm) return;
                        $.ajax({
                            url: './ajax/settings.ajax.php?action=reset_host',
                            type: 'GET',
                            beforeSend: function() {
                                $('#resetHost').html(oText + ' <i class="fa fa-spin fa-refresh"></i>').prop('disabled', true);
                            },
                            success: function(data) {
                                $('#resetHost').html(oText).prop('disabled', false);
                                if (data.status === 'success') {
                                    $('#bypass_host').find('option').removeAttr('selected');
                                    $('#bypass_host').multiSelect('select', data.result);
                                    $('#bypass_host').multiSelect('refresh');
                                    swal('Success!', data.message, 'success');
                                } else {
                                    swal('Error!', data.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                swal('Error!', xhr.responseText, 'error');
                            }
                        });
                    });
            }
        };

        var load_balancers = {
            reload: function() {
                $('#tbLoadBalancers').DataTable().ajax.reload(null, false);
            },
            delete: function(id) {
                if (typeof id !== 'undefined' && id !== '') {
                    swal({
                            title: "Are you sure?",
                            text: "Deleted data cannot be restored anymore!",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            $.ajax({
                                url: "./ajax/load_balancers.ajax.php",
                                type: "POST",
                                data: {
                                    id: id,
                                    action: "delete"
                                },
                                success: function(res) {
                                    if (res.status === 'ok') {
                                        swal("Success!", res.message, "success");
                                        setTimeout(function() {
                                            load_balancers.reload();
                                        }, 1000);
                                    } else {
                                        swal("Error!", res.message, "error");
                                    }
                                },
                                error: function(xhr) {
                                    swal("Error!", xhr.responseText, "error");
                                }
                            });
                        });
                }
            },
            list: function() {
                if ($('#tbLoadBalancers').length) {
                    $('#tbLoadBalancers').DataTable({
                        serverSide: true,
                        ajax: "./ajax/load_balancers.datatables.ajax.php",
                        columns: [{
                                data: 'name',
                                responsivePriority: 0
                            },
                            {
                                data: 'link',
                                responsivePriority: 1
                            },
                            {
                                data: 'status',
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    return '<i class="fa fa-' + (value == 1 ? 'check-circle text-success' : 'times-circle text-danger') + '" title="' + (value == 1 ? 'Active' : 'Inactive') + '" style="font-size:1.5rem"></i>';
                                }
                            },
                            {
                                data: 'public',
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    return '<i class="fa fa-' + (value == 1 ? 'check-circle text-success' : 'times-circle text-danger') + '" title="' + (value == 1 ? 'Show' : 'Hide') + '" style="font-size:1.5rem"></i>';
                                }
                            },
                            {
                                data: 'added',
                                className: 'text-right',
                            },
                            {
                                data: 'updated',
                                className: 'text-right',
                            },
                            {
                                data: 'id',
                                className: 'text-center',
                                responsivePriority: 2,
                                render: function(value, type, row) {
                                    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-custom dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button><div class="dropdown-menu dropdown-menu-right border-0 shadow"><a href="admin.php?go=load_balancers/edit&id=' + value + '" class="dropdown-item"><i class="fa fa-pencil"></i> Edit</a><button type="button" class="dropdown-item" onclick="load_balancers.delete(' + value + ')"><i class="fa fa-trash"></i> Delete</button></div></div>';
                                }
                            }
                        ],
                        columnDefs: [{
                                orderable: false,
                                targets: [6]
                            },
                            {
                                visible: true,
                                targets: [3, 4, 5, 6],
                                className: 'noVis'
                            }
                        ],
                        order: [
                            [5, "desc"]
                        ]
                    });
                }
            }
        };

        var gdrive_files = {
            publicChecked: function() {
                var ids = [];
                var $ckItem = $('#tbGDFiles').find('tbody').find('input[type=checkbox]:checked');
                $ckItem.each(function() {
                    ids.push($(this).val());
                });
                if (ids.length) {
                    $('#ckAll, #ckAll1').prop('checked', false);
                    swal({
                            title: "Make Public",
                            text: "Are you sure you want to make these " + ids.length + " files publicly accessible?",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-primary",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            setTimeout(function() {
                                var ids_public = [];
                                var ids_failed = [];
                                var j = 0;
                                var publicFile = function(id, i) {
                                    $.ajax({
                                        url: './ajax/gdrive_files.ajax.php',
                                        type: 'POST',
                                        data: {
                                            action: 'public',
                                            id: id,
                                            email: $('select#email').val()
                                        },
                                        success: function(result) {
                                            if (result.status === 'ok') {
                                                ids_public.push(id);
                                            } else {
                                                ids_failed.push(id);
                                            }
                                        },
                                        error: function() {
                                            ids_failed.push(id);
                                        },
                                        complete: function() {
                                            j++;
                                            if (j >= ids.length) {
                                                swal({
                                                    title: 'Make Public',
                                                    text: ids_public.length + ' files can be accessed by the public, while the remaining ' + ids_failed.length + ' files cannot be accessed by the public yet!',
                                                    type: "info",
                                                    showLoaderOnConfirm: false
                                                });
                                                gdrive_files.reload();
                                                return;
                                            }
                                        }
                                    });
                                };
                                for (var i = 0; i < ids.length; i++) {
                                    publicFile(ids[i], i);
                                }
                            }, 1000);
                        }
                    );
                } else {
                    swal('Warning!', 'Please tick some of the files listed below!', 'warning')
                }
            },
            privateChecked: function() {
                var ids = [];
                var $ckItem = $('#tbGDFiles').find('tbody').find('input[type=checkbox]:checked');
                $ckItem.each(function() {
                    ids.push($(this).val());
                });
                if (ids.length) {
                    $('#ckAll, #ckAll1').prop('checked', false);
                    swal({
                            title: "Make Private",
                            text: "Are you sure you want to make " + ids.length + " files private?",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-warning",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            setTimeout(function() {
                                var ids_private = [];
                                var ids_failed = [];
                                var j = 0;
                                var privateFile = function(id, i) {
                                    $.ajax({
                                        url: './ajax/gdrive_files.ajax.php',
                                        type: 'POST',
                                        data: {
                                            action: 'private',
                                            id: id,
                                            email: $('select#email').val()
                                        },
                                        success: function(result) {
                                            if (result.status === 'ok') {
                                                ids_private.push(id);
                                            } else {
                                                ids_failed.push(id);
                                            }
                                        },
                                        error: function() {
                                            ids_failed.push(id);
                                        },
                                        complete: function() {
                                            j++;
                                            if (j >= ids.length) {
                                                swal({
                                                    title: 'Make Private',
                                                    text: ids_private.length + ' files have become private, while the other ' + ids_failed.length + ' files are not private yet!',
                                                    type: "info",
                                                    showLoaderOnConfirm: false
                                                });
                                                gdrive_files.reload();
                                                return;
                                            }
                                        }
                                    });
                                };
                                for (var i = 0; i < ids.length; i++) {
                                    privateFile(ids[i], i);
                                }
                            }, 1000);
                        }
                    );
                } else {
                    swal('Warning!', 'Please tick some of the files listed below!', 'warning')
                }
            },
            public: function(id) {
                $.ajax({
                    url: "./ajax/gdrive_files.ajax.php",
                    type: "POST",
                    data: {
                        id: id,
                        email: $('select#email').val(),
                        action: 'public'
                    },
                    success: function(res) {
                        if (res.status === 'ok') {
                            swal("Success!", res.message, "success");
                            gdrive_files.reload();
                        } else {
                            swal("Error!", res.message, "error");
                        }
                    },
                    error: function(xhr) {
                        swal("Error!", xhr.responseText, "error");
                    }
                });
            },
            private: function(id) {
                $.ajax({
                    url: "./ajax/gdrive_files.ajax.php",
                    type: "POST",
                    data: {
                        id: id,
                        email: $('select#email').val(),
                        action: 'private'
                    },
                    success: function(res) {
                        if (res.status === 'ok') {
                            swal("Success!", res.message, "success");
                            gdrive_files.reload();
                        } else {
                            swal("Error!", res.message, "error");
                        }
                    },
                    error: function(xhr) {
                        swal("Error!", xhr.responseText, "error");
                    }
                });
            },
            reload: function() {
                $('select#email option').each(function(i, e) {
                    localStorage.removeItem('nextPageToken-' + md5(e.value));
                });
                $('#tbGDFiles').DataTable().ajax.reload(null, false);
            },
            list: function() {
                if ($('#tbGDFiles').length) {
                    var email = $('select#email').val();
                    $('#tbGDFiles').on('preXhr.dt', function(e, settings, data) {
                        data.private = $('#onlyPrivate').is(':checked');
                        data.email = email;
                        data.token = localStorage.getItem('nextPageToken-' + settings._iDisplayStart + '-' + md5(email));
                    }).on('xhr.dt', function(e, settings, json, xhr) {
                        localStorage.setItem('nextPageToken-' + (settings._iDisplayStart + settings._iDisplayLength) + '-' + md5(email), json.token);
                    }).DataTable({
                        serverSide: true,
                        info: false,
                        pagingType: 'simple',
                        ajax: {
                            url: "./ajax/gdrive_files.datatables.ajax.php",
                        },
                        columns: [{
                                data: 'actions',
                                responsivePriority: 0,
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    var html = '';
                                    html += '<div class="custom-control custom-checkbox">';
                                    html += '<input type="checkbox" class="custom-control-input" id="' + value.id + '~' + value.email + '" value="' + value.id + '">';
                                    html += '<label class="custom-control-label" for="' + value.id + '~' + value.email + '"></label>';
                                    html += '</div>';
                                    return html;
                                }
                            },
                            {
                                data: 'title',
                                responsivePriority: 1
                            },
                            {
                                data: 'desc',
                                responsivePriority: 2
                            },
                            {
                                data: 'mimeType',
                                className: 'text-center',
                            },
                            {
                                data: 'alternateLink',
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    var html = '';
                                    html += '<div class="dropdown">';
                                    html += '<a class="btn btn-secondary btn-sm dropdown-toggle" href="#" role="button" id="ddLinks-' + (meta.row + 1) + '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Links</a>';
                                    html += '<div class="dropdown-menu shadow border-0" aria-labelledby="ddLinks-' + (meta.row + 1) + '">';
                                    html += '<a class="dropdown-item" href="' + value.view + '" target="_blank">View Link</a>';
                                    html += '<a class="dropdown-item" href="' + value.download + '" target="_blank">Download Link</a>';
                                    html += '<a class="dropdown-item" href="' + value.preview + '" target="_blank">Preview Link</a>';
                                    html += '<div class="dropdown-divider"></div>';
                                    html += '<a class="dropdown-item" href="' + value.embed + '" target="_blank">Embed Link</a>';
                                    html += '<a class="dropdown-item" href="javascript:void(0)" onclick="copyText(\'' + value.embed_code + '\')">Embed Code</a>';
                                    html += '</div></div>';
                                    return html;
                                }
                            },
                            {
                                data: 'shared',
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    return '<i class="fa fa-' + (value ? 'check-circle text-success' : 'times-circle text-danger') + '" style="font-size:1.5rem"></i>';
                                }
                            },
                            {
                                data: 'editable',
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    return '<i class="fa fa-' + (value ? 'check-circle text-success' : 'times-circle text-danger') + '" style="font-size:1.5rem"></i>';
                                }
                            },
                            {
                                data: 'copyable',
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    return '<i class="fa fa-' + (value ? 'check-circle text-success' : 'times-circle text-danger') + '" style="font-size:1.5rem"></i>';
                                }
                            },
                            {
                                data: 'createdDate',
                                className: 'text-right',
                            },
                            {
                                data: 'modifiedDate',
                                className: 'text-right',
                            },
                            {
                                data: 'actions',
                                className: 'text-center',
                                responsivePriority: 3,
                                render: function(value, type, row, meta) {
                                    var html = '';
                                    if (value.shared) {
                                        html += '<button type="button" class="dropdown-item" onclick="gdrive_files.private(\'' + value.id + '\');$(this).prop(\'disabled\',true)" title="Make it Private"><i class="fa fa-eye-slash"></i> Private</button>';
                                    } else {
                                        html += '<button type="button" class="dropdown-item" onclick="gdrive_files.public(\'' + value.id + '\');$(this).prop(\'disabled\',true)" title="Share with the Public"><i class="fa fa-eye"></i> Public</button>';
                                    }
                                    html += '<button type="button" class="dropdown-item" onclick="gdrive_files.delete(\'' + value.id + '\')" title="Delete"><i class="fa fa-trash"></i> Delete</button>';
                                    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-custom dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button><div class="dropdown-menu dropdown-menu-right border-0 shadow">' + html + '</div></div>';
                                }
                            }
                        ],
                        columnDefs: [{
                                orderable: false,
                                targets: [0, 3, 4, 5, 6, 10]
                            },
                            {
                                visible: true,
                                targets: [0, 1, 2, 10],
                                className: 'noVis'
                            }
                        ],
                        order: [
                            [9, "desc"]
                        ]
                    });
                }
            },
            removeIndex: function(id) {
                var $row = $('#tbGDFiles').find('tbody').find('tr#' + id);
                $row.next('tr.child').remove();
                $row.remove();
                if ($('#tbGDFiles').find('tbody').find('tr').length == 0) {
                    gdrive_files.list();
                }
            },
            delete: function(id) {
                if (typeof id !== 'undefined') {
                    swal({
                            title: "Are you sure?",
                            text: "Deleted data cannot be restored anymore!",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            $.ajax({
                                url: "./ajax/gdrive_files.ajax.php",
                                type: "POST",
                                data: {
                                    id: id,
                                    email: $('select#email').val(),
                                    action: 'delete'
                                },
                                success: function(res) {
                                    if (res.status === 'ok') {
                                        swal("Success!", res.message, "success");
                                        setTimeout(function() {
                                            gdrive_files.removeIndex(id);
                                        }, 1000);
                                    } else {
                                        swal("Error!", res.message, "error");
                                    }
                                },
                                error: function(xhr) {
                                    swal("Error!", xhr.responseText, "error");
                                }
                            });
                        });
                }
            },
            deleteChecked: function() {
                var ids = [];
                var $ckItem = $('#tbGDFiles').find('tbody').find('input[type=checkbox]:checked');
                $ckItem.each(function() {
                    ids.push($(this).val());
                });
                if (ids.length) {
                    $('#ckAll, #ckAll1').prop('checked', false);
                    swal({
                            title: "Are you sure?",
                            text: "Do you want to delete these " + ids.length + " files? Deleted files cannot be restored anymore!",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            setTimeout(function() {
                                var ids_deleted = [];
                                var ids_failed = [];
                                var j = 0;
                                var deleteFile = function(id, i) {
                                    $.ajax({
                                        url: './ajax/gdrive_files.ajax.php',
                                        type: 'POST',
                                        data: {
                                            action: 'delete',
                                            id: id,
                                            email: $('select#email').val()
                                        },
                                        success: function(result) {
                                            if (result.status === 'ok') {
                                                ids_deleted.push(id);
                                                gdrive_files.removeIndex(id);
                                            } else {
                                                ids_failed.push(id);
                                            }
                                        },
                                        error: function() {
                                            ids_failed.push(id);
                                        },
                                        complete: function() {
                                            j++;
                                            if (j >= ids.length) {
                                                swal({
                                                    title: 'Delete File',
                                                    text: ids_deleted.length + ' files have been deleted and ' + ids_failed.length + ' files have failed to delete.',
                                                    type: "info",
                                                    showLoaderOnConfirm: false
                                                });
                                                return;
                                            }
                                        }
                                    });
                                };
                                for (var i = 0; i < ids.length; i++) {
                                    deleteFile(ids[i], i);
                                }
                            }, 1000);
                        }
                    );
                } else {
                    swal('Warning!', 'Please select the Google Drive file that you want to delete!', 'warning')
                }
            }
        };

        var gdrive_accounts = {
            reload: function() {
                $('#tbGDAccounts').DataTable().ajax.reload(null, false);
            },
            delete: function(id) {
                if (typeof id !== 'undefined') {
                    swal({
                            title: "Are you sure?",
                            text: "Deleted data cannot be restored anymore!",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            $.ajax({
                                url: "./ajax/gdrive_accounts.ajax.php",
                                type: "POST",
                                data: {
                                    id: id,
                                    action: 'delete'
                                },
                                success: function(res) {
                                    if (res.status === 'ok') {
                                        swal("Success!", res.message, "success");
                                        setTimeout(function() {
                                            gdrive_accounts.reload();
                                        }, 1000);
                                    } else {
                                        swal("Error!", res.message, "error");
                                    }
                                },
                                error: function(xhr) {
                                    swal("Error!", xhr.responseText, "error");
                                }
                            });
                        });
                }
            },
            removeIndex: function(id) {
                var $row = $('#tbGDAccounts').find('tbody').find('tr#' + id);
                $row.next('tr.child').remove();
                $row.remove();
                if ($('#tbGDAccounts').find('tbody').find('tr').length == 0) {
                    gdrive_accounts.list();
                }
            },
            deleteChecked: function() {
                var ids = [];
                var $ckItem = $('#tbGDAccounts').find('tbody').find('input[type=checkbox]:checked');
                $ckItem.each(function() {
                    ids.push($(this).val());
                });
                if (ids.length) {
                    $('#ckAllGDA, #ckAllGDA1').prop('checked', false);
                    swal({
                            title: "Are you sure?",
                            text: "Are you sure you want to delete these " + ids.length + " Google Drive accounts? Deleted accounts cannot be restored anymore!",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            setTimeout(function() {
                                var ids_deleted = [];
                                var ids_failed = [];
                                var j = 0;
                                var deleteFile = function(id, i) {
                                    $.ajax({
                                        url: './ajax/gdrive_accounts.ajax.php',
                                        type: 'POST',
                                        data: {
                                            action: 'delete',
                                            id: id
                                        },
                                        success: function(result) {
                                            if (result.status === 'ok') {
                                                ids_deleted.push(id);
                                                gdrive_accounts.removeIndex(id);
                                            } else {
                                                ids_failed.push(id);
                                            }
                                        },
                                        error: function() {
                                            ids_failed.push(id);
                                        },
                                        complete: function() {
                                            j++;
                                            if (j >= ids.length) {
                                                swal({
                                                    title: 'Delete Accounts',
                                                    text: ids_deleted.length + ' accounts have been deleted and ' + ids_failed.length + ' accounts have failed to delete.',
                                                    type: "info",
                                                    showLoaderOnConfirm: false
                                                });
                                                return;
                                            }
                                        }
                                    });
                                };
                                for (var i = 0; i < ids.length; i++) {
                                    deleteFile(ids[i], i);
                                }
                            }, 1000);
                        }
                    );
                } else {
                    swal('Warning!', 'Please select the account you want to delete!', 'warning')
                }
            },
            list: function() {
                if ($('#tbGDAccounts').length) {
                    $('#tbGDAccounts').DataTable({
                        serverSide: true,
                        ajax: "./ajax/gdrive_accounts.datatables.ajax.php",
                        columns: [{
                                data: 'id',
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    var html = '';
                                    html += '<div class="custom-control custom-checkbox">';
                                    html += '<input type="checkbox" class="custom-control-input" id="gda-' + value + '" value="' + value + '">';
                                    html += '<label class="custom-control-label" for="gda-' + value + '"></label>';
                                    html += '</div>';
                                    return html;
                                }
                            },
                            {
                                data: 'email',
                                responsivePriority: 0
                            },
                            {
                                data: 'status',
                                responsivePriority: 1,
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    var icon = 'fa-exclamation-circle text-warning';
                                    var title = 'User rate limit exceeded';
                                    if (value == 1) {
                                        icon = 'fa-check-circle text-success';
                                        title = 'Active';
                                    } else if (value == 0) {
                                        icon = 'fa-times-circle text-danger';
                                        title = 'Inactive';
                                    }
                                    return '<i class="fa ' + icon + '" title="' + title + '" style="font-size:1.5rem"></i>';
                                }
                            },
                            {
                                data: 'created',
                                className: 'text-center',
                            },
                            {
                                data: 'modified',
                                className: 'text-center',
                            },
                            {
                                data: 'id',
                                className: 'text-center',
                                responsivePriority: 2,
                                render: function(value, type, row) {
                                    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-custom dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button><div class="dropdown-menu dropdown-menu-right border-0 shadow"><a href="admin.php?go=gdrive_accounts/edit&id=' + value + '" class="dropdown-item"><i class="fa fa-pencil"></i> Edit</a><button type="button" class="dropdown-item" onclick="gdrive_accounts.delete(\'' + value + '\')"><i class="fa fa-trash"></i> Delete</button></div></div>';
                                }
                            }
                        ],
                        columnDefs: [{
                                orderable: false,
                                targets: [0, 5]
                            },
                            {
                                visible: true,
                                targets: [0, 1, 5],
                                className: 'noVis'
                            }
                        ],
                        order: [
                            [1, "asc"]
                        ]
                    });
                }
            }
        };

        var sessions = {
            delete: function(id) {
                if (typeof id !== 'undefined') {
                    swal({
                            title: "Are you sure?",
                            text: "Deleted data cannot be restored anymore!",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            $.ajax({
                                url: "./ajax/sessions.ajax.php",
                                type: "POST",
                                data: {
                                    id: id,
                                    action: 'delete'
                                },
                                success: function(res) {
                                    if (res.status === 'ok') {
                                        swal("Success!", res.message, "success");
                                        setTimeout(function() {
                                            sessions.reload();
                                        }, 1000);
                                    } else {
                                        swal("Error!", res.message, "error");
                                    }
                                },
                                error: function(xhr) {
                                    swal("Error!", xhr.responseText, "error");
                                }
                            });
                        });
                }
            },
            list: function() {
                if ($('#tbSessions').length) {
                    $('#tbSessions').DataTable({
                        serverSide: true,
                        ajax: "./ajax/sessions.datatables.ajax.php",
                        columns: [{
                                data: 'id',
                                responsivePriority: 0,
                                className: 'text-center',
                                render: function(value, type, row, meta) {
                                    var html = '';
                                    html += '<div class="custom-control custom-checkbox">';
                                    html += '<input type="checkbox" class="custom-control-input" id="row-' + meta.row + '" value="' + value + '">';
                                    html += '<label class="custom-control-label" for="row-' + meta.row + '"></label>';
                                    html += '</div>';
                                    return html;
                                }
                            },
                            {
                                data: 'username',
                                responsivePriority: 1
                            },
                            {
                                data: 'ip',
                                responsivePriority: 2
                            },
                            {
                                data: 'useragent'
                            },
                            {
                                data: 'created',
                                className: 'text-center',
                            },
                            {
                                data: 'expired',
                                className: 'text-center',
                            },
                            {
                                data: 'id',
                                className: 'text-center',
                                responsivePriority: 3,
                                render: function(value, type, row) {
                                    return '<button type="button" class="btn btn-danger btn-sm" onclick="sessions.delete(' + value + ')"><i class="fa fa-trash"></i></button>';
                                }
                            }
                        ],
                        columnDefs: [{
                                orderable: false,
                                targets: [0, 6]
                            },
                            {
                                visible: true,
                                targets: [0, 1, 2],
                                className: 'noVis'
                            }
                        ],
                        order: [
                            [5, "desc"]
                        ]
                    });
                }
            },
            reload: function() {
                $('#tbSessions').DataTable().ajax.reload(null, false);
            },
            removeIndex: function(id) {
                var $row = $('#tbSessions').find('tbody').find('tr#' + id);
                $row.next('tr.child').remove();
                $row.remove();
                if ($('#tbSessions').find('tbody').find('tr').length == 0) {
                    sessions.reload();
                }
            },
            deleteChecked: function() {
                var ids = [];
                var $ckItem = $('#tbSessions').find('tbody').find('input[type=checkbox]:checked');
                $ckItem.each(function() {
                    ids.push($(this).val());
                });
                if (ids.length) {
                    $('#ckAllSessions, #ckAllSessions1').prop('checked', false);
                    swal({
                            title: "Are you sure?",
                            text: "Are you sure you want to delete these " + ids.length + " sessions? Deleted sessions cannot be restored anymore!",
                            type: "warning",
                            showLoaderOnConfirm: true,
                            showCancelButton: true,
                            cancelButtonClass: "btn-secondary",
                            confirmButtonClass: "btn-danger",
                            closeOnConfirm: false
                        },
                        function(isConfirm) {
                            if (!isConfirm) return;
                            setTimeout(function() {
                                var ids_deleted = [];
                                var ids_failed = [];
                                var j = 0;
                                var deleteFile = function(id, i) {
                                    $.ajax({
                                        url: './ajax/sessions.ajax.php',
                                        type: 'POST',
                                        data: {
                                            action: 'delete',
                                            id: id
                                        },
                                        success: function(result) {
                                            if (result.status === 'ok') {
                                                ids_deleted.push(id);
                                                sessions.removeIndex(id);
                                            } else {
                                                ids_failed.push(id);
                                            }
                                        },
                                        error: function() {
                                            ids_failed.push(id);
                                        },
                                        complete: function() {
                                            j++;
                                            if (j >= ids.length) {
                                                swal({
                                                    title: 'Delete File',
                                                    text: ids_deleted.length + ' sessions have been deleted and ' + ids_failed.length + ' sessions have failed to delete.',
                                                    type: "info",
                                                    showLoaderOnConfirm: false
                                                });
                                                return;
                                            }
                                        }
                                    });
                                };
                                for (var i = 0; i < ids.length; i++) {
                                    deleteFile(ids[i], i);
                                }
                            }, 1000);
                        }
                    );
                } else {
                    swal('Warning!', 'Please select the sessions that you want to delete!', 'warning')
                }
            }
        };
    </script>
<?php endif; ?>
<?php
$useRecaptcha = ['login', 'register', 'reset-password'];
if (empty($_GET['go']) || in_array($_GET['go'], $useRecaptcha)) {
    include_once '../includes/recaptcha.php';
}
?>
<?php include_once '../includes/histats.php'; ?>
<?php echo html_entity_decode(get_option('chat_widget')); ?>
</body>

</html>
<?php
ob_end_flush();
?>
