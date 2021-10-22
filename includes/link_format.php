<?php
if (!defined('BASE_DIR')) exit();
?>
<div class="row mt-3 mb-5">
    <div class="col-12">
        <button class="btn btn-custom" data-toggle="collapse" data-target="#hostIDFormat" aria-expanded="true">⚡ Show/Hide Supported Sites</button>
        <div class="card collapse mt-4" id="hostIDFormat">
            <div class="card-header">⚡ Supported Sites</div>
            <div class="card-body p-0">
                <div class="card-content p-3">This tool supports creating streaming links on 50+ websites listed below. We are always developing to support more websites in the future. If the site you want to use is not listed below. Please don't hesitate to contact us.</div>
                <div class="table-responsive">
                    <?php
                    ?>
                    <table class="table table-striped table-hover mb-0" style="font-size:14px!important; min-width:640px">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:45px;"></th>
                                <th style="width:125px;">Host</th>
                                <th style="width:110px;">Status</th>
                                <th>Link Format</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td></td>
                                <td>Direct Link</td>
                                <td>
                                    <?php echo get_host_status('direct', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://vidhost.net/007.mp4">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://bitmovin-a.akamaihd.net/content/MI20192708/master.m3u8">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://bitmovin-a.akamaihd.net/content/MI20192708/stream.mpd">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=drive.google.com"></td>
                                <td>Google Drive</td>
                                <td>
                                    <?php echo get_host_status('gdrive', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://drive.google.com/file/d/1YGihcDRKU9-CLP8_zio5pM0-WjlsP1OD/edit">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://drive.google.com/open?id=1YGihcDRKU9-CLP8_zio5pM0-WjlsP1OD">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=photos.google.com"></td>
                                <td>Google Photos</td>
                                <td>
                                    <?php echo get_host_status('googlephotos', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://photos.google.com/share/AF1QipNwnU5Lz8_VS0rj9NB9HU5suC0tNqawYe6wOA2E1_YcIyC-EvfSsCrwB5db3f8Zfw/photo/AF1QipM4KPqzVwAk8kUiRSNGp_nPyCuhSYbsJiWBaPZ9?key=eGswTGNLU2o0UUtkMVJLdUEwNTVLaUhueEdTNVpB">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://photos.google.com/share/AF1QipNwnU5Lz8_VS0rj9NB9HU5suC0tNqawYe6wOA2E1_YcIyC-EvfSsCrwB5db3f8Zfw?key=eGswTGNLU2o0UUtkMVJLdUEwNTVLaUhueEdTNVpB">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=fembed.com"></td>
                                <td>Fembed</td>
                                <td>
                                    <?php echo get_host_status('fembed', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.fembed.com/v/qgl1-fee-e-nrry">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.fembed.com/f/qgl1-fee-e-nrry">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=www.blogger.com"></td>
                                <td>Blogger</td>
                                <td>
                                    <?php echo get_host_status('blogger', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.blogger.com/video.g?token=AD6v5dztESa7qH5wLsokzq8FkYdteKg1SEaEF8mhrLO2IdLwYunOdvYRaLOMAPTOO0k1XbQ3tEthtLlmTJeg8b6wjmjB6gwRd4h-nr1Wvziym94EE4V73CK6MENsls2k10_OkiMMFTRT">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=uptobox.com"></td>
                                <td>Uptobox</td>
                                <td>
                                    <?php echo get_host_status('uptobox', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://uptobox.com/28fca59gexib">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://uptostream.com/28fca59gexib">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=youtube.com"></td>
                                <td>Youtube</td>
                                <td>
                                    <?php echo get_host_status('youtube', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.youtube.com/watch?v=qGCg15vg8ls">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://youtu.be/qGCg15vg8ls">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=facebook.com"></td>
                                <td>Facebook</td>
                                <td>
                                    <?php echo get_host_status('facebook', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.facebook.com/AttackOnTitan/videos/349559842677536">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=ok.ru"></td>
                                <td>OK.ru</td>
                                <td>
                                    <?php echo get_host_status('okru', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://ok.ru/video/1726154213914">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://ok.ru/videoembed/1726154213914">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=disk.yandex.ru"></td>
                                <td>Yandex Disk</td>
                                <td>
                                    <?php echo get_host_status('yadisk', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://disk.yandex.ru/i/jUCaMeoCKepLUw">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://yadi.sk/i/jUCaMeoCKepLUw">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=www.dropbox.com"></td>
                                <td>Dropbox</td>
                                <td>
                                    <?php echo get_host_status('dropbox', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.dropbox.com/s/seap0x345nexifw/007.mp4">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=onedrive.live.com"></td>
                                <td>OneDrive</td>
                                <td>
                                    <?php echo get_host_status('onedrive', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://onedrive.live.com/embed?resid=2D375281D3105D45%2123209">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://onedrive.live.com/?cid=2D375281D3105D45&id=2D375281D3105D45%2123209&parId=2D375281D3105D45%21105&o=OneUp">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=www.amazon.com"></td>
                                <td>Amazon Drive</td>
                                <td>
                                    <?php echo get_host_status('amazondrive', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.amazon.com/clouddrive/share/ljr2Tl6Fv3UdezkJbUMBO4KweJYh5ESF700b9kyZbH7">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=anonfiles.com"></td>
                                <td>Anonfiles</td>
                                <td>
                                    <?php echo get_host_status('anonfile', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://anonfiles.com/d5EeOaBfo6">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://anonfiles.com/d5EeOaBfo6/SPECTRE_-_Official_Trailer_mp4">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=bayfiles.com"></td>
                                <td>BayFiles</td>
                                <td>
                                    <?php echo get_host_status('bayfiles', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://bayfiles.com/x8EbOcB5o7">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://bayfiles.com/x8EbOcB5o7/SPECTRE_-_Official_Trailer_mp4">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=doodstream.com"></td>
                                <td>DoodStream</td>
                                <td>
                                    <?php echo get_host_status('dood', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://dood.to/d/jq7gd6p2mo9b">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://doodstream.com/d/jq7gd6p2mo9b">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=filerio.in"></td>
                                <td>Filerio</td>
                                <td>
                                    <?php echo get_host_status('filerio', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://filerio.in/wwpxwve3ibtm">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://filerio.in/embed-wwpxwve3ibtm.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=files.fm"></td>
                                <td>Files.fm</td>
                                <td>
                                    <?php echo get_host_status('filesfm', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://files.fm/u/azsqc6eh8">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://files.fm/f/g6fhcp8qm">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=files.im"></td>
                                <td>Files.im</td>
                                <td>
                                    <?php echo get_host_status('filesim', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://files.im/x833jhj0ljj0">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://files.im/embed-x833jhj0ljj0.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=gofile.io"></td>
                                <td>Gofile</td>
                                <td>
                                    <?php echo get_host_status('gofile', TRUE); ?>
                                </td>
                                <td><input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://gofile.io/d/piMiVM"></td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=hexupload.net"></td>
                                <td>Hexupload</td>
                                <td>
                                    <?php echo get_host_status('hexupload', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://hexupload.net/163obdyd1pwm">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://hexupload.net/embed-163obdyd1pwm.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=hxfile.co"></td>
                                <td>HxFile</td>
                                <td>
                                    <?php echo get_host_status('hxfile', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://hxfile.co/ml5nd4t6rg7d">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://hxfile.co/embed-ml5nd4t6rg7d.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=indishare.org"></td>
                                <td>IndiShare</td>
                                <td>
                                    <?php echo get_host_status('indishare', TRUE); ?>
                                </td>
                                <td><input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.indishare.org/8n6c4fuzbfvt">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://dl.indishare.cc/8n6c4fuzbfvt">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=www.mediafire.com"></td>
                                <td>MediaFire</td>
                                <td>
                                    <?php echo get_host_status('mediafire', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.mediafire.com/file/8kov3shiy05ao7k/">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.mediafire.com/file/8kov3shiy05ao7k/007.mp4/file">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=megaup.net"></td>
                                <td>MegaUp</td>
                                <td>
                                    <?php echo get_host_status('megaup', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://megaup.net/2hZlu">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://megaup.net/2hZlu/spectre.mp4">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=mixdrop.co"></td>
                                <td>MixDrop</td>
                                <td>
                                    <?php echo get_host_status('mixdrop', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://mixdrop.co/f/knnndwnjipeqm8">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://mixdrop.co/e/knnndwnjipeqm8">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=www.mp4upload.com"></td>
                                <td>mp4upload</td>
                                <td>
                                    <?php echo get_host_status('mp4upload', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.mp4upload.com/kfqv40px28yi">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.mp4upload.com/embed-kfqv40px28yi.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=ninjastream.to"></td>
                                <td>NinjaStream</td>
                                <td>
                                    <?php echo get_host_status('ninjastream', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://ninjastream.to/watch/apwQEzMpeZPKe">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://ninjastream.to/download/apwQEzMpeZPKe">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=www.okstream.cc"></td>
                                <td>Okstream</td>
                                <td>
                                    <?php echo get_host_status('okstream', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.okstream.cc/ab52fe4fdd1e/">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.okstream.cc/e/ab52fe4fdd1e/">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.okstream.cc/e/ab52fe4fdd1e/spectre.mp4">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=racaty.net"></td>
                                <td>Racaty</td>
                                <td>
                                    <?php echo get_host_status('racaty', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://racaty.net/iil54h7713c6.html">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://racaty.net/embed-iil54h7713c6.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=rumble.com"></td>
                                <td>Rumble</td>
                                <td>
                                    <?php echo get_host_status('rumble', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://rumble.com/v3wihl-the-boss-baby-trailer.html">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://rumble.com/embed/v1abwr/">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=solidfiles.com"></td>
                                <td>Solidfiles</td>
                                <td>
                                    <?php echo get_host_status('solidfiles', TRUE); ?>
                                </td>
                                <td><input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.solidfiles.com/v/Q4D5D2X2AB4GR"></td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=soundcloud.com"></td>
                                <td>Soundcloud</td>
                                <td>
                                    <?php echo get_host_status('soundcloud', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://soundcloud.com/dengerin-musik-797697733/habib-syech-bin-abdul-qodir-assegaf-padang-wulan">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=streamable.com"></td>
                                <td>Streamable</td>
                                <td>
                                    <?php echo get_host_status('streamable', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://streamable.com/nqfrzj">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://streamable.com/e/nqfrzj">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=streamsb.net"></td>
                                <td>StreamSB</td>
                                <td>
                                    <?php echo get_host_status('streamsb', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://streamsb.net/vy2pdsyploap">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://streamsb.net/vy2pdsyploap.html">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://streamsb.net/embed-vy2pdsyploap.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=streamtape.com"></td>
                                <td>Streamtape</td>
                                <td>
                                    <?php echo get_host_status('streamtape', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://streamtape.xyz/v/2qYyjjMD92TPPX">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://streamtape.xyz/e/2qYyjjMD92TPPX">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=uploads.mobi"></td>
                                <td>Uploads.mobi</td>
                                <td>
                                    <?php echo get_host_status('uploadsmobi', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://uploads.mobi/t796dlu4bl6t">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://uploads.mobi/embed-t796dlu4bl6t.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=upstream.to"></td>
                                <td>UpStream</td>
                                <td>
                                    <?php echo get_host_status('upstream', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://upstream.to/embed-d1fl3fks6nos.html">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://upstream.to/d1fl3fks6nos.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=userscloud.com"></td>
                                <td>Userscloud</td>
                                <td>
                                    <?php echo get_host_status('userscloud', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://userscloud.com/3pe4db2oxtcr">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=videobin.co"></td>
                                <td>Videobin</td>
                                <td>
                                    <?php echo get_host_status('videobin', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://videobin.co/x7f3sx36oehn">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://videobin.co/embed-x7f3sx36oehn.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=vidlox.me"></td>
                                <td>Vidlox</td>
                                <td>
                                    <?php echo get_host_status('vidlox', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://vidlox.me/qdvf31mjqrbp.html">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://vidlox.me/embed-qdvf31mjqrbp.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=vidmoly.me"></td>
                                <td>Vidmoly</td>
                                <td>
                                    <?php echo get_host_status('vidmoly', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://vidmoly.me/w/c6hw7oxsclbb">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://vidmoly.to/embed-c6hw7oxsclbb.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=vidoza.net"></td>
                                <td>Vidoza</td>
                                <td>
                                    <?php echo get_host_status('vidoza', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://vidoza.net/psuogbc7pph1/spectre_official_trailer">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://vidoza.net/psuogbc7pph1.html">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://vidoza.net/embed-psuogbc7pph1.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=vimeo.com"></td>
                                <td>Vimeo</td>
                                <td>
                                    <?php echo get_host_status('vimeo', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://vimeo.com/259411563">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://player.vimeo.com/video/259411563">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=vup.to"></td>
                                <td>VUP.to</td>
                                <td>
                                    <?php echo get_host_status('vupto', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://vup.to/llxx7gj4jav5.html">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://vupload.com/llxx7gj4jav5.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=www.yourupload.com"></td>
                                <td>YourUpload</td>
                                <td>
                                    <?php echo get_host_status('yourupload', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.yourupload.com/watch/SFy6T2PbbGWi">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www.yourupload.com/embed/SFy6T2PbbGWi">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=zippyshare.com"></td>
                                <td>Zippyshare</td>
                                <td>
                                    <?php echo get_host_status('zippyshare', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://www70.zippyshare.com/v/gZ55XaiU/file.html">
                                </td>
                            </tr>
                            <tr>
                                <td><img width="16" height="16" src="//www.google.com/s2/favicons?domain=v2.zplayer.live"></td>
                                <td>zPlayer.live</td>
                                <td>
                                    <?php echo get_host_status('zplayer', TRUE); ?>
                                </td>
                                <td>
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://v2.zplayer.live/video/6gqrvznt7t8p">
                                    <input type="url" readonly onfocus="this.select()" class="form-control form-control-sm mb-1" value="https://v2.zplayer.live/embed/6gqrvznt7t8p">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
