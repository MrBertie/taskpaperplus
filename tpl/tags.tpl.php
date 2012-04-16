<div id="tags">
    <ul>
    <?php
    if (!empty($tags)) {
        foreach($tags as $tag => $freq) {
            echo '<li><span class="tag" title="' . lang('tag_click_tip') . '">' . no_underscores($tag) . '</span><span class="freq">' .  $freq . '</span></li>';
        }
    } else {
        echo '<span class="just-info">' . lang('tagless') . '</span>';
    }
    ?>
    </ul>
</div>