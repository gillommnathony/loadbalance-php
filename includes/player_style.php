<?php
if (empty(get_option('player_skin'))) :
    $player_color = !empty(get_option('player_color')) ? '#' . get_option('player_color') : '#673AB7';
?>
    <style>
        .jw-option.jw-active-option,
        .jw-settings-open .jw-icon-settings,
        .jw-icon-inline[aria-expanded="true"],
        .jw-settings-item-active,
        .jw-controlbar .jw-icon.jw-button-color:hover .jw-svg-icon,
        :not(.jw-flag-touch) .jw-button-color:not(.jw-logo-button):hover,
        .jw-sharing-link:active,
        .jw-sharing-copy:active,
        .jw-sharing-link:focus,
        .jw-sharing-copy:focus {
            fill: <?php echo $player_color; ?> !important;
            color: <?php echo $player_color; ?> !important;
            background-color: transparent !important;
        }

        .jw-knob,
        .jw-progress,
        .lds-ellipsis div {
            background: <?php echo $player_color; ?> !important;
        }
    </style>
<?php endif; ?>
