<h1><?php echo $filter_header; ?></h1>
<div class="filters">
    <ul>
        <?php
        foreach ($filters as $key => $filter) {
            list($skip, $title, $colour, $visible) = $filter;
            if ($visible) {
                if (config('hide_tips') === true) $title = '';
                echo '<li><span id="' . $key. '" class="filter-' . $colour . '" ' .
                     'title="' . $title . '">' . no_underscores($key) . '</span></li>';
            }
        }
        ?>
    </ul>
</div>

