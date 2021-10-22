<?php
if (!defined('BASE_DIR')) exit;
?>
</main>
<footer id="footer" class="row py-5 bg-dark rounded-bottom">
    <div class="col-12">
        <ul class="nav justify-content-center">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>terms">Terms</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>privacy">Privacy</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="https://forms.gle/zbMrMTtwjdUahtwq6" target="_blank">DMCA</a>
            </li>
        </ul>
        <p class="text-center text-white my-3">&copy; 2020 - <?php echo date('Y'); ?>. Made with <i class="fa fa-heart text-danger"></i> by <?php echo sitename(); ?>.</p>
    </div>
</footer>
</div>
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
<a href="javascript:void(0)" id="gotoTop" class="bg-custom shadow">
    <span class="gotoContent">
        <i class="fa fa-chevron-up"></i>
    </span>
</a>
<script src="<?php echo BASE_URL; ?>assets/js/popper.min.js" defer></script>
<script src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js" defer></script>
<script src="<?php echo BASE_URL; ?>assets/js/sweetalert.min.js" defer></script>
<script>
    var $ = jQuery.noConflict();

    $(document).ready(function() {
        $('[data-tooltip=true]').tooltip();

        $('#uploadSubFile').on('change', function() {
            var fileName = $(this).val();
            $(this).next('.custom-file-label').html(fileName);
            $('#btnUploadSub').prop('disabled', false);
        });

        var showIDFormat = localStorage.getItem('hostIDFormat');
        if (showIDFormat) {
            $('#hostIDFormat').collapse('show');
        }
        $('#hostIDFormat').on('shown.bs.collapse', function() {
            localStorage.setItem('hostIDFormat', true);
        }).on('hidden.bs.collapse', function() {
            localStorage.removeItem('hostIDFormat', true);
        });

        $('#btnUploadSub').click(function() {
            $('#frmUploadSub').trigger('submit');
        });
        $('#frmUploadSub').on('submit', function(e) {
            e.preventDefault();

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
                    $('#frmUploadSub')[0].reset();
                    $('#frmUploadSub').find('.custom-file-label').html('Choose file');
                    $('#btnUploadSub').prop('disabled', true);
                    $('#upsProgress').addClass('d-none');

                    if (response.status == "fail") {
                        alert(response.result);
                    } else {
                        $('#contSubs').find('input.focus').val(response.result);
                        $('#modalUploadSub').modal('hide');
                    }
                }
            });
        });

        $('#modalUploadSub').on('hidden.bs.modal', function(e) {
            $('input.subtitle').each(function() {
                $(this).removeClass('focus');
            });
        })

        $(window).on('scroll', function() {
            scrollFunction();
        });
        $('#gotoTop').on('click', function() {
            $('html,body').animate({
                scrollTop: 0
            }, 'slow');
        });

        $('.btn-info').click(function() {
            $('#hostIDFormat').collapse('show');
            $('html,body').animate({
                scrollTop: $('#hostIDFormat').offset().top
            }, 'slow');
        });
    });

    function openModalSub(e) {
        e.closest(".form-group").find('input.subtitle').addClass('focus');
        $('#modalUploadSub').modal('show');
    }

    function addSubtitle() {
        var langs = '<?php echo subtitle_languages("lang[]", ""); ?>';
        var newHtml = '<div class="form-group"><div class="input-group"><div class="input-group-prepend">' + langs + '</div><input type="text" name="sub[]" class="form-control subtitle" placeholder="Subtitle Link (.srt/.vtt)"><div class="input-group-append"><button type="button" class="btn btn-primary" data-tooltip="true" title="Upload Subtitle" onclick="openModalSub($(this))"><i class="fa fa-upload"></i></button><button type="button" class="btn btn-danger" data-tooltip="true" title="Remove Subtitle" onclick="removeSubtitle($(this))"><i class="fa fa-minus"></i></button></div></div></div>';
        var $cs = $('#contSubs');
        if ($cs.find('.form-group').length < 10) $cs.append(newHtml);
        else swal('Warning!', 'Only 10 subtitles are allowed.', 'warning');
    }

    function removeSubtitle(e) {
        e.closest('.form-group').remove();
    }

    function scrollFunction() {
        var g = $("#gotoTop");
        if (document.body.scrollTop > 640 || document.documentElement.scrollTop > 480) {
            g.fadeIn();
        } else {
            g.fadeOut();
        }
    }
</script>
<?php
include_once 'includes/recaptcha.php';
include_once 'includes/share.php';
include_once 'includes/histats.php';
?>
<?php echo html_entity_decode(get_option('chat_widget')); ?>
</body>

</html>
