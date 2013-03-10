<ul class="filters">
    <?php
    foreach ($this->filters as $key => $filter) {
        list($_, $title, $colour, $visible) = $filter;
        if ($visible) {
            if (\tpp\config('hide_tips') === true) $title = '';
            echo '<li><span id="' . $key. '" class="bk-' . $colour . '" ' .
                 'title="' . $title . '">' . \tpp\no_underscores($key) . '</span></li>';
        }
    }
    ?>
</ul>